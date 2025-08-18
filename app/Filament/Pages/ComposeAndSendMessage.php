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

class ComposeAndSendMessage extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationLabel = 'Compose & Send';

    protected static ?string $navigationGroup = 'Messaging';

    protected static string $view = 'filament.pages.compose-and-send-message';

    public ?int $message_id = null;

    public array $user_ids = [];

    public array $guest_ids = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        $user = auth()->user();
        $userOptions = [];
        try {
            $siteId = $user?->getUserOwnerSiteId();
            if ($siteId) {
                $site = Site::find($siteId);
                $team = $site?->teams()->first();
                if ($team) {
                    $userIds = TeamUser::where('team_id', $team->id)->pluck('user_id');
                    $userOptions = User::whereIn('id', $userIds)->orderBy('name')->pluck('name', 'id')->toArray();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Compose page user options failed: '.$e->getMessage());
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Message')
                    ->schema([
                        Forms\Components\Select::make('message_id')
                            ->label('Message')
                            ->options(fn () => MsgMessage::orderBy('id', 'desc')->pluck('subject', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(1),
                Forms\Components\Section::make('Recipients')
                    ->schema([
                        Forms\Components\MultiSelect::make('user_ids')
                            ->label('Users')
                            ->options($userOptions)
                            ->searchable()
                            ->preload(),
                        Forms\Components\MultiSelect::make('guest_ids')
                            ->label('Guests')
                            ->options(fn () => MsgGuest::orderBy('name')->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->preload(),
                    ])->columns(2),
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('send')
                        ->label('Send')
                        ->color('primary')
                        ->action('submit')
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        if (empty($data['message_id']) || (empty($data['user_ids']) && empty($data['guest_ids']))) {
            Notification::make()
                ->title('Select a message and at least one recipient')
                ->danger()
                ->send();
            return;
        }

        try {
            $token = auth()->user()?->personal_access_token ?? null;
            if (!$token) {
                Notification::make()->title('Missing API token')->danger()->send();
                return;
            }

            $response = Http::withToken($token)
                ->acceptJson()
                ->post(url('/api/messages/send'), [
                    'message_id' => $data['message_id'],
                    'user_ids' => array_values($data['user_ids'] ?? []),
                    'guest_ids' => array_values($data['guest_ids'] ?? []),
                ]);

            if ($response->successful()) {
                Notification::make()->title('Message queued for delivery')->success()->send();
                $this->reset(['message_id', 'user_ids', 'guest_ids']);
                $this->form->fill();
            } else {
                $err = $response->json('message') ?? $response->body();
                Notification::make()->title('Send failed: ' . $err)->danger()->send();
            }
        } catch (\Throwable $e) {
            Notification::make()->title('Unexpected error: ' . $e->getMessage())->danger()->send();
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        $panel = \Filament\Facades\Filament::getCurrentPanel();
        return $panel && $panel->getId() === 'site-admin';
    }
}
