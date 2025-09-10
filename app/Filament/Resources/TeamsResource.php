<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamsResource\Pages;
use App\Filament\Resources\TeamsResource\RelationManagers;
use App\Models\Team;
use App\Models\Site;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Apps;
use App\Models\TeamSite;
use App\Models\TeamUser;
use App\Models\User;
use Filament\Forms\Components;
use Filament\Facades\Filament;

class TeamsResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'My Site';
    
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Toggle::make('personal_team')
        
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\ToggleColumn::make('personal_team'),
        ])
        ->filters([
            //
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
            RelationManagers\TeamUsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeams::route('/create'),
            'edit' => Pages\EditTeams::route('/{record}/edit'),
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
            if ($panel && ($panel->getId() === 'admin' || $panel->getId() === 'site-admin') && $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return $query; // full access in admin and site-admin panels for super-admins
            }
        } catch (\Throwable $e) {}

        try {
            $siteId = $user->getUserOwnerSiteId();
            if ($siteId) {
                $site = Site::find($siteId);
                $team = $site?->teams()->first();
                if ($team) {
                    return $query->where('id', $team->id);
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
        if ($panel->getId() === 'admin' && method_exists($user, 'isSuperAdmin')) return $user->isSuperAdmin();
        return false;
    }
}
