<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Http\Requests\SitePageTemplateRequest;

class SitePageDataTemplateFormInputs extends Component
{

    public $template;

    public function mount($template)
    {
        $this->template = $template;
    }
   
    protected function rules()
    {
        $request = new SitePageTemplateRequest();

       return $request->rules();
     
    }
    public function render()
    {
        
        return view('livewire.site-page-data-template-form-inputs');
    }

    public function updated($propertyName)
    {
        $sitePageTemplateRequest = new SitePageTemplateRequest();
        $this->resetErrorBag($propertyName);
     
        $this->validateOnly($propertyName, $sitePageTemplateRequest->rules());
         

    }    
    public function submit()
    {
        
        $sitePageTemplateRequest = new SitePageTemplateRequest();
        $this->validate($sitePageTemplateRequest->rules());
        $this->template->save();

        session()->flash('success', 'Template saved successfully.');

        return redirect()->route('site-page-data-templates.index');
   
    }
}
