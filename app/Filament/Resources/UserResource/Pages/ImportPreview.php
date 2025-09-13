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
        
        if (!$this->file || !isset($data['team_id'])) {
            Notification::make()
                ->title('Error')
                ->body('Please upload a CSV file and select a team.')
                ->danger()
                ->send();
            return;
        }
        
        $this->team_id = $data['team_id']; // Using single variable
        
        // Get the file path from the uploaded file
        // Filament's FileUpload returns an array of paths or a single path string
        if (is_array($this->file)) {
            if (empty($this->file)) {
                Notification::make()
                    ->title('Error')
                    ->body('No file was uploaded. Please upload a CSV file.')
                    ->danger()
                    ->send();
                return;
            }
            $filePath = $this->file[0];
        } else {
            $filePath = $this->file;
        }
        
        $tempFile = Storage::disk('local')->path($filePath);
        
        $this->loadCsvData($tempFile);
        $this->hasUploadedFile = true;
    }

    protected function loadCsvData($filePath): void
    {
        $userImportService = app(UserImportService::class);
        $aiValidationService = app(AIValidationService::class);
        
        // Create a temporary uploaded file from the path
        $file = new \Illuminate\Http\UploadedFile(
            $filePath,
            $this->file->getClientOriginalName(),
            $this->file->getMimeType(),
            null,
            true
        );
        
        // Process the file
        $fileData = $userImportService->processUploadedFile($file);
        $this->headers = $fileData['headers'];
        $this->csvData = $fileData['data'];
        $this->total_rows = count($this->csvData);
        
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
        return $form
            ->schema([
                $this->hasUploadedFile ? null : Forms\Components\Section::make('Upload CSV File')
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
                                
                                // Super admins can see all teams
                                try {
                                    if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                                        return \App\Models\Team::pluck('name', 'id');
                                    }
                                } catch (\Exception $e) {
                                    // Fallback if method doesn't exist
                                }
                                
                                // Get teams the user owns
                                $ownedTeams = [];
                                try {
                                    if (method_exists($user, 'team_owner') && $user->team_owner) {
                                        $ownedTeams = $user->team_owner->pluck('name', 'id')->toArray();
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
                                                $memberTeams[$membership->team->id] = $membership->team->name;
                                            }
                                        }
                                    }
                                } catch (\Exception $e) {
                                    // Fallback if method doesn't exist
                                }
                                
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
                    ]),
                Section::make('Field Mapping')
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
                                    ->searchable(),
                            ])
                            ->default(function () {
                                $mappings = [];
                                foreach ($this->mappings as $userField => $csvHeader) {
                                    if ($csvHeader) {
                                        $mappings[] = [
                                            'csv_header' => $csvHeader,
                                            'user_field' => $userField,
                                        ];
                                    }
                                }
                                return $mappings;
                            })
                            ->columns(2),
                    ]),
                    
                Section::make('Data Quality')
                    ->description('Review data quality issues before importing')
                    ->schema([
                        ViewField::make('data_quality_issues')
                            ->view('filament.resources.user-resource.components.data-quality-issues')
                            ->viewData([
                                'dataQuality' => $this->dataQuality,
                            ]),
                    ]),
                    
                Section::make('Preview')
                    ->description('Preview the first 5 rows of data')
                    ->schema([
                        ViewField::make('data_preview')
                            ->view('filament.resources.user-resource.components.data-preview')
                            ->viewData([
                                'headers' => $this->headers,
                                'data' => array_slice($this->csvData, 0, 5),
                            ]),
                    ]),
            ]);
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

    public function import(): void
    {
        try {
            $userImportService = app(UserImportService::class);
            
            // Get updated mappings from form
            $mappings = $this->getUpdatedMappings();
            
            // Import the users
            $results = $userImportService->importUsers(
                $this->csvData,
                $mappings,
                $this->team_id
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
}
