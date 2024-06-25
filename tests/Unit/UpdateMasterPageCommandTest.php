<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use App\Models\SitePages;
use App\Models\MasterPage;
use Illuminate\Support\Facades\Log;

class UpdateMasterPageCommandTest extends TestCase
{
    protected $sitePage, $masterPage;

   /* public function setUp(): void
    {
        parent::setUp();

        // Create fake data for testing
        $this->sitePage = SitePages::factory()->create([
            'description' => 'Test page content'
        ]);

        $this->masterPage = MasterPage::factory()->create([
            'pagename' => 'test.master'


        ]);
       
    }*/

    /** @test */
    public function it_updates_the_master_page_file()
    {
        // using existing test records created from previous run of above code
        $this->sitePage = SitePages::find(61);
        $this->masterPage = MasterPage::find(6);
    
        $this->assertTrue($this->sitePage != null && $this->masterPage!= null );

        $masterFilename = resource_path('views/sitepage/templates/' . str_replace('.', '', $this->masterPage->pagename) . '.blade.php');

        // Ensure the file does not exist before the command runs
        if (File::exists($masterFilename)) {
            File::delete($masterFilename);
        }

        // Run the artisan command
        Artisan::call('update:master-page', [
            'pageContentsId' => $this->sitePage->id,
            'masterId' => $this->masterPage->id,
        ]);

        // Assert the file was created and contains the correct content
        $this->assertTrue(File::exists($masterFilename));
        $this->assertStringEqualsFile($masterFilename, $this->sitePage->js.' '.$this->sitePage->css.' '.$this->sitePage->description);

        // Clean up
       File::delete($masterFilename);
    }

    /** @test */
    public function it_shows_help_when_arguments_are_missing()
    {
        $result = Artisan::call('update:master-page');
        $this->assertStringContainsString('php artisan update:master-page <pageContentsId> <masterId>', Artisan::output());
    }
}
