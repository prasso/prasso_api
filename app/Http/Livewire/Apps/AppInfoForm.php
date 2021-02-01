<?php

namespace App\Http\Livewire\Apps;

use Livewire\Component;

class AppInfoForm extends Component
{
    public $teamapps;
    public $team;
    public $teams;

    public function render()
    {
        return view('livewire.apps.app-info-form');
    }
}
