<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Dashboard extends Component
{
    public $user_content;

    public function __construct($userContent)
    {
        $this->user_content = $userContent;
    }

    public function render()
    {
        return view('components.dashboard');
    }
}