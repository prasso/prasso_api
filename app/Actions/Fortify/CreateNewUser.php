<?php

namespace App\Actions\Fortify;

use App\Models\Invitation;
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

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;
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
        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'phone' => '',
                'version' => '',
            ]), function (User $user) use ($input) {

                $site = Controller::getClientFromHost();
                $site_team_id=config('constants.PRASSO_TEAM_ID');
                //get the team from the site
                if ($site->supports_registration) {
                    $team = $site->teamFromSite();
                    $site_team_id = $team->id;
                    $team->users()->attach(
                        $user,
                        ['role' => 'user']
                    );
                    $user->current_team_id = $team->id;
                    $user->save();
                    TeamMemberAdded::dispatch($team, $user);
                }
                else{
                    $site_team_id = $this->createTeam($user,$site);
                }
                try{
                $user->sendWelcomeEmail($site_team_id);
                }
                catch(\Throwable $e){
                    Log::info("Error sending welcome email: {$site->host}");
                    Log::info($e);
                }
                ## BEGIN EDIT - if there's an invite, attach them accordingly ##
                if (isset($input['invite'])) {
                    if ($invitation = Invitation::where('code', $input['invite'])->first()) {
                        if ($team = $invitation->team) {
                            $team->users()->attach(
                                $user,
                                ['role' => $invitation->role]
                            );
                            $user->current_team_id = $team->id;
                            $user->save();
                            TeamMemberAdded::dispatch($team, $user);
                            $invitation->delete();
                        }
                    }
                }
                else
                {
                    if (!$site->supports_registration) {
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
