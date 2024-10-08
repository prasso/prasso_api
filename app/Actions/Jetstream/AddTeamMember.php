<?php

namespace App\Actions\Jetstream;

use App\Models\TeamInvitation;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\AddsTeamMembers;
use Laravel\Jetstream\Events\AddingTeamMember;
use Laravel\Jetstream\Events\TeamMemberAdded;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Rules\Role;
use Illuminate\Support\Facades\Log;

class AddTeamMember implements AddsTeamMembers
{
    /**
     * Add a new team member to the given team.
     *
     * @param  mixed  $user
     * @param  mixed  $team
     * @param  string  $email
     * @param  string|null  $role
     * @return void
     */
    public function add($user, $team, string $email, string $role = null)
    {
        Gate::forUser($user)->authorize('addTeamMember', $team);
        if (!isset($role))
        {
            $role=config('constants.TEAM_USER_ROLE');
        }
        $this->validate($team, $email, $role);

        $newTeamMember = User::where('email', $email)->first();

        if ($newTeamMember) {
            AddingTeamMember::dispatch($team, $newTeamMember);

            $team->users()->attach(
                $newTeamMember = Jetstream::findUserByEmailOrFail($email),
                ['role' => $role]
            );
            UserRole::create([
                'user_id' => $newTeamMember->id,
                'role_id' => $role === 'user' ? 1 : 2,
            ]);
            
       
            $newTeamMember->current_team_id = $team->id;
            $newTeamMember->save();
            
            TeamMemberAdded::dispatch($team, $newTeamMember);
           
        }


    }

    /**
     * Validate the add member operation.
     *
     * @param  mixed  $team
     * @param  string  $email
     * @param  string|null  $role
     * @return void
     */
    protected function validate($team, string $email, ?string $role)
    {
        Validator::make([
            'email' => $email,
            'role' => $role,
        ], $this->rules(), [
            'email.exists' => __('We were unable to find a registered user with this email address.'),
        ]
        )->validateWithBag('addTeamMember');
    }

    /**
     * Get the validation rules for adding a team member.
     *
     * @return array
     */
    protected function rules() {
        ## BEGIN EDIT - comment out exists:users check  ##
        return array_filter([
            'email' => [
                'required',
                'email'
            ],
            'role' => Jetstream::hasRoles()
                ? ['required', 'string', new Role]
                : null,
        ]);
       
    }

    /**
     * Ensure that the user is not already on the team.
     *
     * @param  mixed  $team
     * @param  string  $email
     * @return \Closure
     */
    protected function ensureUserIsNotAlreadyOnTeam($team, string $email)
    {
        return function ($validator) use ($team, $email) {
            $validator->errors()->addIf(
                $team->hasUserWithEmail($email),
                'email',
                __('This user already belongs to the team.')
            );
        };
    }
}
