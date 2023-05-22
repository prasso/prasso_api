<?php

namespace App\Http\Livewire\Apps;

use Livewire\Component;
use app\Models\Apps;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;

class AppInfoForm extends Component
{

    use WithFileUploads;

    public $teamapp;
    public $teamapps;
    public $team;
    public $team_selection;
    public $team_id;
    public $site_id;
    public $sites;
    public $photo;

    public $show_success;

    public function render()
    {
        return view('livewire.apps.app-info-form');
    }

    protected $rules = [
        'teamapp.app_name' => 'required|min:6',
        'teamapp.page_title' => 'required|min:6',
        'teamapp.page_url' => 'required|min:6',
        'teamapp.appicon' => 'required_without:photo',
        'teamapp.site_id' => 'required|min:1',
        'teamapp.sort_order' => 'required',
        'photo' => 'required_without:teamapp.appicon|max:1024'
    ];

    public function updateApp()
    {
        $this->teamapp->team_id = $this->team_id;
        $this->teamapp->site_id = $this->site_id;
        $this->validate();

        // Execution doesn't reach here if validation fails.
        if (isset($this->photo))
        {
            // Store in the "photos" directory in a configured "s3" bucket.
            //prassouploads/prasso/-app-photos/logos-1/
            $this->photo->store(config('constants.APP_LOGO_PATH') .'logos-'.$this->teamapp->team_id, 's3');
            $this->teamapp->appicon = config('constants.CLOUDFRONT_ASSET_URL') . config('constants.APP_LOGO_PATH') .'logos-'.$this->teamapp->team_id.'/'. $this->photo->hashName();
        }
        Apps::processUpdates($this->teamapp->toArray()  );
        $this->show_success = true;
    }
}
