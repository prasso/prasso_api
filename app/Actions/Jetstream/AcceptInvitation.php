<?php

namespace App\Actions\Jetstream;

use App\Models\TeamInvitation;
use App\Models\UserRole;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Contracts\AddsTeamMembers;
use Laravel\Jetstream\Events\AddingTeamMember;
use Laravel\Jetstream\Events\TeamMemberAdded;
use Laravel\Jetstream\Jetstream;

class AcceptInvitation
{
    /**
     * Accept a team invitation.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TeamInvitation  $invitation
     * @return void
     */
    public function accept($user, TeamInvitation $invitation)
    {
        $this->ensureUserCanAcceptInvitation($user, $invitation);

        $invitation->team->users()->attach(
            $user->id, ['role' => $invitation->role]
        );

        // Assign the appropriate role based on the invitation
        $roleId = $invitation->role === 'user' 
            ? config('constants.USER_ROLE', 1) 
            : config('constants.INSTRUCTOR', 2);
            
        if (!UserRole::where('user_id', $user->id)
                    ->where('role_id', $roleId)
                    ->exists()) {
            UserRole::create([
                'user_id' => $user->id,
                'role_id' => $roleId,
            ]);
        }

        $invitation->delete();

        TeamMemberAdded::dispatch($invitation->team, $user);
    }

    /**
     * Ensure that the user can accept the given invitation.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TeamInvitation  $invitation
     * @return void
     */
    protected function ensureUserCanAcceptInvitation($user, $invitation)
    {
        if ($invitation->email !== $user->email) {
            throw new AuthorizationException;
        }

        if (! $invitation->team->hasUserWithEmail($user->email)) {
            throw ValidationException::withMessages([
                'email' => [__('You were not invited to this team.')],
            ]);
        }
    }
}
