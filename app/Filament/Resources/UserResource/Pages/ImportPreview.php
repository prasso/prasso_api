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
    public $field_mappings = []; // Added for form validation
    public $data_quality_issues = []; // Added for form validation
    public $data_preview = []; // Added for form validation
    public $total_rows = 0; // Added for form validation
    public $dataQuality = [];
    public $hasUploadedFile = false;
    public $file;

    public function mount(): void
    {
        // Initialize the form
        $this->form->fill();
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
        
        if (!$this->hasUploadedFile) {
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
                            if (property_exists($user, 'is_admin') && $user->is_admin) {
                                // Get all teams with ID and name for debugging
                                $teams = \App\Models\Team::all();
                                $options = [];
                                foreach ($teams as $team) {
                                    $options[$team->id] = "ID: {$team->id} - {$team->name}";
                                }
                                return $options;
                            }
                            
                            // Check if user has admin role based on property
                            if (property_exists($user, 'role') && $user->role === 'admin') {
                                // Get all teams with ID and name for debugging
                                $teams = \App\Models\Team::all();
                                $options = [];
                                foreach ($teams as $team) {
                                    $options[$team->id] = "ID: {$team->id} - {$team->name}";
                                }
                                return $options;
                            }
                            
                            // Get teams the user owns
                            $ownedTeams = [];
                            try {
                                if (method_exists($user, 'team_owner') && $user->team_owner) {
                                    foreach ($user->team_owner as $team) {
                                        $ownedTeams[$team->id] = "ID: {$team->id} - {$team->name}";
                                    }
                                }
                            } catch (\Exception $e) {
                                // Fallback if method doesn't exist
                            }
                            
                            // Get teams the user is a member of
                            $memberTeams = [];
                            try {
                                if (method_exists($user, 'team_member') && $user->team_member) {
                                    foreach ($user->team_member as $membership) {
                                        if ($membership->team) {
                                            $memberTeams[$membership->team->id] = "ID: {$membership->team->id} - {$membership->team->name}";
                                        }
                                    }
                                }
                            } catch (\Exception $e) {
                                // Fallback if method doesn't exist
                            }
                            
                            // Debug output to log
                            \Illuminate\Support\Facades\Log::info('Team options for import:', [
                                'owned_teams' => $ownedTeams,
                                'member_teams' => $memberTeams
                            ]);
                            // Merge owned and member teams
                            return array_merge($ownedTeams, $memberTeams);
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
        
        if (isset($formData['field_mappings'])) {
            foreach ($formData['field_mappings'] as $mapping) {
                $mappings[$mapping['user_field']] = $mapping['csv_header'];
            }
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
        try {
            $userImportService = app(UserImportService::class);
            
            // Get updated mappings from form
            $mappings = $this->getUpdatedMappings();
            
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
