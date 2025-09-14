<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TabsResource\Pages;
use App\Models\Tabs;
use App\Models\Apps;
use App\Models\Site;
use Filament\Forms;
use Filament\Forms\Components as Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

class TabsResource extends Resource
{
   
  
    protected static ?string $navigationGroup = 'Mobile App Configuration';
    
    protected static ?string $model = Tabs::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    
    protected static ?string $pluralModelLabel = 'Tabs';
    
    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Select::make('app_id')
                    ->label('App')
                    ->required()
                    ->options(function () {
                        $authUser = auth()->user();
                        $siteId = $authUser?->getUserOwnerSiteId();
                        if (!$siteId) return [];
                        $site = Site::find($siteId);
                        $team = $site?->teams()->first();
                        if (!$team) return [];
                        return Apps::where('team_id', $team->id)->orderBy('app_name')->pluck('app_name', 'id');
                    }),
                Components\TextInput::make('label')
                    ->required(),
                Components\TextInput::make('icon')
                    ->maxLength(255)
                    ->label('Icon (heroicon class)')
                    ->nullable(),
                Components\TextInput::make('page_title')
                    ->label('Page Title')
                    ->nullable(),
                Components\TextInput::make('page_url')
                    ->label('Page URL')
                    ->required(),
                Components\TextInput::make('request_header')
                    ->label('Request Header')
                    ->nullable(),
                Components\TextInput::make('parent')
                    ->label('Parent')
                    ->nullable(),
                Components\Toggle::make('restrict_role')
                    ->label('Restrict by Role')
                    ->default(false),
                Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->label('Sort Order'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('app.app_name')->label('App')->sortable()->searchable(),
                Columns\TextColumn::make('label')->sortable()->searchable(),
                Columns\TextColumn::make('page_title')->label('Title')->sortable()->searchable(),
                Columns\TextColumn::make('page_url')->label('URL')->searchable(),
                Columns\IconColumn::make('restrict_role')->boolean()->label('Restricted'),
                Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
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
                    return $query->whereHas('app', function ($q) use ($team) {
                        $q->where('team_id', $team->id);
                    });
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTabs::route('/'),
            'create' => Pages\CreateTabs::route('/create'),
            'edit' => Pages\EditTabs::route('/{record}/edit'),
        ];
    }
}
