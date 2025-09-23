<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use ReflectionClass;
use ReflectionMethod;

class ChurchModelRelationshipsTest extends TestCase
{
    private string $modelsPath;
    private array $errors = [];
    private array $modelClasses = [];
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->modelsPath = __DIR__ . '/../../packages/prasso/church/src/Models';
        $this->errors = [];
        $this->modelClasses = [];
        
        // First, collect all model classes
        $this->collectModelClasses();
    }
    
    /**
     * Test that all model relationships reference existing classes.
     */
    public function testModelRelationships(): void
    {
        foreach ($this->modelClasses as $modelClass) {
            $this->validateModelRelationships($modelClass);
        }
        
        if (!empty($this->errors)) {
            $errorMessage = "Found " . count($this->errors) . " errors in model relationships:\n";
            $errorMessage .= implode("\n", $this->errors);
            $this->fail($errorMessage);
        }
        
        $this->assertTrue(true, "All model relationships are valid");
    }
    
    /**
     * Collect all model classes in the church package.
     */
    private function collectModelClasses(): void
    {
        $finder = new Finder();
        $finder->files()
            ->in($this->modelsPath)
            ->name('*.php');
            
        foreach ($finder as $file) {
            $filePath = $file->getRealPath();
            $content = file_get_contents($filePath);
            
            // Extract namespace and class name
            preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches);
            preg_match('/class\s+(\w+)(?:\s+extends\s+([^\s{]+))?(?:\s+implements\s+([^{]+))?/', $content, $classMatches);
            
            if (empty($namespaceMatches) || empty($classMatches)) {
                continue;
            }
            
            $namespace = trim($namespaceMatches[1]);
            $className = trim($classMatches[1]);
            $fullClassName = $namespace . '\\' . $className;
            
            // Check if it's a model
            if (strpos($content, 'extends Model') !== false || 
                strpos($content, 'extends \\Illuminate\\Database\\Eloquent\\Model') !== false) {
                $this->modelClasses[] = $fullClassName;
            }
        }
    }
    
    /**
     * Validate relationships for a specific model class.
     */
    private function validateModelRelationships(string $modelClass): void
    {
        try {
            $reflectionClass = new ReflectionClass($modelClass);
            $filePath = $reflectionClass->getFileName();
            
            // Get all public methods that might be relationships
            foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                // Skip methods from parent classes
                if ($method->getDeclaringClass()->getName() !== $modelClass) {
                    continue;
                }
                
                // Check if method returns a relationship
                if ($this->isRelationshipMethod($method)) {
                    $this->validateRelationshipMethod($method, $modelClass, $filePath);
                }
            }
        } catch (\Throwable $e) {
            $this->errors[] = "Error validating model {$modelClass}: " . $e->getMessage();
        }
    }
    
    /**
     * Check if a method is likely to be a relationship method.
     */
    private function isRelationshipMethod(ReflectionMethod $method): bool
    {
        // Common relationship method names
        $relationshipMethodNames = [
            'hasOne', 'hasMany', 'belongsTo', 'belongsToMany', 
            'hasManyThrough', 'hasOneThrough', 'morphTo', 
            'morphOne', 'morphMany', 'morphToMany'
        ];
        
        // Check return type if available
        if ($method->hasReturnType()) {
            $returnType = $method->getReturnType()->getName();
            if (strpos($returnType, 'Illuminate\\Database\\Eloquent\\Relations\\') === 0) {
                return true;
            }
        }
        
        // Check method body for relationship calls
        $start = $method->getStartLine();
        $end = $method->getEndLine();
        $source = file($method->getFileName());
        $body = implode('', array_slice($source, $start - 1, $end - $start + 1));
        
        foreach ($relationshipMethodNames as $relationshipMethod) {
            if (strpos($body, '$this->' . $relationshipMethod . '(') !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validate a relationship method.
     */
    private function validateRelationshipMethod(ReflectionMethod $method, string $modelClass, string $filePath): void
    {
        $methodName = $method->getName();
        $start = $method->getStartLine();
        $end = $method->getEndLine();
        $source = file($filePath);
        $body = implode('', array_slice($source, $start - 1, $end - $start + 1));
        
        // Extract related model class
        if (preg_match('/return\s+\$this->(?:hasOne|hasMany|belongsTo|belongsToMany|hasManyThrough|hasOneThrough|morphTo|morphOne|morphMany|morphToMany)\s*\(\s*([^:,\)]+)::class/', $body, $matches)) {
            $relatedClass = trim($matches[1]);
            
            // Handle fully qualified class names
            if (strpos($relatedClass, '\\') === 0) {
                $relatedClass = substr($relatedClass, 1);
            } 
            // Handle relative class names in the same namespace
            elseif (strpos($relatedClass, '\\') === false) {
                $classNamespace = (new ReflectionClass($modelClass))->getNamespaceName();
                $relatedClass = $classNamespace . '\\' . $relatedClass;
            }
            
            // Check if the related class exists
            if (!class_exists($relatedClass)) {
                $this->errors[] = "Model {$modelClass}::{$methodName} references non-existent class {$relatedClass}";
                
                // Suggest creating the missing model
                $this->errors[] = "  Suggestion: Create the missing model class {$relatedClass}";
            }
        }
        // Check for direct class references without ::class syntax
        elseif (preg_match('/return\s+\$this->(?:hasOne|hasMany|belongsTo|belongsToMany|hasManyThrough|hasOneThrough|morphTo|morphOne|morphMany|morphToMany)\s*\(\s*[\'"]([^\'"]+)[\'"]/', $body, $matches)) {
            $relatedTable = trim($matches[1]);
            $this->errors[] = "Model {$modelClass}::{$methodName} uses string '{$relatedTable}' instead of ::class syntax. Consider updating to use ::class for better type safety.";
        }
    }
}
