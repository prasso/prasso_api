<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserRoleResource\Pages;
use App\Filament\Resources\UserRoleResource\RelationManagers;
use App\Models\UserRole;
use App\Models\User;
use App\Models\Role;
use App\Models\Team;
use App\Models\TeamSite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;


class UserRoleResource extends Resource
{
    protected static ?string $model = UserRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'My Site';
    
    protected static ?int $navigationSort = 28;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Hidden::make('id'),
                Components\Select::make('user_id')->label('User')
                    ->required()
                    ->placeholder('Select a user')
                    ->options(function () {
                        $user = auth()->user();
                        // For superadmins, show all users with roles
                        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                            return User::whereHas('roles')
                                ->pluck('name', 'id');
                        }
                        
                        // For sub-admins, only show users from their site
                        if (!$user) return [];
                        $siteId = $user->current_team_id;
                        if (!$siteId) return [];
                        $teamIds = TeamSite::where('site_id', $siteId)
                            ->pluck('team_id')
                            ->toArray();
                            
                        return User::whereHas('teams', function($query) use ($teamIds) {
                                $query->whereIn('teams.id', $teamIds);
                            })
                            ->whereHas('roles')
                            ->pluck('name', 'id');
                    })
                    ->searchable(),
                Components\Select::make('role_id')->label('Team')
                    ->required()
                    ->placeholder('Select a Role')
                    ->options(function () {
                        return Role::pluck('role_name', 'id');
                    }),
                  
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
                // Skip filtering for superadmins
                if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                    return;
                }
                
                // For sub-admins, filter users by their current site
                if (!$user) return;
                $siteId = $user->current_team_id;
                if (!$siteId) return;
                $teamIds = TeamSite::where('site_id', $siteId)
                    ->pluck('team_id')
                    ->toArray();
                
                $query->whereHas('user.teams', function($q) use ($teamIds) {
                    $q->whereIn('teams.id', $teamIds);
                });
            })
            ->columns([
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('team_role.role_name')
            ])
            ->filters([
                SelectFilter::make('role_id')
                ->options(function () {
                    return Role::pluck('role_name', 'id');
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
            RelationManagers\UserRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserRoles::route('/'),
            'create' => Pages\CreateUserRole::route('/create'),
            'edit' => Pages\EditUserRole::route('/{record}/edit'),
        ];
    }
}
