<?php

namespace App\Livewire\Apps;

use Livewire\Component;
use App\Models\Tabs;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;


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
    
    public function mount($sortorders, $moredata, $icondata, Request $request)
    {

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

        session()->put('url', $request->url());
    }


    protected $rules = [
        'tabdata.id' => 'required',
        'tabdata.app_id' => 'required',
        'tabdata.icon' => 'required',
        'tabdata.label' => 'required',
        'tabdata.page_url' => 'required', //
        'tabdata.page_title' => 'required', //
        'tabdata.request_header' => 'required', 
        'tabdata.sort_order' => 'required',
        'tabdata.parent' => 'required'
    ];
   
    public function updateTab()
    {
      //  $this->validate();

        // Execution doesn't reach here if validation fails.
        $this->tabdata = Tabs::processUpdates($this->tabdata );      
        $this->showsuccess = true;

       return $this->redirectToThisApp();
    }

    protected function redirectToThisApp()
    {
         // ./team/1/apps/2/tabs/new
        // ./team/1/apps/2

        $url = session()->get('url');
        //$url = str_replace('tabs/new','tabs/'.$this->tabdata['id'],$url);
        $url = str_replace('tabs/new','',$url);
        return redirect()->to($url);
    }
}
