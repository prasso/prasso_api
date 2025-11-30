<?php

namespace App\Policies;

use App\Models\User;
use Prasso\Messaging\Models\MsgMessage;

class MsgMessagePolicy
{
    /**
     * Determine if the user can view the message and its conversation
     */
    public function viewMessage(User $user, MsgMessage $message): bool
    {
        // Super-admins can view any message
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }
        
        // Regular users must own the team that the message belongs to
        return $user->teams()
            ->where('teams.id', $message->team_id)
            ->exists();
    }
}
