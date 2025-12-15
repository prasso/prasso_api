<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Team;
use App\Models\TeamUser;
use Tests\TestCase;

class SubteamTest extends TestCase
{   

    /** @test */
    public function user_can_belong_to_subteam()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        // Add user to team
        $user->current_team_id = $team->id;
        $user->save();

        // Reload the user model from the database
        $user->refresh();

        // Assert that the user belongs to the team
        $this->assertEquals($user->current_team_id, $team->id);
    }

    /** @test */
    public function team_can_have_multiple_users()
    {
        $team = Team::factory()->create();
        $users = User::factory()->count(3)->create();

        // Add users to team
        foreach ($users as $user) {
            TeamUser::forceCreate([
                'user_id' => $user->id,
                'team_id' => $team->id,
                'role' => config('constants.TEAM_USER_ROLE')
            ]);

        }
        $team->refresh();    
        $team->load('team_members');

        // Assert that the team has the correct number of users
        $this->assertEquals(3, $team->team_members->count());
    }
}