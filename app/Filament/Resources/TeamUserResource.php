<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamUserResource\Pages;
use App\Filament\Resources\TeamUserResource\RelationManagers;
use App\Models\TeamUser;
use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use App\Models\Site;
use Filament\Forms\Form;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Facades\Filament;


class TeamUserResource extends Resource
{
    protected static ?string $model = TeamUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Hidden::make('id'),
                Components\Select::make('user_id')->label('User')
                    ->required()
                    ->placeholder('Select a user')
                    ->options(function () {
                        $authUser = auth()->user();
                        $siteId = $authUser?->getUserOwnerSiteId();
                        if (!$siteId) return [];
                        $site = Site::find($siteId);
                        $team = $site?->teams()->first();
                        if (!$team) return [];
                        $userIds = TeamUser::where('team_id', $team->id)->pluck('user_id');
                        return User::whereIn('id', $userIds)->pluck('name', 'id');
                    }),
                Components\Select::make('team_id')->label('Team')
                    ->required()
                    ->placeholder('Select a Team')
                    ->options(function () {
                        $authUser = auth()->user();
                        $siteId = $authUser?->getUserOwnerSiteId();
                        if (!$siteId) return [];
                        $site = Site::find($siteId);
                        $team = $site?->teams()->first();
                        return $team ? Team::where('id', $team->id)->pluck('name', 'id') : [];
                    }),
                
                Components\Select::make('role')->label('Role')
                    ->required()
                    ->placeholder('Select a Role')
                    ->options(function () {
                        return Role::pluck('role_name', 'role_name');
                    })    
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('team.name'),
                Tables\Columns\TextColumn::make('role')
            ])
            ->filters([
                SelectFilter::make('team_id')
                ->options(function () {
                    $authUser = auth()->user();
                    $siteId = $authUser?->getUserOwnerSiteId();
                    if (!$siteId) return [];
                    $site = Site::find($siteId);
                    $team = $site?->teams()->first();
                    return $team ? Team::where('id', $team->id)->pluck('name', 'id') : [];
                })
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
            RelationManagers\TeamRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeamUsers::route('/'),
            'create' => Pages\CreateTeamUser::route('/create'),
            'edit' => Pages\EditTeamUser::route('/{record}/edit'),
        ];
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
            if ($panel && $panel->getId() === 'admin' && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return $query; // full access in admin panel
            }
        } catch (\Throwable $e) {}

        try {
            $siteId = $user->getUserOwnerSiteId();
            if ($siteId) {
                $site = Site::find($siteId);
                $team = $site?->teams()->first();
                if ($team) {
                    return $query->where('team_id', $team->id);
                }
            }
        } catch (\Throwable $e) {}

        return $query->whereRaw('1 = 0');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $panel = Filament::getCurrentPanel();
        $user = auth()->user();
        if (!$panel || !$user) return false;
        if ($panel->getId() === 'site-admin') return true;
        if ($panel->getId() === 'admin') return method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        return false;
    }
}
