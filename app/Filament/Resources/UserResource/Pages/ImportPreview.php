<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Services\UserImportService;
use App\Services\AIValidationService;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
use App\Models\User;

class ImportPreview extends Page
{
    use InteractsWithForms;
    use WithFileUploads;

    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.resources.user-resource.pages.import-preview';

    public ?array $data = [];
    
    public $csvData = [];
    public $headers = [];
    public $mappings = [];
    public $requiredFields = [];
    public $team_id; // Using snake_case to match form field name
    
    // Track when team_id is updated
    public function updatedTeamId($value)
    {
        info('Team ID updated:', ['new_value' => $value]);
    }
    public $field_mappings = []; // Added for form validation
    public $data_quality_issues = []; // Added for form validation
    public $data_preview = []; // Added for form validation
    public $total_rows = 0; // Added for form validation
    public $dataQuality = [];
    public $hasUploadedFile = false;
    public $file;

    public function mount(): void
    {
        // Check if team_id is in session and set it
        if (session()->has('import_team_id')) {
            $this->team_id = session('import_team_id');
        }
        
        // Check if CSV data is in session and restore it
        if (session()->has('import_csv_data')) {
            $this->csvData = session('import_csv_data');
            $this->headers = session('import_headers');
            $this->mappings = session('import_mappings');
            $this->total_rows = count($this->csvData);
            $this->hasUploadedFile = !empty($this->csvData);
        } else {
            // Reset the component state if no CSV data in session
            $this->csvData = [];
            $this->headers = [];
            $this->mappings = [];
            $this->total_rows = 0;
            $this->hasUploadedFile = false;
            
            // Clear any previous session data
            session()->forget(['import_csv_data', 'import_headers', 'import_mappings']);
        }
        
        // Initialize the form
        $this->form->fill();
        
        // Log the component state for debugging
        \Illuminate\Support\Facades\Log::info('ImportPreview mounted:', [
            'has_csv_data' => !empty($this->csvData),
            'row_count' => count($this->csvData),
            'has_team_id' => !empty($this->team_id),
            'has_upload_file' => $this->hasUploadedFile
        ]);
    }
    
    /**
     * Get the selected team information
     * 
     * @return array
     */
    public function getSelectedTeam(): array
    {
        // Try to get team_id from multiple sources
        $teamId = null;
        
        // First try the class property
        if ($this->team_id) {
            $teamId = (int) $this->team_id;
         }
        
        // If not found, try to get from session
        if (!$teamId && session()->has('import_team_id')) {
            $teamId = (int) session('import_team_id');
            
            // Update the class property for consistency
            $this->team_id = $teamId;
        }
        
        // If not found, try to get from form state
        if (!$teamId) {
            $formData = $this->form->getRawState();
            if (isset($formData['team_id'])) {
                $teamId = (int) $formData['team_id'];
                info('getSelectedTeam from form data:', ['team_id' => $teamId]);
            }
        }
        
        // If still not found, try to get from request
        if (!$teamId) {
            $request = request();
            if ($request->has('team_id')) {
                $teamId = (int) $request->input('team_id');
                info('getSelectedTeam from request:', ['team_id' => $teamId]);
            }
        }
        
        if (!$teamId) {
            return [
                'name' => 'No team selected',
                'id' => null
            ];
        }

        $team = \App\Models\Team::find($teamId);
        
        if (!$team) {
            return [
                'name' => 'Unknown team',
                'id' => $teamId
            ];
        }
        
        return [
            'name' => $team->name,
            'id' => $team->id
        ];
    }
    
    public function create(): void
    {
        $data = $this->form->getState();
        
        if (!isset($data['team_id'])) {
            Notification::make()
                ->title('Error')
                ->body('Please select a team.')
                ->danger()
                ->send();
            return;
        }
        
        $this->team_id = $data['team_id']; // Using single variable
        
        // Store team_id in session for persistence
        session(['import_team_id' => $this->team_id]);
        
        // Check if file exists and is valid
        if (empty($data['file'])) {
            Notification::make()
                ->title('Error')
                ->body('Please upload a CSV file.')
                ->danger()
                ->send();
            return;
        }
        
        try {
            // In Filament v3, the file upload component returns the path directly in the form state
            $filePath = $data['file'];
            
            // Handle both array and string cases
            if (is_array($filePath)) {
                if (empty($filePath)) {
                    throw new \Exception('No file was uploaded.');
                }
                
                // Get the first file path regardless of the key structure
                $filePath = array_values($filePath)[0];
            }
            
            // Get the full path to the file on disk
            $tempFile = Storage::disk('local')->path($filePath);
            
            if (!file_exists($tempFile)) {
                throw new \Exception('File does not exist at the specified path.');
            }
            
            $this->loadCsvData($tempFile);
            $this->hasUploadedFile = true;
            
            // Store CSV data in session for persistence
            session([
                'import_csv_data' => $this->csvData,
                'import_headers' => $this->headers,
                'import_mappings' => $this->mappings
            ]);
            
            // Force a refresh of the form to show the preview sections
            $this->form->fill();
            
            // Add JavaScript to scroll to preview section
            $this->dispatch('scroll-to-preview');
            
            Notification::make()
                ->title('Success')
                ->body('File uploaded successfully. ' . $this->total_rows . ' rows found.')
                ->success()
                ->send();
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to process the uploaded file: ' . $e->getMessage())
                ->danger()
                ->send();
                
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::error('File upload error: ' . $e->getMessage(), [
                'file' => $data['file'] ?? null,
                'team_id' => $this->team_id
            ]);
        }
    }

    protected function loadCsvData($filePath): void
    {
        $userImportService = app(UserImportService::class);
        $aiValidationService = app(AIValidationService::class);
        
        // Create a temporary uploaded file from the path
        // Use path_info to get the filename from the path
        $pathInfo = pathinfo($filePath);
        $originalName = $pathInfo['basename'] ?? 'import.csv';
        
        // Determine mime type from file or default to text/csv
        $mimeType = mime_content_type($filePath) ?: 'text/csv';
        
        // Create a temporary uploaded file from the path
        $file = new \Illuminate\Http\UploadedFile(
            $filePath,
            $originalName,
            $mimeType,
            null,
            true
        );
        
        // Process the file
        $fileData = $userImportService->processUploadedFile($file);
        $this->headers = $fileData['headers'];
        $this->csvData = $fileData['data'];
        $this->total_rows = count($this->csvData);
        
        // Format phone numbers in the preview data
        $this->formatPhoneNumbersInPreview();
        
        // Get required fields
        $this->requiredFields = $userImportService->getRequiredUserFields();
        
        // Get AI validation
        $aiResponse = $aiValidationService->validateAndMapFields(
            $this->headers,
            $this->requiredFields,
            $this->csvData
        );
        
        $this->mappings = $aiResponse['mappings'];
        
        // Analyze data quality
        $this->dataQuality = $aiValidationService->analyzeDataQuality(
            $this->csvData,
            $this->mappings
        );
    }

    public function form(Form $form): Form
    {
        $schema = [];
        
        // Check if we actually have CSV data, not just the flag
        $hasCsvData = !empty($this->csvData) && count($this->csvData) > 0;
        $this->hasUploadedFile = $hasCsvData; // Update the flag based on actual data
        
        // Always show file upload if no CSV data is available
        if (!$hasCsvData) {
            // Add a reset button at the top if there's session data
            if (session()->has('import_csv_data') || session()->has('import_team_id')) {
                $schema[] = Forms\Components\Section::make('Previous Import Data Detected')
                    ->description('A previous import session was detected. You can continue with this data or reset to start fresh.')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('reset_import')
                                ->label('Reset Import Data')
                                ->action('resetImport')
                                ->color('danger')
                                ->icon('heroicon-o-trash')
                        ])
                    ]);
            }
            
            $schema[] = Forms\Components\Section::make('Upload CSV File')
                ->schema([
                    Forms\Components\FileUpload::make('file')
                        ->label('CSV File')
                        ->helperText('Upload a CSV file with user data. The first row should contain column headers.')
                        ->acceptedFileTypes(['text/csv', 'text/plain'])
                        ->disk('local')
                        ->directory('tmp/csv-imports')
                        ->visibility('private')
                        ->maxSize(10240) // 10MB
                        ->required(),
                    
                    Forms\Components\Select::make('team_id')
                        ->label('Team')
                        ->helperText('Select the team to import users into')
                        ->options(function () {
                            $user = \Illuminate\Support\Facades\Auth::user();
                            if (!$user) return [];
                            
                            // Check if user is admin based on property
                            if ($user->isSuperAdmin()) {
                                // Get all teams with ID and name for debugging
                                $teams = \App\Models\Team::all();
                                $options = [];
                                foreach ($teams as $team) {
                                    $options[$team->id] = "{$team->name}";
                                }
                                return $options;
                            }
                            
                            // Get teams the user is Instructor of
                            $memberTeams = [];
                            try {
                                if (method_exists($user, 'team_member') && $user->team_member) {
                                    foreach ($user->team_member as $membership) {
                                        if ($membership->team) {
                                            $memberTeams[$membership->team->id] = "{$membership->team->name}";
                                        }
                                    }
                                }
                            } catch (\Exception $e) {
                                // Fallback if method doesn't exist
                            }

                            // Merge owned and member teams
                            return $memberTeams;
                        })
                        ->searchable()
                        ->required(),
                    
                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('upload')
                            ->label('Upload and Preview')
                            ->action('create')
                            ->color('primary')
                    ])
                ]);
        }
        
        if ($this->hasUploadedFile) {
            // Add hidden field to persist team_id
            $schema[] = Forms\Components\Hidden::make('team_id')
                ->default($this->team_id);
                
            $schema[] = Section::make('Field Mapping')
                ->description('Map CSV columns to user fields')
                ->schema([
                    Placeholder::make('total_rows')
                        ->label('Total Rows')
                        ->content(fn () => $this->total_rows),
                        
                    Repeater::make('field_mappings')
                        ->schema([
                            Select::make('csv_header')
                                ->label('CSV Column')
                                ->options(fn () => array_combine($this->headers, $this->headers))
                                ->searchable(),
                                
                            Select::make('user_field')
                                ->label('User Field')
                                ->options($this->requiredFields)
                                ->searchable()
                        ])
                        ->default(function () {
                            $mappings = [];
                            foreach ($this->mappings as $userField => $csvHeader) {
                                if ($csvHeader) {
                                    $mappings[] = [
                                        'csv_header' => $csvHeader,
                                        'user_field' => $userField
                                    ];
                                }
                            }
                            return $mappings;
                        })
                        ->columns(2)
                ]);
                
            $schema[] = Section::make('Data Quality')
                ->description('Review data quality issues before importing')
                ->schema([
                    ViewField::make('data_quality_issues')
                        ->view('filament.resources.user-resource.components.data-quality-issues')
                        ->viewData([
                            'dataQuality' => $this->dataQuality
                        ])
                ]);
                
            $schema[] = Section::make('Preview')
                ->description('Preview the first 5 rows of data')
                ->schema([
                    ViewField::make('data_preview')
                        ->view('filament.resources.user-resource.components.data-preview')
                        ->viewData([
                            'headers' => $this->headers,
                            'data' => array_slice($this->csvData, 0, 5)
                        ])
                ]);
                
            // Add import button section
            $schema[] = Section::make('Import')
                ->description('Import the data into the system')
                ->schema([
                    Forms\Components\Placeholder::make('import_count')
                        ->label('Records to Import')
                        ->content($this->total_rows),
                        
                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('import')
                            ->label('Import ' . $this->total_rows . ' Users')
                            ->action('import')
                            ->color('success')
                            ->icon('heroicon-o-arrow-up-tray')
                            ->size('lg')
                            ->button()
                    ])
                ]);
        }
        
        return $form->schema($schema);
    }

    #[Computed]
    public function getUpdatedMappings(): array
    {
        $mappings = [];
        $formData = $this->form->getRawState();
        
        // Try to get mappings from form data first
        if (isset($formData['field_mappings'])) {
            foreach ($formData['field_mappings'] as $mapping) {
                $mappings[$mapping['user_field']] = $mapping['csv_header'];
            }
        }
        
        // If no mappings found in form data, use the stored mappings
        if (empty($mappings) && !empty($this->mappings)) {
            $mappings = $this->mappings;
            \Illuminate\Support\Facades\Log::info('Using stored mappings:', $mappings);
        }
        
        // If still no mappings, try to get from session
        if (empty($mappings) && session()->has('import_mappings')) {
            $mappings = session('import_mappings');
            \Illuminate\Support\Facades\Log::info('Using session mappings:', $mappings);
        }
        
        // If still no mappings but we have headers, create default mappings
        if (empty($mappings) && !empty($this->headers)) {
            // Try to create mappings based on header names
            $requiredFields = app(\App\Services\UserImportService::class)->getRequiredUserFields();
            foreach ($requiredFields as $fieldKey => $fieldName) {
                foreach ($this->headers as $header) {
                    if (stripos($header, $fieldKey) !== false || stripos($header, $fieldName) !== false) {
                        $mappings[$fieldKey] = $header;
                        break;
                    }
                }
            }
            \Illuminate\Support\Facades\Log::info('Created default mappings:', $mappings);
        }
        
        return $mappings;
    }

    /**
     * Import users from CSV data
     * 
     * @param int|null $teamId Optional team ID parameter
     * @return void
     */
    public function import(?int $teamId = null): void
    {
        info('importing');
        
        // Debug CSV data to verify it's available
        \Illuminate\Support\Facades\Log::info('CSV data for import:', [
            'row_count' => count($this->csvData),
            'has_headers' => !empty($this->headers),
            'has_mappings' => !empty($this->mappings)
        ]);
        try {
            $userImportService = app(UserImportService::class);
            
            // Get updated mappings from form
            $mappings = $this->getUpdatedMappings();
            
            // Debug the mappings
            \Illuminate\Support\Facades\Log::info('Mappings for import:', $mappings);
            
            // Get the form data to retrieve team_id
            $formData = $this->form->getRawState();
            
            // Debug the raw form data
            \Illuminate\Support\Facades\Log::info('Import form data:', $formData);
            
            // Try to get team_id from multiple sources
            if ($teamId === null) {
                // First check if it's in the request parameters
                $request = request();
                if ($request->has('team_id')) {
                    $teamId = (int) $request->input('team_id');
                    \Illuminate\Support\Facades\Log::info('Team ID from request:', ['team_id' => $teamId]);
                }
                
                // Then check if it's in the form data
                if (!$teamId && isset($formData['team_id'])) {
                    $teamId = (int) $formData['team_id'];
                    \Illuminate\Support\Facades\Log::info('Team ID from form data:', ['team_id' => $teamId]);
                }
                
                // If not found in form data, try the class property
                if (!$teamId && $this->team_id) {
                    $teamId = (int) $this->team_id;
                    \Illuminate\Support\Facades\Log::info('Team ID from class property:', ['team_id' => $teamId]);
                }
            } else {
                \Illuminate\Support\Facades\Log::info('Team ID from parameter:', ['team_id' => $teamId]);
            }
            
            // If still not found, show error
            if (!$teamId) {
                throw new \Exception('Team ID is required for import. Please go back and select a team.');
            }
            
            // Verify the team exists
            $team = \App\Models\Team::find($teamId);
            if (!$team) {
                \Illuminate\Support\Facades\Log::error('Team not found:', ['team_id' => $teamId]);
                throw new \Exception("Team with ID {$teamId} not found. Please select a valid team.");
            }
            
            \Illuminate\Support\Facades\Log::info('Importing users to team:', [
                'team_id' => $teamId,
                'team_name' => $team->name
            ]);
            
            // Import the users
            $results = $userImportService->importUsers(
                $this->csvData,
                $mappings,
                $teamId
            );
            
            // Show success notification
            Notification::make()
                ->title('Import Complete')
                ->body("Successfully imported {$results['success']} users. Failed: {$results['failed']}")
                ->success()
                ->send();
                
            // Clear the session data
            session()->forget(['import_csv_data', 'import_headers', 'import_mappings']);
            
            // Reset the component state
            $this->csvData = [];
            $this->headers = [];
            $this->mappings = [];
            $this->total_rows = 0;
            $this->hasUploadedFile = false;
            
            // Redirect back to the users list
            $this->redirect(static::getResource()::getUrl('index'));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Import Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancel(): void
    {
        $this->redirect(static::getResource()::getUrl('index'));
    }
    
    /**
     * Clear all import session data and reset the component
     */
    public function resetImport(): void
    {
        // Clear session data
        session()->forget([
            'import_csv_data',
            'import_headers',
            'import_mappings',
            'import_team_id'
        ]);
        
        // Reset component state
        $this->csvData = [];
        $this->headers = [];
        $this->mappings = [];
        $this->total_rows = 0;
        $this->hasUploadedFile = false;
        $this->team_id = null;
        
        // Log the reset
        \Illuminate\Support\Facades\Log::info('Import session data cleared');
        
        // Show notification
        Notification::make()
            ->title('Import Reset')
            ->body('All import data has been cleared. You can start a new import.')
            ->success()
            ->send();
            
        // Refresh the form
        $this->form->fill();
    }
    
    /**
     * Format phone numbers in the preview data
     */
    private function formatPhoneNumbersInPreview(): void
    {
        // Find phone number column in the mappings
        $phoneColumn = null;
        foreach ($this->headers as $header) {
            if (stripos($header, 'phone') !== false || stripos($header, 'mobile') !== false) {
                $phoneColumn = $header;
                break;
            }
        }
        
        // If no phone column found, return
        if (!$phoneColumn) {
            return;
        }
        
        // Format phone numbers in the preview data
        foreach ($this->csvData as &$row) {
            if (isset($row[$phoneColumn]) && !empty($row[$phoneColumn])) {
                $formattedPhone = $this->formatPhoneNumber($row[$phoneColumn]);
                // Add a formatted version to show both original and formatted
                $row[$phoneColumn] = $row[$phoneColumn] . ' → ' . $formattedPhone;
            }
        }
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
