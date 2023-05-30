<div>
<x-slot name="header">

    <x-jet-dropdown-link href="{{ route('dashboard')  }}">
                {{ __('Return to Dashboard') }}
            </x-jet-responsive-nav-link>
</x-slot>

<x-jet-form-section submit="createSiteAndApp" id="wizard-form">
<x-slot name="title">
        {{ __('New Site and App') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Easily create your site and the app that is associated with it') }}
    </x-slot>
    
<x-slot name="form" >

  <div class="col-span-6 sm:col-span-6 prose">
  <div wire:loading>
      @if($step4 )
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
          Creating your site...
      </div>
      @endif
    </div>

@if ($step1)
  <!-- Step 1: Business Information -->
  <div class="step inset-0  ease-out duration-400" id="step-1">
    <h2>Your Business Info</h2>
    <label for="business-name">Business Name:</label>
    <input class='form-input rounded-md shadow-sm mt-1 block w-full' wire:model.defer='site_name' type="text" id="business-name" name="business-name">
    <br>
    <label for="business-type">Business Type:</label><br/>
    <select id="business-type"  wire:model.defer='business_type' name="business-type" class='border-2 border-indigo-600/100 p-2'>
      <option value="">Select a Business Type</option>
      <option value="retail">Retail</option>
      <option value="service">Service</option>
      <option value="other">Other</option>
    </select>
    <br> <br>
    <label for="business-description">In one or two statements, tell us about your business</label>
    <textarea  wire:model.defer='description' class='form-input rounded-md shadow-sm mt-1 block w-full' id="business-description" name="business-description"></textarea>
    <br>
    <button type="button" wire:click="wizardProgress('NEXT')"  class="next-button cursor-pointer ml-6 border p-2 rounded-xl">Next</button>
  </div>
  @endif

  @if ($step2)
  <!-- Step 2: Domain Name -->
  <div class="step  inset-0  ease-out duration-400" id="step-2">
    <h2>What would you like to use for your web site address?</h2>
    <label for="domain-name">Domain Name: (example - one word no spaces - yourbusinessname) we will add prasso.io to this</label>
    <input  wire:model.defer='host' class='form-input rounded-md shadow-sm mt-1 block w-full' type="text" id="domain-name" name="domain-name">
    <br>
    <button type="button" wire:click="wizardProgress('PREV')" class="prev-button cursor-pointer ml-6 border p-2 rounded-xl">PREV</button>
    <button type="button" wire:click="wizardProgress('NEXT')"  class="next-button cursor-pointer ml-6 border p-2 rounded-xl">Next</button>
  </div>
  @endif

  @if ($step3)
  <!-- Step 3: Branding -->
  <div class="step  inset-0  ease-out duration-400" id="step-3">
    <h2>Your main primary color:</h2>
   
    <label for="primary-color">Primary Color:</label>
    <input wire:model.defer='main_color' class='rounded-md shadow-sm  block' type="color" id="primary-color" name="primary-color">
    <br>
    <h2>Your image folder name:</h2>
   
   <label for="primary-color">Primary Color:</label>
   <input wire:model.defer='image_folder' class='rounded-md shadow-sm  block' type="color" id="image-folder" name="image-folder">
   <br>

    <button type="button" wire:click="wizardProgress('PREV')"  class="prev-button cursor-pointer ml-6 border p-2 rounded-xl">PREV</button>
    <button type="button" wire:click="wizardProgress('NEXT')"  class="next-button cursor-pointer ml-6 border p-2 rounded-xl">Next</button>
  </div>
@endif


@if ($step4)
  <!-- Step 4: Review -->
  <div class="step  inset-0  ease-out duration-400" id="step-4">
    <h2>Review and confirm</h2>
    <label>Site Info</label>
    @include('sites.site-inputs')
   
    <br>

    <button type="button" wire:click="wizardProgress('PREV')" class="prev-button cursor-pointer ml-6 border p-2 rounded-xl">PREV</button>
  </div>
@endif

</div>

</x-slot>

<x-slot name="actions">
@if($step4 )
    <x-jet-action-message class="mr-3" on="saved">
        {{ __('Saved.') }}
    </x-jet-action-message>
    <x-jet-button wire:loading.remove wire:processing.attr="disabled" >
      <i wire:loading wire:target='submitForm' class="fas fa-spin fa-spinner mr-2"></i> 
        {{ __('Create Your Site and App') }}
    </x-jet-button>
    <div wire:loading>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
          Creating your site...
      </div>
    </div>
@endif
</x-slot>
</x-jet-form-section>
</div>