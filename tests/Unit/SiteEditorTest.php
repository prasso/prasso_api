<?php
use App\Models\Apps;
use App\Models\Site;
use App\Models\SitePages;
use App\Http\Livewire\SiteEditor;
use App\Services\AppSyncService;
use App\Models\Team;
use Tests\TestCase;

class SiteEditorTest extends TestCase
{

    /** @test */
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