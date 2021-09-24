<?php

namespace App\Http\Livewire\Apps;

use Livewire\Component;
use app\Models\Apps;
use Illuminate\Support\Facades\Log;

class AppInfoForm extends Component
{
    public $teamapp;
    public $teamapps;
    public $team;
    public $team_selection;
    public $team_id;
    public $site_id;
    public $sites;

    public $show_success;

    public function render()
    {
        return view('livewire.apps.app-info-form');
    }

    protected $rules = [
        'teamapp.app_name' => 'required|min:6',
        'teamapp.page_title' => 'required|min:6',
        'teamapp.page_url' => 'required|min:6',
        'teamapp.site_id' => 'required|min:1',
        'teamapp.sort_order' => 'required'
    ];

    public function updateApp()
    {
        $this->validate();
        // Execution doesn't reach here if validation fails.
        Apps::processUpdates($this->teamapp->toArray()  );
        $this->show_success = true;
    }
}
