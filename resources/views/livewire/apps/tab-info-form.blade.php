<div>
<x-jet-form-section submit="updateTab">
    <x-slot name="title">
        {{ __('Tab Info') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Details of this App tab.') }}
    </x-slot>
  
    <x-slot name="form">

    @if($showsuccess)
    <div class="p-5 text-green-400 bg-green-50">
    Saved!
    {{$showsuccess = false}}
    </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @if (is_array($errors))
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
                @else
                dd($errors)
                @endif
            </ul>
        </div>
    @endif


    <x-jet-input id="app_id"
                    type="hidden"
                    wire:model.defer="tabdata.app_id" />

    <x-jet-input id="id"
                type="hidden"
                wire:model.defer="tabdata.id" />

    <!-- Icon -->
    <div class="col-span-6 sm:col-span-4">
        <x-jet-label for="icon" value="{{ __('Icon') }}" />

        <div class="multiselect">
            <div class="selectBox" x-data="{ isShowing: false }" onclick="showRadios()">
                <select  class='border-2 border-indigo-600/100 p-2'>
                    <option>Select an icon</option>
                </select>
                <div class="overSelect"></div>
            </div>
            <div id="checkboxes">
                @foreach($icondata as $name)
                <label for="{{ $name }}">
                    <input x:model="selectedIcon" onclick="showRadios()" type="radio" value="{{ $name }}" wire:model.defer="tabdata.icon"  name="icon" /><i class="material-icons md-36">{{ $name }}</i> {{ $name }}</label>
                @endforeach
            </div>
        </div>
        <div style="float:right; margin-left:2px;color:brown;" id="selectedIcon"></div>
        
        <x-jet-input-error for="icon" class="mt-2" />

    </div>
    <!-- Tab Label -->
    <div class="col-span-6 sm:col-span-4">
        <x-jet-label for="tab_label" value="{{ __('Tab Label') }}" />

        <x-jet-input id="tab_label"
                    type="text"
                    class="mt-1 block w-full"
                    wire:model.defer="tabdata.label" />

        <x-jet-input-error for="tab_label" class="mt-2" />
    </div>
    <!-- Page Title -->
    <div class="col-span-6 sm:col-span-4">
        <x-jet-label for="page_title" value="{{ __('Page Title') }}" />

        <x-jet-input id="page_title"
                    type="text"
                    class="mt-1 block w-full"
                    wire:model.defer="tabdata.page_title" />

        <x-jet-input-error for="page_title" class="mt-2" />
    </div>
    <!-- Page Url -->
    <div class="col-span-6 sm:col-span-4">
        <x-jet-label for="page_url" value="{{ __('Page Url') }}" />

        <x-jet-input id="page_url"
                    type="text"
                    class="mt-1 block w-full"
                    wire:model.defer="tabdata.page_url" />

        <x-jet-input-error for="page_url" class="mt-2" />
    </div>
     <!-- Request Headers -->
     <div class="col-span-6 sm:col-span-4">
        <x-jet-label for="page_url" value="{{ __('Request Headers (advanced and optional)') }}" />

        <x-jet-input id="request_header"
                    type="text"
                    class="mt-1 block w-full"
                    wire:model.defer="tabdata.request_header" />

        <x-jet-input-error for="request_header" class="mt-2" />
    </div>
    <!--Sort Order -->
    <div class="col-span-6 sm:col-span-4">
        <x-jet-label for="sort_order" value="{{ __('Sort Order') }}" />
        <div class="flex items-center mt-2">
         <select name="sort_order" id="sort_order" class="mt-1 block w-fullborder-2 border-indigo-600/100 p-2"  wire:model.defer="tabdata.sort_order" >
            @foreach(Session::get('sortorders') as $id) )
                <option value="{{ json_encode($id) }}">{{ json_encode($id) }}</option>
            @endforeach
        </select>
        </div>
    </div>

    <!-- More Tab? -->
    <div class="col-span-6 sm:col-span-4">
        <x-jet-label for="more_data" value="{{ __('Overflow/More') }}" />
        <div class="flex items-center mt-2">

        <select name="more_data" id="more_data" class="mt-1 block w-full border-2 border-indigo-600/100 p-2"  wire:model.defer="tabdata.parent" >
            @foreach(Session::get('moredata') as $id) )
                <option value="{{ $id[0] }}">{{ $id[1] }}</option>
            @endforeach
        </select>
        </div>
    </div>
    
    </x-slot>

<x-slot name="actions">
    <x-jet-action-message class="mr-3" on="saved">
        {{ __('Saved.') }}
    </x-jet-action-message>

    <x-jet-button>
        {{ __('Save') }}
    </x-jet-button>
</x-slot>


</x-jet-form-section>             
</div>
