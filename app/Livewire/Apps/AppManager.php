<?php

namespace App\Livewire\Apps;

use Livewire\Component;

class AppManager extends Component
{
    public $teamapp;
    public $team;

    public function render()
    {
        return view('livewire.apps.app-manager');
    }
}
