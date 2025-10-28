<?php

namespace App\Livewire\Apps;

use Livewire\Component;
use App\Models\Tabs;
use App\Models\Apps;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;


class TabInfoForm extends Component
{
  
    protected $sortorders;
    public $tabdata;
    protected $moredata;
    protected $icondata;

    protected $showsuccess;
    protected $newAppId = null;

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
        // If app_id is 0, create the app first so the tab can attach to it
        if (!isset($this->tabdata['app_id']) || (int)$this->tabdata['app_id'] === 0) {
            $url = session()->get('url');
            $teamId = null;
            if ($url && preg_match('#/team/(\d+)/apps/(\d+)#', $url, $m)) {
                $teamId = (int) $m[1];
            }
            $user = Auth::user();
            // Derive a site_id for this user
            $siteId = method_exists($user, 'getUserOwnerSiteId') ? $user->getUserOwnerSiteId() : null;
            // Create a minimal App record
            $app = Apps::create([
                'team_id' => $teamId,
                'site_id' => $siteId,
                'appicon' => null,
                'app_name' => $this->tabdata['label'] ?? 'New App',
                'page_title' => $this->tabdata['page_title'] ?? 'Home',
                'page_url' => $this->tabdata['page_url'] ?? '/',
                'sort_order' => $this->tabdata['sort_order'] ?? 0,
                'user_role' => null,
            ]);
            $this->newAppId = $app->id;
            $this->tabdata['app_id'] = $app->id;
            // Ensure tab sort order starts at 1 if not set
            if (!isset($this->tabdata['sort_order'])) {
                $this->tabdata['sort_order'] = 1;
            }
        }

        $this->tabdata = Tabs::processUpdates($this->tabdata );      
        $this->showsuccess = true;

       return $this->redirectToThisApp();
    }

    protected function redirectToThisApp()
    {
         // ./team/1/apps/2/tabs/new
        // ./team/1/apps/2

        $url = session()->get('url');
        $url = str_replace('tabs/new','',$url);
        // If we created a new app, swap the app id segment in the URL
        if ($this->newAppId !== null) {
            $url = preg_replace('#(/team/\d+/apps/)\d+#', '$1'.$this->newAppId, $url);
        }
        return redirect()->to($url);
    }
}
