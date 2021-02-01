<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AppTabsLayout extends Component
{
    public $apptabs;

    /**
     * Create the component instance.
     *
     * @param  string  $type
     * @param  string  $message
     * @return void
     */
    public function __construct($apptabs)
    {
        $this->apptabs = $apptabs;
    }
    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('components.apptabs-layout');
    }
}
