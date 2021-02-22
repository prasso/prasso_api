<?php

namespace App\Http\Livewire\Apps;

use Livewire\Component;
use App\Models\Tabs;
use Illuminate\Support\Facades\Log;

class TabInfoForm extends Component
{
  
    protected $sortorders;
    public $tabdata;
    protected $moredata;
    protected $icondata;

    protected $showsuccess;

    public function render()
    {
        return view('livewire.apps.tab-info-form')
        ->with('showsuccess', $this->showsuccess)
        ->with('tabdata', $this->tabdata)
        ->with('sortorders', $this->sortorders)
        ->with('moredata', $this->moredata)
        ->with('icondata', $this->icondata);
    }

      /*  'tabdata'=>$tabdata, 
                         'sortorders' => $sortorders 
                        , 'moredata' => $moredata ,'icondata' => $icondata*/
    public function mount(Tabs $newtabdata, $sortorders, $moredata, $icondata)
    {
        $this->tabdata = ($this->tabdata != null) ? $newtabdata : Tabs::make();
        if ($sortorders == null)
        {
            $sortorders = session()->get('sortorders');
        }
        else
        {
            $this->sortorders = $sortorders;
            session()->put('sortorders', $sortorders);
        }
        if ($moredata == null)
        {
            $moredata = session()->get('moredata');
        }
        else
        {
            $this->moredata = $moredata;
            session()->put('moredata', $moredata);
        }
        if ($icondata == null)
        {
            $icondata = session()->get('icondata');
        }
        else
        {
            $this->icondata = $icondata;
            session()->put('icondata', $icondata);
        }

    }


    protected $rules = [
        'tabdata.app_id' => 'required',
        'tabdata.icon' => 'required',
        'tabdata.label' => 'required|min:6',
        'tabdata.page_url' => 'required|min:6', //
        'tabdata.page_title' => 'required|min:6', //
        'tabdata.sort_order' => 'required',
        'tabdata.parent' => 'required'
    ];
    /*
    [{
	"type": "syncInput",
	"payload": {
		"name": "tabdata.page_url",
		"value": "https://barimorphosis.com"
	}
}, {
	"type": "syncInput",
	"payload": {
		"name": "tabdata.page_title",
		"value": "First Tab"
	}
}, {
	"type": "syncInput",
	"payload": {
		"name": "tabdata.label",
		"value": "First Tab"
	}
}, {
	"type": "callMethod",
	"payload": {
		"method": "updateTab",
		"params": []
	}
}] */

    public function updateTab()
    {
Log::info('validating this data: '.json_encode($this->tabdata));
        $this->validate();
Log::info('saving a tab'.json_encode($this->tabdata));
        // Execution doesn't reach here if validation fails.
        $this->tabdata = Tabs::processUpdates($this->tabdata->toArray() );
 Log::info('tab was saved'.json_encode($this->tabdata));        
        $this->showsuccess = true;
        return $this->tabdata;
    }
}
