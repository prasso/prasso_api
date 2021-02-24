<?php

namespace App\Http\Livewire\Apps;

use Livewire\Component;

class TabListForm extends Component
{
    public $apptabs;
    public $selectedteam;
    public $selected_app;
    
    protected $rules = [
        'apptabs' => 'required',
        'selectedteam' => 'required',
        'selected_app' => 'required'
    ];
    public function render()
    {
        return view('livewire.apps.tab-list-form');
    }
}
