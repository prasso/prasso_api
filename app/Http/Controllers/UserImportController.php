<?php

namespace App\Http\Controllers;

use App\Services\UserImportService;
use App\Services\AIValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserImportController extends Controller
{
    protected $userImportService;
    protected $aiValidationService;

    public function __construct(UserImportService $userImportService, AIValidationService $aiValidationService)
    {
        $this->userImportService = $userImportService;
        $this->aiValidationService = $aiValidationService;
    }

    /**
     * Process the uploaded CSV file and validate with AI
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processUpload(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
                'team_id' => 'required|integer|exists:teams,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Process the uploaded file
            $fileData = $this->userImportService->processUploadedFile($request->file('file'));
            
            // Get required fields for User model
            $requiredFields = $this->userImportService->getRequiredUserFields();
            
            // Send to AI for validation and mapping
            $aiResponse = $this->aiValidationService->validateAndMapFields(
                $fileData['headers'],
                $requiredFields,
                $fileData['data']
            );
            
            // Analyze data quality
            $dataQuality = $this->aiValidationService->analyzeDataQuality(
                $fileData['data'],
                $aiResponse['mappings']
            );
            
            // Return the response
            return response()->json([
                'success' => true,
                'headers' => $fileData['headers'],
                'sample_data' => array_slice($fileData['data'], 0, 5),
                'total_rows' => count($fileData['data']),
                'required_fields' => $requiredFields,
                'mappings' => $aiResponse['mappings'],
                'validation' => $aiResponse['validation'],
                'data_quality' => $dataQuality,
                'team_id' => $request->input('team_id')
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing CSV upload: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import users based on validated data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importUsers(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'data' => 'required|array',
                'mappings' => 'required|array',
                'team_id' => 'required|integer|exists:teams,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Import the users
            $results = $this->userImportService->importUsers(
                $request->input('data'),
                $request->input('mappings'),
                $request->input('team_id')
            );
            
            return response()->json([
                'success' => true,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Error importing users: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error importing users: ' . $e->getMessage()
            ], 500);
        }
    }
}
