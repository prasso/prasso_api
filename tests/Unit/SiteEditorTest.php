<?php
use App\Models\Apps;
use App\Models\Site;
use App\Models\SitePages;
use App\Livewire\SiteEditor;
use App\Services\AppSyncService;
use App\Models\Team;
use App\Models\User;
use Tests\TestCase;

class SiteEditorTest extends TestCase
{

 
    public function testEditSite()
    {
        // Create a user with the necessary permissions.
        $user = User::factory()->create();
        $user->assignRole('site-admin');
        $user->save();
        $user->refresh();
        $team = Team::factory()->create();
        $team->user_id = $user->id;
        
        $user->current_team_id = $team->id;
        
        // Create a site with an associated team.
        $site = Site::factory()->create();

        $site->teams()->attach($team);
        $site->save();
        $team->save();
        $user->save();
        $site->refresh();
        $team->refresh();
        $user->refresh();

        // Log in as the user.
        $this->actingAs($user);

        // Call the editSite method with the site ID.
        $response = $this->get(route('site.edit', $site->id));

        // Assert that the response is successful.
        $response->assertSuccessful();

        // Assert that the site and team variables are passed to the view.
        $response->assertViewHas('site', $site);
        $response->assertViewHas('team', $team);
        
    }

       /** @test  */
       public function it_can_sync_selected_site_pages_to_an_app_as_tabs()
       {
           $team = Team::factory()->create();
           
           // Create a site
           $site = Site::factory()->create();
   
           // Create some site pages
           $sitePages = SitePages::factory()->count(3)->create([
               'fk_site_id' => $site->id,
           ]);
   
           // Create an app
           $app = Apps::factory()->create([
               'site_id' => $site->id,
               'team_id' =>  $team->id,
           ]);
   
           // Select some site pages to sync to the app
           $selectedPages = $sitePages->take(2)->pluck('id')->toArray();
           // Call the syncAppToSite method
           $appSyncService = new AppSyncService();
           $siteEditor = new SiteEditor($appSyncService);
           $siteEditor->showTheSyncDialog($site->id);
           $siteEditor->selectedPages = $selectedPages;
           $siteEditor->syncAppToSite();
           
           // Assert that the selected site pages were added as tabs to the app
           $this->assertCount(2, $app->tabs);
           $this->assertEquals($sitePages->firstWhere('id', $selectedPages[0])->title, $app->tabs[0]->page_title);
           $this->assertEquals($sitePages->firstWhere('id', $selectedPages[1])->title, $app->tabs[1]->page_title);
       }
  
}
?>