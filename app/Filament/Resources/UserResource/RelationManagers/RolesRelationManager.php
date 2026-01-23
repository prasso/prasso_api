<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Role;

class RolesRelationManager extends RelationManager
{
    protected static string $relationship = 'roles';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('role_id')
                    ->label('Role')
                    ->options(Role::pluck('role_name', 'id'))
                    ->required()
                    ->reactive(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('role_name')
            ->columns([
                Tables\Columns\TextColumn::make('role_name')
                    ->label('Role'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Assigned')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Assign Role')
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        Forms\Components\Select::make('recordId')
                            ->label('Role')
                            ->options(Role::pluck('role_name', 'id'))
                            ->required()
                            ->searchable(),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Remove Role'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
