<?php

namespace App\Services;

use App\Models\User;
use App\Models\Team;
use App\Models\TeamUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserImportService
{
    /**
     * Process the uploaded CSV file and extract user data
     *
     * @param UploadedFile $file
     * @return array
     */
    public function processUploadedFile(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $data = [];
        $headers = [];
        $isFirstRow = true;
        $delimiter = $this->detectDelimiter($path);

        if (($handle = fopen($path, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if ($isFirstRow) {
                    $headers = $row;
                    $isFirstRow = false;
                    continue;
                }

                $rowData = [];
                foreach ($headers as $index => $header) {
                    $rowData[trim($header)] = isset($row[$index]) ? trim($row[$index]) : null;
                }
                $data[] = $rowData;
            }
            fclose($handle);
        }

        return [
            'headers' => $headers,
            'data' => $data
        ];
    }

    /**
     * Detect the delimiter used in the CSV file
     *
     * @param string $path
     * @return string
     */
    private function detectDelimiter(string $path): string
    {
        $delimiters = [',', "\t", ';', '|'];
        $firstLine = '';
        
        if (($handle = fopen($path, 'r')) !== false) {
            $firstLine = fgets($handle);
            fclose($handle);
        }

        $counts = [];
        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($firstLine, $delimiter);
        }
        
        // Return the delimiter with the highest count
        return array_search(max($counts), $counts);
    }

    /**
     * Get required fields from the User model
     *
     * @return array
     */
    public function getRequiredUserFields(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'team_id' => 'Team ID',
            'phone' => 'Phone'
        ];
    }

    /**
     * Send data to AI for validation and mapping
     *
     * @param array $csvHeaders
     * @param array $requiredFields
     * @param array $sampleData
     * @return array
     */
    public function sendToAIForValidation(array $csvHeaders, array $requiredFields, array $sampleData): array
    {
        // This is a placeholder for the actual AI integration
        // In a real implementation, this would make an API call to an AI service
        
        // For now, we'll simulate a response with field mappings
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
                'message' => 'CSV data structure appears valid.'
            ]
        ];
    }

    /**
     * Import users based on the provided data and mappings
     *
     * @param array $data
     * @param array $mappings
     * @param int $teamId
     * @return array
     */
    public function importUsers(array $data, array $mappings, int $teamId): array
    {

        // If mappings are empty but we have data, try to auto-map based on column names
        if (empty($mappings) && !empty($data)) {
            $headers = array_keys($data[0]);
            $requiredFields = $this->getRequiredUserFields();
            
            foreach ($requiredFields as $fieldKey => $fieldName) {
                foreach ($headers as $header) {
                    // Case-insensitive match for field name in header
                    if (stripos($header, $fieldKey) !== false || stripos($header, $fieldName) !== false) {
                        $mappings[$fieldKey] = $header;
                        break;
                    }
                }
            }
            
            Log::info('Auto-generated mappings:', $mappings);
        }
        
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($data as $index => $row) {
            try {
                // Map CSV data to user fields
                $userData = [];
                foreach ($mappings as $fieldKey => $csvHeader) {
                    if ($csvHeader && isset($row[$csvHeader])) {
                        $userData[$fieldKey] = $row[$csvHeader];
                    }
                }

                // Ensure required fields are present
                if (empty($userData['email']) || empty($userData['name'])) {
                    $results['failed']++;
                    $results['errors'][] = "Row " . ($index + 2) . ": Missing required fields (name or email)";
                    continue;
                }

                // Check if user already exists
                $existingUser = User::where('email', $userData['email'])->first();
                if ($existingUser) {
                    // Update existing user
                    $existingUser->name = $userData['name'];
                    if (!empty($userData['phone'])) {
                        $existingUser->phone = $this->formatPhoneNumber($userData['phone']);
                    }
                    $existingUser->save();
                    
                    // Add to team if not already a member
                    if (!$existingUser->isTeamMember($teamId)) {
                        TeamUser::create([
                            'team_id' => $teamId,
                            'user_id' => $existingUser->id,
                            'role' => config('constants.TEAM_USER_ROLE')
                        ]);
                    }
                    
                    $results['success']++;
                } else {
                    // Create new user
                    $newUser = new User();
                    $newUser->name = $userData['name'];
                    $newUser->email = $userData['email'];
                    $newUser->password = Hash::make($userData['password'] ?? Str::random(10));
                    $newUser->phone = !empty($userData['phone']) ? $this->formatPhoneNumber($userData['phone']) : '';
                    $newUser->version = 'v1';
                    $newUser->save();
                    
                    // Add to team
                    TeamUser::create([
                        'team_id' => $teamId,
                        'user_id' => $newUser->id,
                        'role' => config('constants.TEAM_USER_ROLE')
                    ]);
                    
                    $results['success']++;
                }
            } catch (\Exception $e) {
                Log::error('User import error: ' . $e->getMessage());
                $results['failed']++;
                $results['errors'][] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return $results;
    }
    
    /**
     * Format phone number to ensure it has country code '1' if missing
     *
     * @param string $phoneNumber
     * @return string
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If the phone number doesn't start with '1', add it
        if (!empty($phoneNumber) && strlen($phoneNumber) > 0 && $phoneNumber[0] !== '1') {
            $phoneNumber = '1' . $phoneNumber;
        }
        
        return $phoneNumber;
    }
}
