<?php
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\SitePageController;
use App\Models\SitePages;
use App\Models\User;
use Illuminate\Http\Request;

class VisualEditorTest extends TestCase
{
    use WithoutMiddleware;

    public function testGrapesUpdatedView()
    {
         // Create a user and authenticate
         $user = User::find(1);
         $this->actingAs($user);
 
        $response = $this->get('/visual-editor/1');

        $response->assertStatus(200);
        $response->assertViewIs('sitepage.grapes-updated');
        $response->assertSee('Visual Editor');
        $response->assertSee('Prasso uses Grapesjs');
        $response->assertSee('Grapesjs');
    }

    
}