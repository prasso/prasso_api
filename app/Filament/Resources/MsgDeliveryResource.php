<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MsgDeliveryResource\Pages;
use App\Models\Site;
use App\Models\TeamUser;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Prasso\Messaging\Models\MsgDelivery;

class MsgDeliveryResource extends Resource
{
    protected static ?string $model = MsgDelivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Messaging';

    protected static ?string $navigationLabel = 'Messages Sent';

    protected static ?int $navigationSort = 22;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('message.subject')->label('Message')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('recipient_type')->label('Recipient Type')->badge(),
                Tables\Columns\TextColumn::make('channel')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'queued' => 'warning',
                        'sent' => 'success',
                        'delivered' => 'success',
                        'failed' => 'danger',
                        'skipped' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('provider_message_id')->label('Provider ID')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('error')
                    ->label('Error Message')
                    ->formatStateUsing(fn ($state, $record) => $record && $record->status === 'failed' ? $state : '')
                    ->wrap()
                    ->limit(100)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (!$state || strlen($state) <= 100) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('sent_at')->dateTime()->since()->sortable(),
                Tables\Columns\TextColumn::make('delivered_at')->dateTime()->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('failed_at')->dateTime()->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'queued' => 'Queued',
                        'sent' => 'Sent',
                        'delivered' => 'Delivered',
                        'failed' => 'Failed',
                        'skipped' => 'Skipped',
                    ]),
                SelectFilter::make('channel')
                    ->options([
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'voice' => 'Voice',
                    ]),
                Filter::make('sent_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Sent From'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Sent Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('sent_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('sent_at', '<=', $date));
                    }),
            ])
            ->defaultSort('sent_at', 'desc')
            ->actions([
                Action::make('replies')
                    ->label('Replies')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('gray')
                    ->modalContent(fn ($record) => view('filament.tables.row-details.replies', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),
            ])
            ->paginationPageOptions([25, 50, 100]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // If Super Admin viewing from the global admin panel, do not scope
        try {
            $panel = \Filament\Facades\Filament::getCurrentPanel();
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin() && $panel && $panel->getId() === 'admin') {
                return $query; // full access in admin panel
            }
        } catch (\Throwable $e) {
            // ignore and continue with scoping
        }

        // Scope to site-owned team users if possible
        try {
            $siteId = $user->getUserOwnerSiteId();
            if ($siteId) {
                $site = Site::find($siteId);
                if ($site) {
                    $team = $site->teams()->first();
                    if ($team) {
                        $userIds = TeamUser::where('team_id', $team->id)->pluck('user_id');
                        if ($userIds->count() > 0) {
                            $query = $query->where(function (Builder $q) use ($userIds) {
                                $q->where(function (Builder $qq) use ($userIds) {
                                    $qq->where('recipient_type', 'user')
                                       ->whereIn('recipient_id', $userIds);
                                });
                                // Guests scoping TBD
                            });
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // If scoping fails, fall back to empty to avoid cross-tenant leakage
            return $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMsgDeliveries::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $panel = \Filament\Facades\Filament::getCurrentPanel();
        $user = auth()->user();
        if (!$panel || !$user) {
            return false;
        }
        if ($panel->getId() === 'site-admin') {
            return true; // visible to site admins in site-admin panel
        }
        if ($panel->getId() === 'admin') {
            return method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        }
        return false;
    }
}
