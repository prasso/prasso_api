<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeamsRelationManager extends RelationManager
{
    protected static string $relationship = 'teams';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'member' => 'Member',
                        'user' => 'User',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Team Name'),
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('Role'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Team')
                            ->required(),
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options([
                                'admin' => 'Admin',
                                'member' => 'Member',
                                'user' => 'User',
                            ])
                            ->default('member')
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options([
                                'admin' => 'Admin',
                                'member' => 'Member',
                                'user' => 'User',
                            ])
                            ->required(),
                    ]),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
