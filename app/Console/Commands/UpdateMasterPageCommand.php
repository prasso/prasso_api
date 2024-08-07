<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SitePages;
use App\Models\MasterPage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class UpdateMasterPageCommand extends Command
{
//example - update:master-page 73 6
//explanation - pageContentsId is id of the site_pages record
//              masterId is the id of the master_pages record
    protected $signature = 'update:master-page {pageContentsId?} {masterId?}';
    protected $description = 'Update the master page file from SitePage content.';


    public function handle()
    {
        $pageContentsId = $this->argument('pageContentsId');
        $masterId = $this->argument('masterId');
    
        if ($pageContentsId === null || $masterId === null) {
            $this->showHelp();
            return;
        }

        $pageContents = SitePages::findOrFail($pageContentsId);
        $masterPage = MasterPage::findOrFail($masterId);

        // Split the pagename on '.'
        $pagenameParts = explode('.', $masterPage->pagename);

        // Get the last item from the resulting array
        $lastPart = end($pagenameParts);

        // Construct the filename using the last part
        $masterFilename = resource_path('views/sitepage/templates/' . $lastPart . '.blade.php');

        if (!File::isDirectory(dirname($masterFilename))) {
            File::makeDirectory(dirname($masterFilename), 0755, true);
        }

        File::put($masterFilename, $pageContents->description);

        $this->info('Master page file updated successfully!');
    }
    /**
     * Show the command help.
     *
     * @return void
     */
    protected function showHelp()
    {
        $this->info('Usage:');
        $this->info('  php artisan update:master-page <pageContentsId> <masterId>');
        $this->info('');
        $this->info('Arguments:');
        $this->info('  <pageContentsId>   The ID of the SitePages model to get the content from');
        $this->info('  <masterId>         The ID of the MasterPage model to update');
    }
}
