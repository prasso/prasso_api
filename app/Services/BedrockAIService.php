<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Aws\Sdk;
use Aws\Credentials\Credentials;
use Aws\BedrockRuntime\BedrockRuntimeClient;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class BedrockAIService
{
    protected $client;
    protected $textModelId;
    protected $imageModelId;
    protected $region;
    protected $accessKey;
    protected $secretKey;

    public function __construct()
    {
        // Initialize text model (Claude Sonnet 3.7 for text generation)
        $this->textModelId = config('services.aws.bedrock.text_model_id', 'anthropic.claude-3-sonnet-20240229-v1:0');
        
        // Initialize image model (Stability AI for image generation)
        $this->imageModelId = config('services.aws.bedrock.image_model_id', 'stability.stable-diffusion-xl-v1');
        
        $this->region = config('services.aws.region', 'us-east-1');
        $this->accessKey = config('services.aws.key');
        $this->secretKey = config('services.aws.secret');
        
        // Log the configuration details for debugging
        Log::info('BedrockAIService initialized with the following configuration:');
        Log::info('Text Model ID: ' . $this->textModelId);
        Log::info('Image Model ID: ' . $this->imageModelId);
        Log::info('Region: ' . $this->region);
        
        // For development/testing without actual AWS credentials
        if (empty($this->accessKey) || empty($this->secretKey)) {
            // Use regular HTTP client for mock responses
            $this->client = new Client([
                'base_uri' => "https://bedrock-runtime.{$this->region}.amazonaws.com",
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);
        } else {
            // Initialize AWS SDK client for Bedrock
            $sdk = new Sdk([
                'region' => $this->region,
                'version' => 'latest',
                'credentials' => new Credentials($this->accessKey, $this->secretKey)
            ]);
            
            $this->client = $sdk->createBedrockRuntime();
        }
    }

    /**
     * Generate site assets (color, logo, favicon) using Amazon Bedrock with Claude
     *
     * @param string $siteName
     * @param string $siteDescription
     * @return array
     */
    public function generateSiteAssets($siteName, $siteDescription)
    {
        try {
            if (empty($siteName)) {
                return [
                    'success' => false,
                    'message' => 'Site name is required for asset generation'
                ];
            }
            
            // Use a default description if none provided
            $siteDescription = $siteDescription ?: 'A professional website';
            
            Log::info('Generating assets for site: ' . $siteName . ' with description: ' . $siteDescription);
            
            // Generate color first (fixed order of operations)
            $color = $this->generateColor($siteName, $siteDescription);
            
            // Log what we're about to do
            Log::info('Generating logo with Stability AI for site: ' . $siteName);
            
            // Generate logo with site context
            $logoUrl = $this->generateLogo($siteName, $siteDescription, $color);
            
            // Generate favicon with site context
            $faviconUrl = $this->generateFavicon($siteName, $siteDescription, $color);
            
            return [
                'success' => true,
                'color' => $color,
                'logo_url' => $logoUrl,
                'favicon' => $faviconUrl,
            ];
        } catch (\Exception $e) {
            Log::error('Error generating site assets: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to generate assets: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate a color based on site name and description
     *
     * @param string $siteName
     * @param string $siteDescription
     * @return string
     */
    protected function generateColor($siteName, $siteDescription)
    {
        try {
            $prompt = "You are a design expert. Based on the following site name and description, suggest a single hex color code that would be appropriate as the main brand color. Only respond with the hex color code, nothing else.\n\nSite Name: {$siteName}\nSite Description: {$siteDescription}";
            
            $response = $this->invokeModel([
                'prompt' => $prompt,
                'max_tokens' => 10,
            ], $this->textModelId);
            
            // Extract hex color from response
            $color = trim($response);
            if (!preg_match('/^#?([a-f0-9]{6})$/i', $color)) {
                // If not a valid hex color, provide a default
                $color = '#002233'; // Default dark teal color as requested
            }
            
            // Ensure it starts with #
            if (substr($color, 0, 1) !== '#') {
                $color = '#' . $color;
            }
            
            return $color;
        } catch (\Exception $e) {
            Log::error('Error generating color: ' . $e->getMessage());
            return '#002233'; // Return requested default color if anything fails
        }
    }

    /**
     * Generate a logo using Claude's image generation capabilities
     *
     * @param string $siteName
     * @param string $siteDescription
     * @param string $color
     * @return string
     */
    protected function generateLogo($siteName, $siteDescription, $color)
    {
        // Enhanced prompt with more context about the site's purpose
        $prompt = "Create a simple, professional logo for a website with the following details:\n\nSite Name: {$siteName}\nSite Description: {$siteDescription}\nBrand Color: {$color}\n\nThe logo should be minimal, modern, and suitable as a website logo. Make it a vector-style image with clean lines. The logo should visually represent the site's purpose and mission. Do not include any text in the logo.";
        
        Log::info('Logo generation prompt: ' . $prompt);
        
        try {
            $response = $this->invokeModelWithImageGeneration($prompt);
            
            // Save the image to storage
            $imageData = base64_decode($response);
            $filename = 'logos/' . Str::slug($siteName) . '_' . time() . '.png';
            
            Storage::disk('public')->put($filename, $imageData);
            
            return Storage::disk('public')->url($filename);
        } catch (\Exception $e) {
            Log::error('Error generating logo: ' . $e->getMessage());
            // Return a default logo path or placeholder
            return 'default-logo.png';
        }
    }

    /**
     * Generate a favicon based on the logo
     *
     * @param string $siteName
     * @param string $siteDescription
     * @param string $color
     * @return string
     */
    protected function generateFavicon($siteName, $siteDescription, $color)
    {
        try {
            // First generate the logo (or get it if already generated)
            $logoUrl = $this->generateLogo($siteName, $siteDescription, $color);
            
            // If we have a default logo or error, return a default favicon
            if ($logoUrl === 'default-logo.png') {
                Log::info('Using default favicon because logo generation failed');
                return 'favicon.ico';
            }
            
            // Get the logo file path from the URL
            $logoPath = str_replace(Storage::disk('public')->url(''), '', $logoUrl);
            
            // Check if the logo exists in storage
            if (!Storage::disk('public')->exists($logoPath)) {
                Log::error('Logo file not found at path: ' . $logoPath);
                return 'favicon.ico';
            }
            
            // Read the logo file content
            $logoContent = Storage::disk('public')->get($logoPath);
            
            // Create a new filename for the favicon
            $faviconFilename = 'favicons/' . Str::slug($siteName) . '_' . time() . '.ico';
            
            // Store the logo content as favicon (same image, will be resized by browser)
            // In a production environment, you might want to resize the image here
            Storage::disk('public')->put($faviconFilename, $logoContent);
            
            Log::info('Created favicon from logo at: ' . $faviconFilename);
            
            return Storage::disk('public')->url($faviconFilename);
        } catch (\Exception $e) {
            Log::error('Error generating favicon: ' . $e->getMessage());
            // Return a default favicon path
            return 'favicon.ico';
        }
    }

    /**
     * Invoke the Claude model via Amazon Bedrock
     *
     * @param array $params
     * @param string|null $modelIdToUse The specific model ID to use, defaults to text model
     * @return string
     */
    protected function invokeModel($params, $modelIdToUse = null)
    {
        try {
            // For development/testing without actual AWS credentials
            if (empty($this->accessKey) || empty($this->secretKey)) {
                // Return mock response for testing
                Log::info('Using mock response for Bedrock model invocation');
                
                // Mock color response if the prompt is asking for a color
                if (strpos($params['prompt'], 'hex color code') !== false) {
                    return '#3B82F6'; // Default blue color
                }
                
                return 'Mock response from Claude model';
            }
            
            // Use AWS SDK for real requests
            $payload = json_encode([
                'anthropic_version' => 'bedrock-2023-05-31',
                'max_tokens' => $params['max_tokens'] ?? 1000,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $params['prompt']
                    ]
                ]
            ]);
            
            // Use the specified model ID or default to text model
            $modelIdToUse = $modelIdToUse ?: $this->textModelId;
            
            // Log the full request details before making the call
            Log::info('Making Bedrock API call with the following parameters:');
            Log::info('Model ID: ' . $modelIdToUse);
            Log::info('Region: ' . $this->region);
            Log::info('Endpoint: ' . 'https://bedrock-runtime.' . $this->region . '.amazonaws.com/model/' . $modelIdToUse . '/invoke');
            
            $response = $this->client->invokeModel([
                'modelId' => $modelIdToUse,
                'contentType' => 'application/json',
                'accept' => 'application/json',
                'body' => $payload
            ]);
            
            $result = json_decode($response['body']->getContents(), true);
            return $result['content'][0]['text'];
        } catch (\Exception $e) {
            Log::error('Error invoking Bedrock model: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Invoke the Claude model for image generation
     *
     * @param string $prompt
     * @return string Base64 encoded image
     */
    /**
     * Modify an existing image with new colors using AI
     *
     * @param string $imagePath Path to the existing image
     * @param string $colorPrompt Description of colors to apply
     * @return string URL of the modified image
     */
    /**
     * Modify an existing image with new colors using AI
     *
     * @param string $imageSource Path to local image or URL of remote image
     * @param string|null $colorPrompt Color prompt to use for modification
     * @param bool $isLocalFile Whether the imageSource is a local file path (true) or URL (false)
     * @return string URL of the modified image
     * @throws \Exception If image processing fails
     */
    public function modifyImageWithColors($imageSource, $colorPrompt = null, $isLocalFile = true)
    {
        try {
            // Log the input parameters for debugging
            Log::info('Starting image modification', [
                'image_source' => $imageSource,
                'color_prompt' => $colorPrompt,
                'is_local_file' => $isLocalFile ? 'yes' : 'no'
            ]);
            
            // Get image data either from local file or URL
            if ($isLocalFile) {
                Log::info('Reading image from local file');
                if (!file_exists($imageSource)) {
                    throw new \Exception("Image file does not exist: {$imageSource}");
                }
                $imageData = file_get_contents($imageSource);
                if ($imageData === false) {
                    throw new \Exception("Could not read local image file: {$imageSource}");
                }
            } else {
                Log::info('Fetching image from URL');
                $imageData = @file_get_contents($imageSource);
                if ($imageData === false) {
                    throw new \Exception("Could not fetch image from URL: {$imageSource}");
                }
            }
            
            // Encode the image as base64
            $base64Image = base64_encode($imageData);
            Log::info('Image encoded as base64', ['image_size' => strlen($base64Image)]);
            
            // Create a prompt for the AI to modify the image with the specified colors
            $prompt = "Modify the provided logo";
            if ($colorPrompt) {
                $prompt .= " by updating its colors to: {$colorPrompt}";
            }
            $prompt .= ". Keep the same style and composition but update the color scheme as described. "
                   . "The result should be a professional logo with the new colors applied.";
            
            Log::info('Image modification prompt: ' . $prompt);
            
            // Call the image generation model with the existing image and prompt
            $response = $this->invokeModelWithImageGeneration($prompt, $base64Image);
            
            // Ensure the logos directory exists
            $directory = 'logos';
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            
            // Generate a unique filename
            $filename = $directory . '/updated_' . uniqid() . '.png';
            
            // Decode and save the image
            $decodedImage = base64_decode($response, true);
            if ($decodedImage === false) {
                throw new \Exception('Failed to decode the AI-generated image');
            }
            
            $saved = Storage::disk('public')->put($filename, $decodedImage);
            if ($saved === false) {
                throw new \Exception('Failed to save the modified image to storage');
            }
            
            $url = Storage::disk('public')->url($filename);
            Log::info('Successfully modified and saved image', ['url' => $url]);
            
            return $url;
            
        } catch (\Exception $e) {
            Log::error('Error modifying image with new colors: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function invokeModelWithImageGeneration($prompt, $base64Image = null)
    {
        try {
            // For development/testing without actual AWS credentials
            if (empty($this->accessKey) || empty($this->secretKey)) {
                // Generate a simple placeholder image for testing
                Log::info('Using mock image for Bedrock model invocation');
                
                // Create a simple colored square as a placeholder
                $width = 200;
                $height = 200;
                $image = imagecreatetruecolor($width, $height);
                $color = imagecolorallocate($image, 59, 130, 246); // Blue color
                imagefill($image, 0, 0, $color);
                
                // Output the image to a buffer
                ob_start();
                imagepng($image);
                $imageData = ob_get_clean();
                imagedestroy($image);
                
                // Return base64 encoded image
                return base64_encode($imageData);
            }
            
            // Prepare AWS SDK client if not already created
            if (!$this->client) {
                $this->client = new BedrockRuntimeClient([
                    'version' => 'latest',
                    'region' => $this->region,
                    'credentials' => [
                        'key' => $this->accessKey,
                        'secret' => $this->secretKey,
                    ],
                ]);
            }
            
            // Use AWS SDK for real requests with Stability AI SDXL model
            // Format according to Stability AI SDXL documentation
            // https://docs.aws.amazon.com/bedrock/latest/userguide/model-parameters-stability-diffusion.html
            $requestBody = [
                'text_prompts' => [
                    [
                        'text' => $prompt
                    ]
                ],
                'cfg_scale' => 7.0,
                'width' => 512,
                'height' => 512
            ];
            
            // Convert to JSON
            $payload = json_encode($requestBody);
            
            
            try {
                // Log the full request details before making the call
                Log::info('Making Stability AI API call with the following parameters:');
                Log::info('Model ID: ' . $this->imageModelId);
                Log::info('Region: ' . $this->region);
                Log::info('Endpoint: ' . 'https://bedrock-runtime.' . $this->region . '.amazonaws.com/model/' . $this->imageModelId . '/invoke');
                Log::info('Request Headers: ' . json_encode([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]));
                
                $response = $this->client->invokeModel([
                    'modelId' => $this->imageModelId,
                    'contentType' => 'application/json',
                    'accept' => 'application/json',
                    'body' => $payload
                ]);
                
                $responseBody = $response['body']->getContents();
                
                $result = json_decode($responseBody, true);
                
                // Log the structure of the response
                Log::info('Response structure: ' . json_encode(array_keys($result)));
                
                // Extract base64 image from response - structure is different for Stability AI
                if (isset($result['artifacts']) && is_array($result['artifacts']) && !empty($result['artifacts'])) {
                    return $result['artifacts'][0]['base64'];
                }
                
                throw new \Exception('No image was found in the Stability AI response');
            } catch (\Aws\Exception\AwsException $e) {
                // Get the full error details from AWS
                Log::error('AWS Error Details: ' . $e->getMessage());
                if ($e->getResponse()) {
                    Log::error('AWS Error Response: ' . $e->getResponse()->getBody());
                }
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error generating image with Bedrock: ' . $e->getMessage());
            throw $e;
        }
    }


}
