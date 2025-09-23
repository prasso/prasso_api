<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Finder\Finder;

class ChurchPackageIntegrityTest extends TestCase
{
    private string $packagePath;
    private array $errors = [];
    private array $warnings = [];
    private array $loadedClasses = [];
    private array $ignoredFiles = [
        // Add files to ignore here if needed
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->packagePath = __DIR__ . '/../../packages/prasso/church/src';
        $this->errors = [];
        $this->warnings = [];
        $this->loadedClasses = [];
    }

    /**
     * Test that all classes in the package can be loaded without errors.
     */
    public function testAllClassesCanBeLoaded(): void
    {
        $this->parseDirectory($this->packagePath);
        
        if (!empty($this->errors)) {
            $errorMessage = "Found " . count($this->errors) . " errors in the church package:\n";
            $errorMessage .= implode("\n", $this->errors);
            $this->fail($errorMessage);
        }
        
        if (!empty($this->warnings)) {
            echo "\nWarnings (not failing the test):\n";
            echo implode("\n", $this->warnings);
        }
        
        $this->assertTrue(true, "All classes loaded successfully");
    }
    
    /**
     * Parse a directory recursively to find all PHP files.
     */
    private function parseDirectory(string $directory): void
    {
        $finder = new Finder();
        $finder->files()
            ->in($directory)
            ->name('*.php');
            
        foreach ($finder as $file) {
            $filePath = $file->getRealPath();
            
            if (in_array($filePath, $this->ignoredFiles)) {
                continue;
            }
            
            $this->parseFile($filePath);
        }
    }
    
    /**
     * Parse a PHP file to check for class loading issues.
     */
    private function parseFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        
        // Extract namespace and class name
        preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches);
        preg_match('/class\s+(\w+)(?:\s+extends\s+([^\s{]+))?(?:\s+implements\s+([^{]+))?/', $content, $classMatches);
        
        if (empty($namespaceMatches) || empty($classMatches)) {
            // This might be a non-class file (like a helper)
            return;
        }
        
        $namespace = trim($namespaceMatches[1]);
        $className = trim($classMatches[1]);
        $fullClassName = $namespace . '\\' . $className;
        
        // Check if class exists
        if (!class_exists($fullClassName, false)) {
            try {
                // Try to load the class
                if (!in_array($fullClassName, $this->loadedClasses)) {
                    $this->loadedClasses[] = $fullClassName;
                    $this->validateClass($fullClassName, $filePath);
                }
            } catch (\Throwable $e) {
                $this->errors[] = "Error loading class {$fullClassName} in {$filePath}: " . $e->getMessage();
            }
        }
    }
    
    /**
     * Validate a class by checking its properties, methods, and dependencies.
     */
    private function validateClass(string $className, string $filePath): void
    {
        try {
            // Try to instantiate a reflection class
            $reflectionClass = new ReflectionClass($className);
            
            // Check parent class
            $parentClass = $reflectionClass->getParentClass();
            if ($parentClass && !class_exists($parentClass->getName())) {
                $this->errors[] = "Class {$className} extends non-existent class {$parentClass->getName()} in {$filePath}";
            }
            
            // Check interfaces
            foreach ($reflectionClass->getInterfaceNames() as $interface) {
                if (!interface_exists($interface)) {
                    $this->errors[] = "Class {$className} implements non-existent interface {$interface} in {$filePath}";
                }
            }
            
            // Check properties
            foreach ($reflectionClass->getProperties() as $property) {
                $this->validateProperty($property, $className, $filePath);
            }
            
            // Check methods
            foreach ($reflectionClass->getMethods() as $method) {
                $this->validateMethod($method, $className, $filePath);
            }
            
            // Check for model relationships
            if (is_subclass_of($className, 'Illuminate\Database\Eloquent\Model')) {
                $this->validateModelRelationships($reflectionClass, $className, $filePath);
            }
        } catch (\Throwable $e) {
            $this->errors[] = "Error validating class {$className} in {$filePath}: " . $e->getMessage();
        }
    }
    
    /**
     * Validate a class property.
     */
    private function validateProperty(ReflectionProperty $property, string $className, string $filePath): void
    {
        // Check property type if available (PHP 7.4+)
        if (method_exists($property, 'getType') && $property->getType()) {
            $typeName = $property->getType()->getName();
            
            // Skip built-in types
            if (!in_array($typeName, ['string', 'int', 'float', 'bool', 'array', 'object', 'callable', 'iterable', 'mixed'])) {
                if (!class_exists($typeName) && !interface_exists($typeName)) {
                    $this->errors[] = "Property {$className}::{$property->getName()} has non-existent type {$typeName} in {$filePath}";
                }
            }
        }
    }
    
    /**
     * Validate a class method.
     */
    private function validateMethod(ReflectionMethod $method, string $className, string $filePath): void
    {
        // Skip methods from parent classes
        if ($method->getDeclaringClass()->getName() !== $className) {
            return;
        }
        
        // Check method parameter types
        foreach ($method->getParameters() as $parameter) {
            if ($parameter->hasType() && !$parameter->getType()->isBuiltin()) {
                $typeName = $parameter->getType()->getName();
                
                if (!class_exists($typeName) && !interface_exists($typeName)) {
                    $this->errors[] = "Method {$className}::{$method->getName()} parameter {$parameter->getName()} has non-existent type {$typeName} in {$filePath}";
                }
            }
        }
        
        // Check return type
        if ($method->hasReturnType() && !$method->getReturnType()->isBuiltin()) {
            $typeName = $method->getReturnType()->getName();
            
            if (!class_exists($typeName) && !interface_exists($typeName)) {
                $this->errors[] = "Method {$className}::{$method->getName()} has non-existent return type {$typeName} in {$filePath}";
            }
        }
    }
    
    /**
     * Validate model relationships.
     */
    private function validateModelRelationships(ReflectionClass $reflectionClass, string $className, string $filePath): void
    {
        $relationshipMethods = [
            'hasOne', 'hasMany', 'belongsTo', 'belongsToMany', 'hasManyThrough', 
            'hasOneThrough', 'morphTo', 'morphOne', 'morphMany', 'morphToMany'
        ];
        
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // Skip methods from parent classes
            if ($method->getDeclaringClass()->getName() !== $className) {
                continue;
            }
            
            // Get method body
            $start = $method->getStartLine();
            $end = $method->getEndLine();
            $source = file($filePath);
            $body = implode('', array_slice($source, $start - 1, $end - $start + 1));
            
            // Check for relationship methods
            foreach ($relationshipMethods as $relationshipMethod) {
                if (preg_match('/\-\>' . $relationshipMethod . '\s*\(\s*([^:,\)]+)::class/i', $body, $matches)) {
                    $relatedClass = trim($matches[1]);
                    
                    // Handle fully qualified class names
                    if (strpos($relatedClass, '\\') === 0) {
                        $relatedClass = substr($relatedClass, 1);
                    } 
                    // Handle relative class names in the same namespace
                    elseif (strpos($relatedClass, '\\') === false) {
                        $classNamespace = $reflectionClass->getNamespaceName();
                        $relatedClass = $classNamespace . '\\' . $relatedClass;
                    }
                    
                    if (!class_exists($relatedClass)) {
                        $this->errors[] = "Model relationship in {$className}::{$method->getName()} references non-existent class {$relatedClass} in {$filePath}";
                    }
                }
            }
        }
    }
}
