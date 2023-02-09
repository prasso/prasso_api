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
            'password' => $this->passwordRules(),
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
                //get the site from the host
                $host = request()->getHttpHost();

                $site = Site::getClient($host);
                if ($site == null)
                {
                    Log::error('create new user - Site not found for host: ' . $host. ' using prasso.io');
                    $site = Site::getClient( 'prasso.io');
                }
                //get the team from the site
                if ($site->supports_registration) {
                    $teamsite = TeamSite::where('site_id', $site->id)->first();
                    if ($teamsite == null)
                    {
                        Log::error('TeamSite not found for site: ' . $site->id);
                        $teamsite = TeamSite::where('site_id', 1)->first();
                    }
                    $team = Team::where('id', $teamsite->team_id)->first();
                    $team->users()->attach(
                        $user,
                        ['role' => 'user']
                    );
                    $user->current_team_id = $team->id;
                    $user->save();
                    TeamMemberAdded::dispatch($team, $user);
                }
                else{
                    $this->createTeam($user);
                }
                
                $user->sendWelcomeEmail();
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
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0] . "'s Team",
            'personal_team' => true,
            'phone' => $user->phone,
        ]));
    }
}
