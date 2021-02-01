<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AppsLayout extends Component
{
    public $apps;
    public $selected_app;

    /**
     * Create the component instance.
     *
     * @param  string  $type
     * @param  string  $message
     * @return void
     */
    public function __construct($selectedapp, $apps)
    {
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
