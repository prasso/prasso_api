<?php

namespace App\Actions\Fortify;

use App\Models\TeamInvitation;
use App\Models\Team;
use App\Models\User;
use App\Models\TeamUser;
use App\Models\Site;
use App\Models\TeamSite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Events\TeamMemberAdded;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Laravel\Jetstream\Jetstream;
use Illuminate\Validation\ValidationException;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    private $site_team_id, $site, $team, $invitation;

    /**
     * Create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
         Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'billing_address' => ['nullable', 'string', function ($attribute, $value, $fail) {
                if (!empty($value)) {
                    $fail(''); //this is the bot eliminator, billing_address is not visible
                }
            }],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
            'phone' => '',
            'version' => ''
        ])->validate();

        $this->site = Controller::getClientFromHost();
        if (!$this->site->supports_registration) {
            throw ValidationException::withMessages([
                'email' => ['This site does not support registration.'],
            ]);
        }
        
        $this->invitation = TeamInvitation::where('email', $input['email'])->first();
        if ($this->invitation){
            //get the team from the invitation
            $this->team = Team::find($this->invitation->team_id);
        }
        else{
            //get the team from the site
            $this->team = $this->site->teamFromSite();
        }

     info('team: '.json_encode($this->team));
               
        $this->site_team_id = $this->team->id;
            
        if ($this->site->invitation_only) {

            if (!$this->invitation) {
                // Throw an exception if the user is not invited
                throw ValidationException::withMessages([
                    'email' => ['This site is invitation-only. Your email is not on the invitation list.'],
                ]);
            }
        }


        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'phone' => '',
                'version' => '',
            ]), function (User $user) use ($input) {

                // Default: add all users to Prasso team at user level
                $prasso_team = Team::find(1);

                $prasso_team->users()->syncWithoutDetaching([
                    $user->id => ['role' => 'user']
                ]);

                // Only attach to team if there's no invitation (invitation handling comes later)
                if (!isset($this->invitation)) {
                    $this->team->users()->attach(
                        $user,
                        ['role' => 'user']
                    );
                    $user->current_team_id = $this->team->id;
                    $user->save();
                    TeamMemberAdded::dispatch($this->team, $user);
                }

                try{
                $user->sendWelcomeEmail($this->site_team_id);
                }
                catch(\Throwable $e){
                    Log::info("Error sending welcome email: {$this->site->host}");
                    Log::info($e);
                }

                // - if there's an invite, attach them accordingly 
                if (isset($this->invitation)) {
                    // Check if the user is already a member of the team
                    if ($this->team->users()->where('user_id', $user->id)->exists()) {
                        // Update the role if the user is already a member
                        $this->team->users()->updateExistingPivot($user->id, ['role' => $this->invitation->role]);
                    } else {
                        // Attach the user to the team with the specified role if they are not already a member
                        $this->team->users()->attach($user, ['role' => $this->invitation->role]);
                    }
                    
                    TeamMemberAdded::dispatch($this->team, $user);
                    
                    $user->current_team_id = $this->team->id;
                    $user->save();
                    $this->invitation->delete();
                }
                else
                {
                    if (!$this->site->supports_registration) {
                        TeamUser::addToBaseTeam($user);
                    }
                }
              
            });
        });
    }
    /**
     * Create a personal team for the user.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    protected function createTeam(User $user) {
        $new_team = Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0] . "'s Team",
            'personal_team' => true,
            'phone' => $user->phone,
        ]);

        $user->ownedTeams()->save($new_team);
        $user->refresh();
        return $new_team->id;
    }
}
