<?php

namespace App\Http\Livewire\Apps;

use Livewire\Component;
use App\Models\Tabs;

class TabInfoForm extends Component
{
    public $team;
    public $sort_orders;
    public $teamapps;
    public $teamapp;
    public $tab_data;
    public $more_data;

    public $show_success;

    public function render()
    {
        return view('livewire.apps.tab-info-form');
    }

    protected $rules = [
        'tab_data.app_id' => 'required',
        'tab_data.icon' => 'required',
        'tab_data.label' => 'required|min:6',
        'tab_data.page_url' => 'required|min:6',
        'tab_data.page_title' => 'required|min:6',
        'tab_data.sort_order' => 'required',
        'tab_data.parent' => 'required'
    ];

    public function updateTab()
    {
        $this->validate();

        // Execution doesn't reach here if validation fails.
        Tabs::processUpdates($this->tab_data->toArray()  );
        $this->show_success = true;
    }
}
