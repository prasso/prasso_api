<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SitePageResource\Pages;
use App\Models\SitePages;
use App\Models\Site;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;

class SitePageResource extends Resource
{
    protected static ?string $model = SitePages::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Site Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('fk_site_id')
                    ->label('Site')
                    ->options(function () {
                        // Prefer the site resolved from the current host
                        $currentSite = \App\Http\Controllers\Controller::getClientFromHost();
                        $siteId = $currentSite?->id;
                        // Fallback to user's owner site id if host-based resolution fails
                        if (!$siteId) {
                            $user = auth()->user();
                            $siteId = $user?->getUserOwnerSiteId();
                        }
                        return $siteId ? Site::where('id', $siteId)->pluck('site_name', 'id')->toArray() : [];
                    })
                    ->default(function () {
                        $currentSite = \App\Http\Controllers\Controller::getClientFromHost();
                        return $currentSite?->id ?? auth()->user()?->getUserOwnerSiteId();
                    })
                    ->required(),
                Forms\Components\TextInput::make('section')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->rows(12)
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('url')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('headers')
                    ->maxLength(255),
                Forms\Components\TextInput::make('masterpage')
                    ->maxLength(255),
                Forms\Components\TextInput::make('template')
                    ->maxLength(255),
                Forms\Components\TextInput::make('style')
                    ->maxLength(255),
                Forms\Components\Toggle::make('login_required')
                    ->required(),
                Forms\Components\TextInput::make('user_level')
                    ->numeric(),
                Forms\Components\TextInput::make('where_value')
                    ->maxLength(255),
                Forms\Components\Toggle::make('page_notifications_on')
                    ->required(),
                Forms\Components\Select::make('menu_id')
                    ->label('Parent Menu')
                    ->options(function () {
                        $currentSite = \App\Http\Controllers\Controller::getClientFromHost();
                        $siteId = $currentSite?->id ?? auth()->user()?->getUserOwnerSiteId();
                        $options = [];
                        if ($siteId) {
                            $options = \App\Models\SitePages::where('fk_site_id', $siteId)->pluck('title', 'id')->toArray();
                        }
                        return [0 => 'Top Level'] + $options;
                    })
                    ->searchable()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('site.site_name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('section')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('parentMenu.title')
                    ->label('Parent Menu')
                    ->sortable(),
                Tables\Columns\IconColumn::make('login_required')
                    ->boolean(),
                Tables\Columns\IconColumn::make('page_notifications_on')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('site')
                    ->relationship('site', 'site_name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (SitePages $record) {
                        $data = $record->toArray();
                        unset($data['id'], $data['created_at'], $data['updated_at']);
                        $data['title'] = ($data['title'] ?? 'Page') . ' (Copy)';
                        $data['url'] = ($data['url'] ?? 'page') . '-copy-' . substr(uniqid(), -4);
                        SitePages::create($data);
                        Notification::make()->title('Page duplicated')->success()->send();
                    }),
                Tables\Actions\Action::make('togglePublish')
                    ->label(fn (SitePages $record) => ($record->menu_id ?? 0) >= 0 ? 'Unpublish' : 'Publish')
                    ->icon(fn (SitePages $record) => ($record->menu_id ?? 0) >= 0 ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (SitePages $record) => ($record->menu_id ?? 0) >= 0 ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function (SitePages $record) {
                        if (($record->menu_id ?? 0) >= 0) {
                            $record->menu_id = -1; // hide from menus
                            $record->save();
                            Notification::make()->title('Page unpublished')->success()->send();
                        } else {
                            $record->menu_id = 0; // show in top-level menu by default
                            $record->save();
                            Notification::make()->title('Page published')->success()->send();
                        }
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        try {
            $panel = Filament::getCurrentPanel();
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin() && $panel && $panel->getId() === 'admin') {
                return $query; // full access in admin panel
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // For the Site Admin panel, scope strictly to the site derived from the current host
        try {
            $currentSite = \App\Http\Controllers\Controller::getClientFromHost();
            if ($currentSite?->id) {
                return $query->where('fk_site_id', $currentSite->id);
            }
        } catch (\Throwable $e) {
            // ignore and fallback below
        }

        // Fallback: use user's owner site id
        try {
            $fallbackSiteId = $user->getUserOwnerSiteId();
            if ($fallbackSiteId) {
                return $query->where('fk_site_id', $fallbackSiteId);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return $query->whereRaw('1 = 0');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSitePages::route('/'),
            'create' => Pages\CreateSitePage::route('/create'),
            'edit' => Pages\EditSitePage::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $panel = Filament::getCurrentPanel();
        $user = auth()->user();
        if (!$panel || !$user) {
            return false;
        }
        if ($panel->getId() === 'site-admin') {
            return true;
        }
        if ($panel->getId() === 'admin') {
            return method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        }
        return false;
    }
}
