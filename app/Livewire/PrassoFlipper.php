<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SitePageData;

class PrassoFlipper extends Component
{
    public $slides=[];

    public function mount($pageid=null)
    {
        if ($pageid){
               // Fetch the json_data as a string
            $jsonDataString = SitePageData::where('fk_site_page_id', $pageid)->first()->json_data;

            // Decode the JSON string to a PHP array
            $this->slides = json_decode($jsonDataString, true);  // 'true' makes it an associative array
            
            // Now $slides is an array and you can work with it
        }
    }

    public function render()
    {
        return view('livewire.prasso-flipper');
    }
}
