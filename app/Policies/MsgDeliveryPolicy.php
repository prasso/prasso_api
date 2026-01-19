<?php

namespace App\Policies;

use App\Models\User;
use Prasso\Messaging\Models\MsgDelivery;

class MsgDeliveryPolicy
{
    /**
     * Determine if the user can view the delivery (and its conversation)
     */
    public function viewDelivery(User $user, MsgDelivery $delivery): bool
    {
        // User must own the team that the delivery belongs to
        return $user->teams()
            ->where('teams.id', $delivery->team_id)
            ->exists();
    }
}
