<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AppsLayout extends Component
{

    public $apps;
    public $selected_app;
    public $activeAppId;

    /**
     * Create the component instance.
     *
     * @param  string  $type
     * @param  string  $message
     * @return void
     */
    public function __construct($activeAppId, $selectedapp,  $apps)
    {
        
        $this->selected_app = $selectedapp;
        $this->apps = $apps;
        $this->activeAppId = $activeAppId;
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
