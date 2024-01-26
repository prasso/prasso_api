<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Team;
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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Hidden::make('id'),
                Components\Hidden::make('version')->default('v1'),
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
                        return Team::pluck('name', 'id');
                    }),

                Components\TextInput::make('profile_photo_path')
                    ->nullable(),
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
            //
        ];
    }

    public static function getPages(): array
    {  
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            //'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
