<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\Events\TeamMemberAdded;
use Illuminate\Support\Facades\Log;

class TeamUser extends Model
{
    use HasFactory;
    
    protected $table = 'team_user';
    public $timestamps = true;


    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public static function addToBaseTeam($user)
    {
        //put this person on the main team for us to welcome until we know who they will settle in with
        if ($team = Team::where('user_id',1)->get()) {
            TeamUser::forceCreate([
                'user_id' => $user->id,
                'team_id' => 1,
                'role' => config('constants.TEAM_USER_ROLE')
            ]);
           
            $user->current_team_id = 1;
            $user->save();
            TeamMemberAdded::dispatch($team, $user);
        }
        else
        {
            Log::info('base team not found in create new user');
        }
    }

    public static function removeTeamMembership($user, $team_id)
    {
        info('remove team membership for user: '.$user.' and team: '.$team_id);
        TeamUser::where('team_id',$team_id)->where('user_id',$user->id)->delete();
    }

    public static function addToTeam($user, $team_id)
    {
        //put this person on the specified team
        $team = Team::where('id',$team_id)->get() ;
        TeamUser::forceCreate([
            'user_id' => $user->id,
            'team_id' => $team_id,
            'role' => config('constants.TEAM_USER_ROLE')
        ]);
        
        $user->current_team_id = $team_id;
        $user->save();
        TeamMemberAdded::dispatch($team, $user);

    }
}
