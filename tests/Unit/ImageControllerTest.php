<?php
use App\Models\TeamImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Team;


class ImageControllerTest extends TestCase
{
    
    public function testIndex()
    {
        // Create a user and team for testing         
        $user = User::factory()->create();
        $team = Team::find($user->current_team_id);

        // Create some team images for testing
        $images = TeamImage::where(['team_id' => $team->id])->get();

        // Mock the authenticated user
        $this->actingAs($user);

        // Call the index method
        $response = $this->get(route('image.library'));

        // Assert that the response is successful
        $response->assertSuccessful();

        // Assert that the images are passed to the view
        $response->assertViewHas('images', $images);
    }
}
?>