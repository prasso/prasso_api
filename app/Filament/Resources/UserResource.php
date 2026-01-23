<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\Site;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;

use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Facades\Filament;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Site Management';

    protected static ?int $navigationSort = 25;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Hidden::make('id'),
                Components\Hidden::make('version')
                    ->default('v1')
                    ->dehydrateStateUsing(fn ($state) => $state ?? 'v1'),
                Components\TextInput::make('name')
                    ->autofocus()
                    ->required(),
                    
                Components\TextInput::make('email')
                    ->email()
                    ->required(),
                    
                Components\TextInput::make('password')
                    ->password()
                    ->required(),

                Components\Select::make('current_team_id')
                    ->required()
                    ->placeholder('Select a team')
                    ->options(function () {
                        $user = \Illuminate\Support\Facades\Auth::user();
                        if (!$user) return [];
                        
                        // For super admins, show all teams the user belongs to
                        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                            // Get the user being edited (from the URL)
                            $recordId = request()->route('record');
                            if ($recordId) {
                                $editedUser = \App\Models\User::find($recordId);
                                if ($editedUser) {
                                    // Show all teams this user belongs to
                                    return \App\Models\Team::whereHas('users', function($query) use ($editedUser) {
                                        $query->where('users.id', $editedUser->id);
                                    })->pluck('name', 'id');
                                }
                            }
                        }
                        
                        // For sub-admins, show only teams from their site
                        $siteId = method_exists($user, 'getUserOwnerSiteId') ? $user->getUserOwnerSiteId() : null;
                        if (!$siteId) return [];
                        $site = Site::find($siteId);
                        $team = $site?->teams()->first();
                        return $team ? Team::where('id', $team->id)->pluck('name', 'id') : [];
                    }),

                Components\FileUpload::make('profile_photo_path')
                    ->label('Profile Photo')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '16:9',
                        '4:3',
                        '1:1',
                    ])
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml'])
                    ->maxSize(5120) // 5MB
                    ->directory(function () {
                        $user = auth()->user();
                        return 'photos-' . ($user ? $user->id : 'default');
                    })
                    ->disk('s3')
                    ->visibility('public')
                    ->imagePreviewHeight('250')
                    ->loadingIndicatorPosition('left')
                    ->panelAspectRatio('2:1')
                    ->panelLayout('integrated')
                    ->removeUploadedFileButtonPosition('right')
                    ->uploadButtonPosition('left')
                    ->uploadProgressIndicatorPosition('left'),
                Components\TextInput::make('phone')     
                    ->required(),

                Components\DateTimePicker::make('created_at')
                    ->default(now())
                    ->disabled(),

                Components\DateTimePicker::make('updated_at')
                    ->default(null)
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID'),
                ImageColumn::make('profile_photo_path'),
                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')->label('Created At'),
                TextColumn::make('updated_at')->label('Updated At')
                // Add more columns as needed
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('team')
                    ->label('Filter by Team')
                    ->multiple()
                    ->options(function () {
                        $user = \Illuminate\Support\Facades\Auth::user();
                        
                        // If user is super admin, show all teams
                        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                            return Team::pluck('name', 'id');
                        }
                        
                        // Otherwise, show only teams associated with the current user
                        if ($user) {
                            $siteId = method_exists($user, 'getUserOwnerSiteId') ? $user->getUserOwnerSiteId() : null;
                            if ($siteId) {
                                $site = Site::find($siteId);
                                // Get team IDs through the pivot table to avoid ambiguous column references
                                $teamIds = [];
                                if ($site) {
                                    // Use the relationship but select specific columns to avoid ambiguity
                                    $teamIds = $site->teams()->select('teams.id')->pluck('teams.id')->toArray();
                                }
                                return Team::whereIn('id', $teamIds)->pluck('name', 'id');
                            }
                        }
                        
                        return [];
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['values'],
                            fn (Builder $query, $values): Builder => $query->whereHas(
                                'team_member',
                                fn (Builder $query) => $query->whereIn('team_id', $values)
                            )
                        );
                    })
                    ->preload()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);

        
    }
    

    public static function getRelations(): array
    {
        return [
            RelationManagers\TeamsRelationManager::class,
            RelationManagers\RolesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {  
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'import-preview' => Pages\ImportPreview::route('/import-preview'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        try {
            $panel = Filament::getCurrentPanel();
            if ($panel && ($panel->getId() === 'admin' || $panel->getId() === 'site-admin') && $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return $query; // full access in admin and site-admin panels for super-admins
            }
        } catch (\Throwable $e) {}

        try {
            $siteId = method_exists($user, 'getUserOwnerSiteId') ? $user->getUserOwnerSiteId() : null;
            if ($siteId) {
                $site = Site::find($siteId);
                $team = $site?->teams()->first();
                if ($team) {
                    $userIds = TeamUser::where('team_id', $team->id)->pluck('user_id');
                    return $query->whereIn('id', $userIds);
                }
            }
        } catch (\Throwable $e) {}

        return $query->whereRaw('1 = 0');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $panel = Filament::getCurrentPanel();
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$panel || !$user) return false;
        if ($panel->getId() === 'site-admin') return true;
        if ($panel->getId() === 'admin' && method_exists($user, 'isSuperAdmin')) return $user->isSuperAdmin();
        return false;
    }
}
