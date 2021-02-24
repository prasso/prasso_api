<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AppsLayout extends Component
{
    public $selected_team;
    public $apps;
    public $selected_app;

    /**
     * Create the component instance.
     *
     * @param  string  $type
     * @param  string  $message
     * @return void
     */
    public function __construct($selectedapp, $selectedteam, $apps)
    {
        $this->selected_team = $selectedteam;
        $this->selected_app = $selectedapp;
        $this->apps = $apps;
    }
    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('components.apps-layout');
    }

}
