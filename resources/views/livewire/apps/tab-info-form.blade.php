<div>
<x-jet-form-section submit="updateTab">
    <x-slot name="title">
        {{ __('Tab Info') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Details of this App tab.') }}
    </x-slot>
  
    <x-slot name="form">

    @if($show_success)
    <div class="p-5 text-green-400 bg-green-50">
    Saved!
    {{$show_success = false}}
    </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


        <x-jet-input id="app_id"
                    type="hidden"
                    wire:model="tab_data.app_id" />

    <!-- Icon -->
    <div class="col-span-6 sm:col-span-4">
        <x-jet-label for="icon" value="{{ __('Icon') }}" />

        <x-jet-input id="icon"
                    type="text"
                    class="mt-1 block w-full"
                    wire:model="tab_data.icon" />

        <x-jet-input-error for="icon" class="mt-2" />
    </div>
    <!-- Tab Label -->
    <div class="col-span-6 sm:col-span-4">
        <x-jet-label for="tab_label" value="{{ __('Tab Label') }}" />

        <x-jet-input id="tab_label"
                    type="text"
                    class="mt-1 block w-full"
                    wire:model="tab_data.label" />

        <x-jet-input-error for="tab_label" class="mt-2" />
    </div>
    <!-- Page Title -->
    <div class="col-span-6 sm:col-span-4">
        <x-jet-label for="page_title" value="{{ __('Page Title') }}" />

        <x-jet-input id="page_title"
                    type="text"
                    class="mt-1 block w-full"
                    wire:model="tab_data.page_title" />

        <x-jet-input-error for="page_title" class="mt-2" />
    </div>
    <!-- Tab Label -->
    <div class="col-span-6 sm:col-span-4">
        <x-jet-label for="page_url" value="{{ __('Page Url') }}" />

        <x-jet-input id="page_url"
                    type="text"
                    class="mt-1 block w-full"
                    wire:model="tab_data.page_url" />

        <x-jet-input-error for="page_url" class="mt-2" />
    </div>
    <!--Sort Order -->
    <div class="col-span-6 sm:col-span-4">
        <x-jet-label for="sort_order" value="{{ __('Sort Order') }}" />
        <div class="flex items-center mt-2">
        <select name="sort_order" id="sort_order" class="mt-1 block w-full"  wire:model="tab_data.sort_order" wire:change="change">
            @foreach($sort_orders as $id) )
                <option value="{{ json_encode($id) }}">{{ json_encode($id) }}</option>
            @endforeach
        </select>
        </div>
    </div>

    <!-- More Tab? -->
    <div class="col-span-6 sm:col-span-4">
        <x-jet-label for="more_data" value="{{ __('Overflow/More') }}" />
        <div class="flex items-center mt-2">
        <select name="more_data" id="more_data" class="mt-1 block w-full"  wire:model="tab_data.parent" wire:change="change">
            @foreach($more_data as $id) )
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
