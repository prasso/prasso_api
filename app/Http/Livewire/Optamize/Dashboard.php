<?php

namespace App\Http\Livewire\Prasso;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Site;
use Illuminate\Http\Request;

class Dashboard extends Component
{
    public $title = "Dashboard";
    public $site;
    public $current_user;


    public function mount(Request $request) {
        //does this user have an admin role?
        $this->current_user = \Auth::user();
        $host = $request->getHost();

        $site = Site::getClient($host);
        $this->site = $site;
      }

    public function render()
    {
        return view('livewire.Prasso.dashboard');
    }
}
