<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Site;
use App\Models\TeamUser;
use Prasso\Messaging\Models\MsgMessage;
use Prasso\Messaging\Models\MsgGuest;
use Prasso\Messaging\Models\MsgDelivery;
use Prasso\Messaging\Models\MsgTeamSetting;
use Prasso\Messaging\Jobs\ProcessMsgDelivery;

class ComposeAndSendMessage extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';
    protected static ?string $navigationLabel = 'Compose & Send';
    protected static ?string $navigationGroup = 'Messaging';
    protected static string $view = 'filament.pages.compose-and-send-message';

    public ?array $data = [];
    
    // Properties to store incomplete data reports
    public array $usersWithoutPhones = [];
    public array $guestsWithoutPhones = [];
    public bool $showIncompleteDataReport = false;
    
    // Team verification properties
    public bool $isTeamVerified = false;
    public ?array $teamRegistrationData = [];
    public ?int $currentTeamId = null;

    public function teamRegistrationForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Team Registration for Messaging')
                    ->description('Your team needs to be verified before you can send messages. Please complete this registration form.')
                    ->schema([
                        Forms\Components\Hidden::make('team_id'),
                        
                        Forms\Components\TextInput::make('team_name')
                            ->label('Team/Organization Name')
                            ->required()
                            ->disabled(),
                            
                        Forms\Components\TextInput::make('help_business_name')
                            ->label('Business Name')
                            ->required(),
                            
                        Forms\Components\TextInput::make('help_contact_email')
                            ->label('Contact Email')
                            ->email()
                            ->required(),
                            
                        Forms\Components\TextInput::make('help_contact_phone')
                            ->label('Contact Phone')
                            ->tel()
                            ->required(),
                            
                        Forms\Components\Select::make('help_purpose')
                            ->label('Business Type')
                            ->options([
                                'church' => 'Church',
                                'non_profit' => 'Non-Profit Organization',
                                'education' => 'Educational Institution',
                                'business' => 'Business',
                                'other' => 'Other',
                            ])
                            ->required(),
                            
                        Forms\Components\TextInput::make('help_contact_website')
                            ->label('Website')
                            ->url(),
                            
                        Forms\Components\Textarea::make('help_disclaimer')
                            ->label('Disclaimer/Additional Information')
                            ->rows(3),
                            
                        Forms\Components\Checkbox::make('agree_to_terms')
                            ->label('I agree to the terms and conditions for messaging services')
                            ->helperText('By checking this box, you agree to comply with all applicable laws and regulations regarding messaging.')
                            ->required(),
                            
                    ])
                    ->columns(1),
                    
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('submit_registration')
                        ->label('Submit Registration')
                        ->action('submitTeamRegistration')
                        ->color('primary')
                        ->icon('heroicon-o-paper-airplane')
                ])
                ->alignRight()
            ])
            ->statePath('teamRegistrationData');
    }
    
    public function composeForm(Form $form): Form
    {
        // Get the current site from the request context
        $site = \App\Http\Controllers\Controller::getClientFromHost();
        
        if (!$site || !$site->exists) {
            // Fall back to user's team site if available
            $user = auth()->user();
            if ($user && $user->currentTeam) {
                $site = $user->currentTeam->site;
            }
        }
        
        if (!$site || !$site->exists) {
            Notification::make()
                ->title('Error')
                ->body('No site found. Please ensure you have access to a valid site.')
                ->danger()
                ->persistent()
                ->send();
            
            throw new \RuntimeException('No valid site found for the current request');
        }
        
        $team = $site->teams()->first();
        
        if (!$team) {
            Notification::make()
                ->title('Error')
                ->body('No team found for the current site.')
                ->danger()
                ->send();
        }
        
        return $form
            ->schema([
                Forms\Components\Section::make('Message')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Message Type')
                            ->options([
                                'email' => 'Email',
                                'sms' => 'SMS',
                                'push' => 'Push Notification',
                                'inapp' => 'In-App',
                            ])
                            ->default('email')
                            ->reactive()
                            ->required(),
                            
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('body')
                            ->label('Message Body')
                            ->required()
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),
                    
                Forms\Components\Section::make('Recipients')
                    ->schema([
                        Forms\Components\Radio::make('recipient_type')
                            ->label('Send To')
                            ->options([
                                'all' => 'All Users',
                                'users' => 'Specific Users',
                                'guests' => 'Specific Guests',
                            ])
                            ->default('all')
                            ->live(),

                        Forms\Components\Select::make('user_ids')
                            ->label('Select Users')
                            ->multiple()
                            ->searchable()
                            ->options(function () use ($team) {
                                if (!$team) return [];
                                
                                // Filter users based on message type
                                if ($this->data['type'] ?? '' === 'sms') {
                                    return $team->users()
                                        ->whereNotNull('users.phone')
                                        ->where('users.phone', '!=', '')
                                        ->pluck('name', 'users.id');
                                }
                                
                                return $team->users()->pluck('name', 'id');
                            })
                            ->visible(fn ($get) => $get('recipient_type') === 'users')
                            ->preload()
                            ->reactive()
                            ->loadingMessage('Loading team members...'),

                        Forms\Components\Select::make('guest_ids')
                            ->label('Select Guests')
                            ->multiple()
                            ->searchable()
                            ->options(function () {
                                // Filter guests based on message type
                                if ($this->data['type'] ?? '' === 'sms') {
                                    return MsgGuest::whereNotNull('phone')
                                        ->where('phone', '!=', '')
                                        ->pluck('name', 'id');
                                }
                                
                                return MsgGuest::pluck('name', 'id');
                            })
                            ->visible(fn ($get) => $get('recipient_type') === 'guests')
                            ->reactive()
                    ])
                    ->columns(1),
                
                // Error report for incomplete data
                Forms\Components\Section::make('Data Quality Report')
                    ->schema([
                        Forms\Components\Placeholder::make('incomplete_data_report')
                            ->content(function () {
                                if (!$this->showIncompleteDataReport) {
                                    return 'No data quality issues found.';
                                }
                                
                                $content = '';
                                
                                if (count($this->usersWithoutPhones) > 0) {
                                    $content .= '<strong>Users without phone numbers:</strong><br>';
                                    foreach ($this->usersWithoutPhones as $user) {
                                        $content .= "- {$user['name']} (ID: {$user['id']})<br>";
                                    }
                                    $content .= '<br>';
                                }
                                
                                if (count($this->guestsWithoutPhones) > 0) {
                                    $content .= '<strong>Guests without phone numbers:</strong><br>';
                                    foreach ($this->guestsWithoutPhones as $guest) {
                                        $content .= "- {$guest['name']} (ID: {$guest['id']})<br>";
                                    }
                                }
                                
                                return new \Illuminate\Support\HtmlString($content);
                            })
                    ])
                    ->visible(fn () => $this->showIncompleteDataReport)
                    ->collapsible(),
                    
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('check_data_quality')
                        ->label('Check Data Quality')
                        ->action('checkIncompleteData')
                        ->color('secondary')
                        ->icon('heroicon-o-clipboard-document-check'),
                        
                    Forms\Components\Actions\Action::make('send')
                        ->label('Send Message')
                        ->action('send')
                        ->color('primary')
                        ->icon('heroicon-o-paper-airplane')
                ])
                ->alignRight()
            ])
            ->statePath('data');
    }

    public function mount(): void
    {
        // Get the current site and team
        $site = \App\Http\Controllers\Controller::getClientFromHost();
        if (!$site || !$site->exists) {
            $user = auth()->user();
            if ($user && $user->currentTeam) {
                $site = $user->currentTeam->site;
            }
        }
        
        if (!$site || !$site->exists) {
            Notification::make()
                ->title('Error')
                ->body('No site found. Please ensure you have access to a valid site.')
                ->danger()
                ->persistent()
                ->send();
            return;
        }
        
        $team = $site->teams()->first();
        if (!$team) {
            Notification::make()
                ->title('Error')
                ->body('No team found for the current site.')
                ->danger()
                ->send();
            return;
        }
        
        $this->currentTeamId = $team->id;
        
        // Check if team is verified for messaging
        $teamSetting = MsgTeamSetting::query()->where('team_id', $team->id)->first();
        $status = $teamSetting?->verification_status;
        $this->isTeamVerified = $teamSetting && strtolower((string)$status) === 'verified';
        
        if ($this->isTeamVerified) {
            // Team is verified, show the compose form
            $this->form->fill();
            $this->checkIncompleteData();
        } else {
            // Team is not verified, prepare registration data
            $this->teamRegistrationData = [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'help_business_name' => $team->name,
                'help_contact_email' => auth()->user()->email ?? '',
                'help_contact_phone' => auth()->user()->phone ?? '',
                'help_purpose' => '',
                'help_contact_website' => $site->domain ?? '',
                'help_disclaimer' => '',
                'agree_to_terms' => false,
            ];
        }
    }
    
    public function checkIncompleteData(): void
    {
        $this->usersWithoutPhones = [];
        $this->guestsWithoutPhones = [];
        $this->showIncompleteDataReport = false;
        
        // Get the current site and team
        $site = \App\Http\Controllers\Controller::getClientFromHost();
        if (!$site || !$site->exists) {
            $user = auth()->user();
            if ($user && $user->currentTeam) {
                $site = $user->currentTeam->site;
            }
        }
        
        if (!$site || !$site->exists) {
            return;
        }
        
        $team = $site->teams()->first();
        if (!$team) {
            return;
        }
        
        // Check users without phone numbers
        $usersWithoutPhones = $team->users()
            ->where(function ($query) {
                $query->whereNull('users.phone')
                    ->orWhere('users.phone', '');
            })
            ->get(['users.id', 'users.name', 'users.email']);
            
        foreach ($usersWithoutPhones as $user) {
            $this->usersWithoutPhones[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        }
        
        // Check guests without phone numbers
        $guestsWithoutPhones = MsgGuest::where(function ($query) {
                $query->whereNull('phone')
                    ->orWhere('phone', '');
            })
            ->get(['id', 'name', 'email']);
            
        foreach ($guestsWithoutPhones as $guest) {
            $this->guestsWithoutPhones[] = [
                'id' => $guest->id,
                'name' => $guest->name,
                'email' => $guest->email,
            ];
        }
        
        $this->showIncompleteDataReport = count($this->usersWithoutPhones) > 0 || count($this->guestsWithoutPhones) > 0;
    }

    public function send()
    {
        try {
            Log::info('ComposeAndSendMessage: send() method started');
            $data = $this->form->getState();
            Log::info('Form data: ' . json_encode($data));
            $recipientCount = 0;

            // Get the current site from the request context
            $site = \App\Http\Controllers\Controller::getClientFromHost();
            Log::info('Site retrieved: ' . ($site ? $site->id : 'null'));

            if (!$site || !$site->exists) {
                // Fall back to user's team site if available
                $user = auth()->user();
                if ($user && $user->currentTeam) {
                    $site = $user->currentTeam->site;
                    Log::info('Fallback to user team site: ' . ($site ? $site->id : 'null'));
                }
            }

            if (!$site || !$site->exists) {
                Notification::make()
                    ->title('Error')
                    ->body('No site found. Please ensure you have access to a valid site.')
                    ->danger()
                    ->persistent()
                    ->send();

                throw new \RuntimeException('No valid site found for the current request');
            }

            $team = $site->teams()->first();
            Log::info('Team retrieved: ' . ($team ? $team->id : 'null'));

            if (!$team) {
                Notification::make()
                    ->title('Error')
                    ->body('No team found for the current site.')
                    ->danger()
                    ->send();
                return;
            }

            // Create the message record first (only content fields)
            Log::info('Creating message record with team_id: ' . $team->id);
            $message = MsgMessage::create([
                'team_id' => $team->id,
                'type' => $data['type'],
                'subject' => $data['subject'],
                'body' => $data['body'],
            ]);
            Log::info('Message created with ID: ' . $message->id);

            // Send to all users in the team
            if ($data['recipient_type'] === 'all') {
                Log::info('Sending to all users in team ' . $team->id);
                $users = $team->users;
                Log::info('Found ' . count($users) . ' users in team');
                foreach ($users as $user) {
                    $this->createMessageDelivery($message, $user, 'user');
                    $recipientCount++;
                }
            }
            // Send to selected users (still scoped to team members)
            elseif ($data['recipient_type'] === 'users' && !empty($data['user_ids'])) {
                Log::info('Sending to selected users: ' . json_encode($data['user_ids']));
                // Ensure selected users are actually in the team
                $users = $team->users()->whereIn('users.id', $data['user_ids'])->get();
                Log::info('Found ' . count($users) . ' matching users in team');
                foreach ($users as $user) {
                    $this->createMessageDelivery($message, $user, 'user');
                    $recipientCount++;
                }
            }
            // Send to selected guests
            elseif ($data['recipient_type'] === 'guests' && !empty($data['guest_ids'])) {
                Log::info('Sending to selected guests: ' . json_encode($data['guest_ids']));
                $guests = MsgGuest::whereIn('id', $data['guest_ids'])->get();
                Log::info('Found ' . count($guests) . ' matching guests');
                foreach ($guests as $guest) {
                    $this->createMessageDelivery($message, $guest, 'guest');
                    $recipientCount++;
                }
            }
            else {
                Log::warning('No valid recipients selected. recipient_type: ' . $data['recipient_type']);
            }

            Notification::make()
                ->title('Message Sent')
                ->body("Message has been sent to {$recipientCount} recipients.")
                ->success()
                ->send();

            Log::info("Message sending completed. Sent to {$recipientCount} recipients.");
            $this->form->fill();
        } catch (\Throwable $e) {
            Log::error('Error in send() method: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            Notification::make()
                ->title('Error')
                ->body('An error occurred while sending the message: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function createMessageDelivery(MsgMessage $message, $recipient, string $recipientType): void
    {
        try {
            Log::info("Starting createMessageDelivery for {$recipientType} with ID {$recipient->id}");
            
            // Create delivery record for recipient
            $delivery = MsgDelivery::create([
                'team_id' => $message->team_id,
                'msg_message_id' => $message->id,
                'recipient_type' => $recipientType,
                'recipient_id' => $recipient->id,
                'channel' => $message->type,
                'status' => 'queued',
                'metadata' => [
                    'subject' => $message->subject,
                    'preview' => mb_substr($message->body, 0, 120),
                ],
            ]);
            
            Log::info("MsgDelivery record created with ID: {$delivery->id}");
            
            // Dispatch the job to process the delivery
            try {
                ProcessMsgDelivery::dispatch($delivery->id);
                Log::info("ProcessMsgDelivery job dispatched for delivery ID: {$delivery->id}");
            } catch (\Throwable $e) {
                Log::error("Failed to dispatch ProcessMsgDelivery job: {$e->getMessage()}");
                Log::error($e->getTraceAsString());
                
                // Update delivery status to reflect the error
                $delivery->update([
                    'status' => 'failed',
                    'error' => "Job dispatch error: {$e->getMessage()}",
                    'failed_at' => now(),
                ]);
            }
            
            Log::info("Message delivery created for {$recipientType} {$recipient->id}: {$message->subject}");
        } catch (\Throwable $e) {
            Log::error("Error in createMessageDelivery: {$e->getMessage()}");
            Log::error($e->getTraceAsString());
            throw $e; // Re-throw to handle in the calling method
        }
    }

    public function form(Form $form): Form
    {
        // Return either the registration form or the compose form based on team verification status
        if (!$this->isTeamVerified) {
            return $this->teamRegistrationForm($form);
        } else {
            return $this->composeForm($form);
        }
    }
    
    public function submitTeamRegistration()
    {
        $data = $this->teamRegistrationData;
        
        // Log the data for debugging
        Log::info('Team registration data submitted', ['data' => $data]);
        
        // Check each required field individually to provide better feedback
        $missingFields = [];
        
        if (empty($data['team_id'])) $missingFields[] = 'Team ID';
        if (empty($data['help_business_name'])) $missingFields[] = 'Business Name';
        if (empty($data['help_contact_email'])) $missingFields[] = 'Contact Email';
        if (empty($data['help_contact_phone'])) $missingFields[] = 'Contact Phone';
        if (empty($data['help_purpose'])) $missingFields[] = 'Business Type';
        if (empty($data['agree_to_terms']) || $data['agree_to_terms'] !== true) $missingFields[] = 'Terms Agreement';
        
        if (!empty($missingFields)) {
            Notification::make()
                ->title('Error')
                ->body('Please fill in the following required fields: ' . implode(', ', $missingFields))
                ->danger()
                ->send();
            return;
        }
        
        // Create or update team settings
        $teamSetting = MsgTeamSetting::query()->where('team_id', $data['team_id'])->first();
        
        if (!$teamSetting) {
            $teamSetting = new MsgTeamSetting();
            $teamSetting->team_id = $data['team_id'];
        }
        
        // Update team settings
        $teamSetting->help_business_name = $data['help_business_name'];
        $teamSetting->help_contact_email = $data['help_contact_email'];
        $teamSetting->help_contact_phone = $data['help_contact_phone'];
        $teamSetting->help_purpose = $data['help_purpose'];
        $teamSetting->help_contact_website = $data['help_contact_website'] ?? null;
        $teamSetting->help_disclaimer = $data['help_disclaimer'] ?? null;
        $teamSetting->verification_status = 'pending';
        $teamSetting->save();
        
        // Send notification to admin (you would implement this based on your notification system)
        // For now, we'll just log it
        Log::info('Team registration submitted for verification', ['team_id' => $data['team_id']]);
        
        Notification::make()
            ->title('Registration Submitted')
            ->body('Your team registration has been submitted for verification. You will be notified once it is approved.')
            ->success()
            ->persistent()
            ->send();
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        $panel = \Filament\Facades\Filament::getCurrentPanel();
        return $panel && $panel->getId() === 'site-admin';
    }
}
