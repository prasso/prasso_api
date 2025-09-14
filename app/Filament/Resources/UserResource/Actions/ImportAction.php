<?php

namespace App\Filament\Resources\UserResource\Actions;

use App\Models\Team;
use App\Models\Site;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImportAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'import';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Import Users')
            ->icon('heroicon-o-arrow-up-tray')
            ->size(ActionSize::Large)
            ->color('success')
            ->form([
                Forms\Components\Group::make()
                    ->schema([
                        FileUpload::make('file')
                            ->label('CSV File')
                            ->helperText('Upload a CSV file with user data. The first row should contain column headers.')
                            ->acceptedFileTypes(['text/csv', 'text/plain'])
                            ->maxSize(10240) // 10MB
                            ->required(),
                        
                        Select::make('team_id')
                            ->label('Team')
                            ->helperText('Select the team to import users into')
                            ->options(function () {
                                $user = Auth::user();
                                if (!$user) return [];
                                
                                // Super admins can see all teams
                                if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                                    return Team::pluck('name', 'id');
                                }
                                
                                // Site admins can only see their own teams
                                $siteId = method_exists($user, 'getUserOwnerSiteId') ? $user->getUserOwnerSiteId() : null;
                                if (!$siteId) return [];
                                
                                $site = Site::find($siteId);
                                return $site ? $site->teams()->pluck('name', 'id') : [];
                            })
                            ->searchable()
                            ->required(),
                    ]),
            ])
            ->action(function (array $data): void {
                // Store the uploaded file and team ID in session for the preview component
                $file = $data['file'];
                
                if ($file instanceof TemporaryUploadedFile) {
                    // Store file path in session for the preview component
                    session()->put('user_import_file_path', $file->getRealPath());
                    session()->put('user_import_team_id', $data['team_id']);
                    
                    // Redirect to the preview page
                    $this->redirect(route('filament.site-admin.resources.users.import-preview'));
                } else {
                    Notification::make()
                        ->title('Error')
                        ->body('Invalid file uploaded.')
                        ->danger()
                        ->send();
                }
            });
    }
}
