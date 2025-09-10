<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\TeamUser;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    protected function afterCreate(): void
    {
        // Create TeamUser record to associate user with team
        if ($this->record && $this->record->current_team_id) {
            try {
                // Default role for new users
                $defaultRole = 'member';
                
                // Check if TeamUser record already exists
                $teamUser = TeamUser::where('user_id', $this->record->id)
                    ->where('team_id', $this->record->current_team_id)
                    ->first();
                    
                if (!$teamUser) {
                    // Create new TeamUser record
                    TeamUser::create([
                        'user_id' => $this->record->id,
                        'team_id' => $this->record->current_team_id,
                        'role' => $defaultRole
                    ]);
                }
                
                // Send welcome email to the new user
                try {
                    $this->record->sendWelcomeEmail($this->record->current_team_id);
                    
                    Notification::make()
                        ->title('Welcome email sent')
                        ->body('A welcome email has been sent to ' . $this->record->email)
                        ->success()
                        ->send();
                } catch (\Throwable $e) {
                    Log::error('Error sending welcome email: ' . $e->getMessage());
                    
                    Notification::make()
                        ->title('Error sending welcome email')
                        ->body('User was created but welcome email could not be sent')
                        ->warning()
                        ->send();
                }
            } catch (\Exception $e) {
                Notification::make()
                    ->title('Error adding user to team')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        }
    }
}
