<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\BedrockAIService;

class AIValidationService
{
    /**
     * Send data to AI for validation and mapping
     *
     * @param array $csvHeaders
     * @param array $requiredFields
     * @param array $sampleData
     * @return array
     */
    public function validateAndMapFields(array $csvHeaders, array $requiredFields, array $sampleData): array
    {
        try {
            // Create a cache key based on the input data to avoid redundant API calls
            $cacheKey = 'ai_validation_' . md5(json_encode([
                'headers' => $csvHeaders,
                'required' => $requiredFields,
                'sample' => array_slice($sampleData, 0, 3) // Use only first 3 samples for cache key
            ]));

            // Check if we have a cached response
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            // Prepare the data for the AI API
            $payload = [
                'csvHeaders' => $csvHeaders,
                'requiredFields' => $requiredFields,
                'sampleData' => array_slice($sampleData, 0, 5), // Limit to 5 samples for API call
                'task' => 'Map CSV headers to required user fields and validate data structure'
            ];

            // Make the API call to the AI service
            $response = $this->callAIService($payload);

            // Cache the response for 1 hour
            Cache::put($cacheKey, $response, 3600);

            return $response;
        } catch (\Exception $e) {
            Log::error('AI validation error: ' . $e->getMessage());
            
            // Fallback to basic mapping if AI service fails
            return $this->fallbackMapping($csvHeaders, $requiredFields);
        }
    }

    /**
     * Make the actual API call to the AI service
     *
     * @param array $payload
     * @return array
     */
    private function callAIService(array $payload): array
    {
        try {
            // Use BedrockAIService instead of direct API calls
            $bedrockService = new BedrockAIService();
            
            // Format the prompt for Bedrock
            $prompt = "You are a data mapping expert. Please analyze the following CSV headers and map them to the required user fields.\n\n";
            $prompt .= "CSV Headers: " . implode(", ", $payload['csvHeaders']) . "\n";
            $prompt .= "Required Fields: " . implode(", ", array_keys($payload['requiredFields'])) . "\n";
            $prompt .= "Sample Data: " . json_encode(array_slice($payload['sampleData'], 0, 2)) . "\n\n";
            $prompt .= "Return a JSON object with mappings between required fields and CSV headers. Format: {\"field_name\": \"matching_header\"}";
            
            // Call Bedrock AI service using the public method
            $response = $bedrockService->invokeModel([
                'prompt' => $prompt,
                'max_tokens' => 500
            ]);
            
            // Try to parse the response as JSON
            $mappings = json_decode($response, true);
            
            // If parsing failed or response is not an array, use fallback
            if (!is_array($mappings)) {
                Log::warning('Failed to parse AI response as JSON: ' . $response);
                return $this->fallbackMapping($payload['csvHeaders'], $payload['requiredFields']);
            }
            
            return [
                'mappings' => $mappings,
                'validation' => [
                    'valid' => true,
                    'message' => 'CSV data structure appears valid.'
                ],
                'ai_used' => true
            ];
        } catch (\Exception $e) {
            Log::error('AI service call failed: ' . $e->getMessage());
            return $this->fallbackMapping($payload['csvHeaders'], $payload['requiredFields']);
        }
    }

    /**
     * Fallback mapping logic when AI service is unavailable
     *
     * @param array $csvHeaders
     * @param array $requiredFields
     * @return array
     */
    private function fallbackMapping(array $csvHeaders, array $requiredFields): array
    {
        $mappings = [];
        
        // Simple mapping logic - match headers that contain required field names
        foreach ($requiredFields as $fieldKey => $fieldName) {
            $matched = false;
            
            foreach ($csvHeaders as $header) {
                // Case-insensitive match for field name in header
                if (stripos($header, $fieldKey) !== false || stripos($header, $fieldName) !== false) {
                    $mappings[$fieldKey] = $header;
                    $matched = true;
                    break;
                }
            }
            
            // If no match found, set to null
            if (!$matched) {
                $mappings[$fieldKey] = null;
            }
        }
        
        return [
            'mappings' => $mappings,
            'validation' => [
                'valid' => true,
                'message' => 'Basic mapping completed. AI validation was not available.'
            ],
            'ai_used' => false
        ];
    }

    /**
     * Analyze the data quality and provide recommendations
     *
     * @param array $data
     * @param array $mappings
     * @return array
     */
    public function analyzeDataQuality(array $data, array $mappings): array
    {
        $issues = [];
        $stats = [
            'total_rows' => count($data),
            'empty_emails' => 0,
            'duplicate_emails' => [],
            'invalid_emails' => [],
            'empty_names' => 0
        ];

        $emails = [];

        foreach ($data as $index => $row) {
            $rowNum = $index + 2; // +2 because index is 0-based and we skip header row
            
            // Check email
            if (isset($mappings['email']) && isset($row[$mappings['email']])) {
                $email = trim($row[$mappings['email']]);
                
                if (empty($email)) {
                    $stats['empty_emails']++;
                    $issues[] = "Row {$rowNum}: Empty email address";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $stats['invalid_emails'][] = $email;
                    $issues[] = "Row {$rowNum}: Invalid email format - {$email}";
                } elseif (in_array($email, $emails)) {
                    $stats['duplicate_emails'][] = $email;
                    $issues[] = "Row {$rowNum}: Duplicate email - {$email}";
                } else {
                    $emails[] = $email;
                }
            } else {
                $stats['empty_emails']++;
                $issues[] = "Row {$rowNum}: Missing email field";
            }
            
            // Check name
            if (isset($mappings['name']) && isset($row[$mappings['name']])) {
                $name = trim($row[$mappings['name']]);
                
                if (empty($name)) {
                    $stats['empty_names']++;
                    $issues[] = "Row {$rowNum}: Empty name field";
                }
            } else {
                $stats['empty_names']++;
                $issues[] = "Row {$rowNum}: Missing name field";
            }
        }

        // Deduplicate issues
        $issues = array_unique($issues);
        
        return [
            'stats' => $stats,
            'issues' => $issues,
            'has_critical_issues' => $stats['empty_emails'] > 0 || $stats['empty_names'] > 0 || count($stats['invalid_emails']) > 0,
            'recommendations' => $this->generateRecommendations($stats)
        ];
    }

    /**
     * Generate recommendations based on data quality stats
     *
     * @param array $stats
     * @return array
     */
    private function generateRecommendations(array $stats): array
    {
        $recommendations = [];
        
        if ($stats['empty_emails'] > 0) {
            $recommendations[] = "Fix {$stats['empty_emails']} rows with missing email addresses";
        }
        
        if (count($stats['invalid_emails']) > 0) {
            $recommendations[] = "Fix " . count($stats['invalid_emails']) . " invalid email formats";
        }
        
        if (count($stats['duplicate_emails']) > 0) {
            $recommendations[] = "Review " . count($stats['duplicate_emails']) . " duplicate email addresses";
        }
        
        if ($stats['empty_names'] > 0) {
            $recommendations[] = "Fix {$stats['empty_names']} rows with missing names";
        }
        
        if (empty($recommendations)) {
            $recommendations[] = "Data looks good! No critical issues found.";
        }
        
        return $recommendations;
    }
}
