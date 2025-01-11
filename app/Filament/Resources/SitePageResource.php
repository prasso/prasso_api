<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SitePageResource\Pages;
use App\Models\SitePages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->relationship('site', 'site_name')
                    ->required(),
                Forms\Components\TextInput::make('section')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
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
                        return [0 => 'Top Level'] + \App\Models\SitePages::pluck('title', 'id')->toArray();
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
                Tables\Actions\DeleteAction::make(),
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
}
