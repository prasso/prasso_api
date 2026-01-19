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
use App\Models\Team;
use App\Models\TeamUser;
use App\Mail\SmsRegistrationNotification;
use Prasso\Messaging\Models\MsgMessage;
use Prasso\Messaging\Models\MsgGuest;
use Prasso\Messaging\Models\MsgDelivery;
use Prasso\Messaging\Models\MsgTeamSetting;
use Prasso\Messaging\Jobs\ProcessMsgDelivery;
use Illuminate\Support\Facades\Mail;

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
    public bool $isTeamPending = false;
    public ?string $adminEmail = null;
    public ?array $teamRegistrationData = [];
    public ?int $currentTeamId = null;

    public function pendingRegistrationForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Registration Under Review')
                    ->description('Your team registration is currently being reviewed by our admin team.')
                    ->schema([
                        Forms\Components\Placeholder::make('pending_notice')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div class="rounded-lg bg-blue-50 border border-blue-200 p-4">' .
                                '<h3 class="font-semibold text-blue-900 mb-2">Registration Status: Pending Review</h3>' .
                                '<p class="text-blue-800 mb-3">Your team registration has been submitted and is currently under review. Our admin team will verify your information and notify you once the review is complete.</p>' .
                                '<p class="text-sm text-blue-700"><strong>Admin Contact:</strong> ' . htmlspecialchars($this->adminEmail ?? 'N/A') . '</p>' .
                                '</div>'
                            )),
                    ])
                    ->columns(1),
                    
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('request_update')
                        ->label('Send Update Request Email')
                        ->action('sendUpdateRequestEmail')
                        ->color('primary')
                        ->icon('heroicon-o-envelope')
                        ->requiresConfirmation()
                        ->modalHeading('Request Update')
                        ->modalDescription('Send an email to the admin requesting an update on your registration status.')
                        ->modalSubmitActionLabel('Send Email'),
                ])
                ->alignRight()
            ])
            ->statePath('teamRegistrationData');
    }

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
                            ->required()
                            ->live(),
                            
                        Forms\Components\TextInput::make('help_contact_email')
                            ->label('Contact Email')
                            ->email()
                            ->required()
                            ->live(),
                            
                        Forms\Components\TextInput::make('help_contact_phone')
                            ->label('Contact Phone')
                            ->tel()
                            ->required()
                            ->live(),
                            
                        Forms\Components\Select::make('help_purpose')
                            ->label('Business Type')
                            ->options([
                                'church' => 'Church',
                                'non_profit' => 'Non-Profit Organization',
                                'education' => 'Educational Institution',
                                'business' => 'Business',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->live(),
                            
                        Forms\Components\TextInput::make('help_contact_website')
                            ->label('Website')
                            ->url()
                            ->live(),
                            
                        Forms\Components\Textarea::make('help_disclaimer')
                            ->label('Disclaimer/Additional Information')
                            ->rows(3)
                            ->live(),
                            
                        Forms\Components\Checkbox::make('agree_to_terms')
                            ->label('I agree to the terms and conditions for messaging services')
                            ->helperText('By checking this box, you agree to comply with all applicable laws and regulations regarding messaging.')
                            ->required()
                            ->live(),
                            
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
                                'sms' => 'SMS',
                                'email' => 'Email',
                                
                            ])
                            ->default('sms')
                            ->reactive()
                            ->required(),
                            
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->visible(fn ($get) => $get('type') !== 'sms'),

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
                                'all' => 'All People',
                                'users_and_guests' => 'Specific People'
                            ])
                            ->default('all')
                            ->live(),

                        Forms\Components\Select::make('person_ids')
                            ->label('Select Specific Persons')
                            ->multiple()
                            ->searchable()
                            ->options(function () use ($team) {
                                $options = [];
                                
                                if ($team) {
                                    // Filter users based on message type
                                    if ($this->data['type'] ?? '' === 'sms') {
                                        $users = $team->users()
                                            ->whereNotNull('users.phone')
                                            ->where('users.phone', '!=', '')
                                            ->pluck('name', 'users.id');
                                    } else {
                                        $users = $team->users()->pluck('name', 'id');
                                    }
                                    
                                    // Add users with 'user_' prefix
                                    foreach ($users as $id => $name) {
                                        $options['user_' . $id] = $name . ' (User)';
                                    }
                                }
                                
                                // Filter guests based on message type
                                if ($this->data['type'] ?? '' === 'sms') {
                                    $guests = MsgGuest::whereNotNull('phone')
                                        ->where('phone', '!=', '')
                                        ->pluck('name', 'id');
                                } else {
                                    $guests = MsgGuest::pluck('name', 'id');
                                }
                                
                                // Add guests with 'guest_' prefix
                                foreach ($guests as $id => $name) {
                                    $options['guest_' . $id] = $name . ' (Guest)';
                                }
                                
                                return $options;
                            })
                            ->visible(fn ($get) => $get('recipient_type') === 'users_and_guests')
                            ->preload()
                            ->reactive()
                            ->loadingMessage('Loading persons...')
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
        $this->isTeamPending = $teamSetting && strtolower((string)$status) === 'pending';
        
        // Get admin email from team settings
        $this->adminEmail = $teamSetting?->help_contact_email ?? null;
        
        if ($this->isTeamVerified) {
            // Team is verified, show the compose form
            $this->form->fill();
            $this->checkIncompleteData();
        } elseif ($this->isTeamPending) {
            // Team registration is pending, show pending notice
            $this->teamRegistrationData = [
                'team_id' => $team->id,
                'team_name' => $team->name,
            ];
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
                'subject' => $data['subject'] ?? 'SMS Message',
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
            // Send to selected users and guests
            elseif ($data['recipient_type'] === 'users_and_guests' && !empty($data['person_ids'])) {
                Log::info('Sending to selected persons: ' . json_encode($data['person_ids']));
                
                // Separate user and guest IDs
                $userIds = [];
                $guestIds = [];
                
                foreach ($data['person_ids'] as $personId) {
                    if (strpos($personId, 'user_') === 0) {
                        $userIds[] = substr($personId, 5); // Remove 'user_' prefix
                    } elseif (strpos($personId, 'guest_') === 0) {
                        $guestIds[] = substr($personId, 6); // Remove 'guest_' prefix
                    }
                }
                
                // Send to selected users
                if (!empty($userIds)) {
                    $users = $team->users()->whereIn('users.id', $userIds)->get();
                    Log::info('Found ' . count($users) . ' matching users in team');
                    foreach ($users as $user) {
                        $this->createMessageDelivery($message, $user, 'user');
                        $recipientCount++;
                    }
                }
                
                // Send to selected guests
                if (!empty($guestIds)) {
                    $guests = MsgGuest::whereIn('id', $guestIds)->get();
                    Log::info('Found ' . count($guests) . ' matching guests');
                    foreach ($guests as $guest) {
                        $this->createMessageDelivery($message, $guest, 'guest');
                        $recipientCount++;
                    }
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
        // Return form based on team verification status
        if ($this->isTeamVerified) {
            return $this->composeForm($form);
        } elseif ($this->isTeamPending) {
            return $this->pendingRegistrationForm($form);
        } else {
            return $this->teamRegistrationForm($form);
        }
    }
    
    public function submitTeamRegistration()
    {
        try {
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
                    ->title('Validation Error')
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
            
            // Send notification to super admin
            $this->notifyAdminOfRegistration($teamSetting, $data['team_id']);
            
            Log::info('Team registration submitted for verification', ['team_id' => $data['team_id']]);
            
            Notification::make()
                ->title('Registration Submitted')
                ->body('Your team registration has been submitted for verification. You will be notified once it is approved.')
                ->success()
                ->persistent()
                ->send();
        } catch (\Throwable $e) {
            Log::error('Error in submitTeamRegistration: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            Notification::make()
                ->title('Error')
                ->body('An error occurred while submitting your registration: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    protected function notifyAdminOfRegistration(MsgTeamSetting $teamSetting, int $teamId): void
    {
        try {
            // Get the team to retrieve its name
            $team = Team::find($teamId);
            if (!$team) {
                Log::warning("Team not found for SMS registration notification: {$teamId}");
                return;
            }

            // Get all super admin users
            $superAdmins = User::whereHas('roles', function ($query) {
                $query->where('role_name', config('constants.SUPER_ADMIN_ROLE_TEXT'));
            })->get();

            if ($superAdmins->isEmpty()) {
                Log::warning('No super admin users found to notify about SMS registration');
                return;
            }

            // Send email to each super admin
            foreach ($superAdmins as $admin) {
                try {
                    Mail::to($admin)->send(new SmsRegistrationNotification($teamSetting, $team->name));
                    Log::info("SMS registration notification sent to admin: {$admin->email}");
                } catch (\Throwable $e) {
                    Log::error("Failed to send SMS registration notification to {$admin->email}: {$e->getMessage()}");
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error in notifyAdminOfRegistration: {$e->getMessage()}");
            Log::error($e->getTraceAsString());
        }
    }

    public function sendUpdateRequestEmail(): void
    {
        try {
            $user = auth()->user();
            if (!$user) {
                Notification::make()
                    ->title('Error')
                    ->body('You must be logged in to send an update request.')
                    ->danger()
                    ->send();
                return;
            }

            // Get the current team
            $site = \App\Http\Controllers\Controller::getClientFromHost();
            if (!$site || !$site->exists) {
                if ($user->currentTeam) {
                    $site = $user->currentTeam->site;
                }
            }

            if (!$site || !$site->exists) {
                Notification::make()
                    ->title('Error')
                    ->body('Could not determine the site for this request.')
                    ->danger()
                    ->send();
                return;
            }

            $team = $site->teams()->first();
            if (!$team) {
                Notification::make()
                    ->title('Error')
                    ->body('Could not find the team associated with this site.')
                    ->danger()
                    ->send();
                return;
            }

            // Get team settings to find admin email
            $teamSetting = MsgTeamSetting::query()->where('team_id', $team->id)->first();
            if (!$teamSetting || !$teamSetting->help_contact_email) {
                Notification::make()
                    ->title('Error')
                    ->body('Admin contact email not found in team settings.')
                    ->danger()
                    ->send();
                return;
            }

            // Send email to the admin
            $adminEmail = $teamSetting->help_contact_email;
            $subject = "Registration Status Update Request - {$team->name}";
            $body = "Hello,\n\n" .
                    "A user from {$team->name} has requested an update on their team registration status.\n\n" .
                    "User: {$user->name}\n" .
                    "Email: {$user->email}\n" .
                    "Team: {$team->name}\n" .
                    "Requested at: " . now()->format('Y-m-d H:i:s') . "\n\n" .
                    "Please review their registration status and provide an update.\n\n" .
                    "Best regards,\nPrasso System";

            Mail::raw($body, function ($message) use ($adminEmail, $subject) {
                $message->to($adminEmail)
                    ->subject($subject);
            });

            Log::info("Update request email sent to admin: {$adminEmail} for team: {$team->id}");

            Notification::make()
                ->title('Update Request Sent')
                ->body("Your update request has been sent to the admin at {$adminEmail}. They will review your registration status and contact you soon.")
                ->success()
                ->persistent()
                ->send();
        } catch (\Throwable $e) {
            Log::error('Error in sendUpdateRequestEmail: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            Notification::make()
                ->title('Error')
                ->body('Failed to send update request email. Please try again later.')
                ->danger()
                ->send();
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        $panel = \Filament\Facades\Filament::getCurrentPanel();
        return $panel && $panel->getId() === 'site-admin';
    }
}
