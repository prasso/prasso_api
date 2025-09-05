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
use Prasso\Messaging\Jobs\ProcessMsgDelivery;

class ComposeAndSendMessage extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';
    protected static ?string $navigationLabel = 'Compose & Send';
    protected static ?string $navigationGroup = 'Messaging';
    protected static string $view = 'filament.pages.compose-and-send-message';

    public ?array $data = [];

    public function form(Form $form): Form
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
            ;
        }
        return $form
            ->schema([
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
                                return $team ? $team->users()->pluck('name', 'users.id') : [];
                            })
                            ->visible(fn ($get) => $get('recipient_type') === 'users')
                            ->searchable()
                            ->preload()
                            ->loadingMessage('Loading team members...'),

                        Forms\Components\Select::make('guest_ids')
                            ->label('Select Guests')
                            ->multiple()
                            ->searchable()
                            ->options(MsgGuest::pluck('name', 'id'))
                            ->visible(fn ($get) => $get('recipient_type') === 'guests'),
                    ])
                    ->columns(1),

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
                    
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('send')
                        ->label('Send Message')
                        ->action('send')
                        ->color('primary')
                        ->icon('heroicon-o-paper-airplane'),
                ])->alignRight(),
            ])
            ->statePath('data');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function send()
    {
        $data = $this->form->getState();
        $recipientCount = 0;

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
            return;
        }

        // Create the message record first (only content fields)
        $message = MsgMessage::create([
            'team_id' => $team->id,
            'type' => $data['type'],
            'subject' => $data['subject'],
            'body' => $data['body'],
        ]);

        // Send to all users in the team
        if ($data['recipient_type'] === 'all') {
            $users = $team->users;
            foreach ($users as $user) {
                $this->createMessageDelivery($message, $user, 'user');
                $recipientCount++;
            }
        }
        // Send to selected users (still scoped to team members)
        elseif ($data['recipient_type'] === 'users' && !empty($data['user_ids'])) {
            // Ensure selected users are actually in the team
            $users = $team->users()->whereIn('users.id', $data['user_ids'])->get();
            foreach ($users as $user) {
                $this->createMessageDelivery($message, $user, 'user');
                $recipientCount++;
            }
        }
        // Send to selected guests
        elseif ($data['recipient_type'] === 'guests' && !empty($data['guest_ids'])) {
            $guests = MsgGuest::whereIn('id', $data['guest_ids'])->get();
            foreach ($guests as $guest) {
                $this->createMessageDelivery($message, $guest, 'guest');
                $recipientCount++;
            }
        }

        Notification::make()
            ->title('Message Sent')
            ->body("Message has been sent to {$recipientCount} recipients.")
            ->success()
            ->send();

        $this->form->fill();
    }

    protected function createMessageDelivery(MsgMessage $message, $recipient, string $recipientType): void
    {
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

        // Dispatch the job to process the delivery
        ProcessMsgDelivery::dispatch($delivery->id);

        Log::info("Message delivery created for {$recipientType} {$recipient->id}: {$message->subject}");
    }

    public static function shouldRegisterNavigation(): bool
    {
        $panel = \Filament\Facades\Filament::getCurrentPanel();
        return $panel && $panel->getId() === 'site-admin';
    }
}
