<x-jet-form-section submit="updateApp">
    <x-slot name="title">
        {{ __('App Name') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Details of this app.') }}
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


        <!-- App Owner Information -->
        <div class="col-span-6">
            <x-jet-label value="{{ __('Select Team') }}" />
            <div class="flex items-center mt-2">
                <select name="teams" id="teams" class="mt-1 block w-full" wire:model="team_id" wire:change="change">
                    @foreach($team_selection as $id=>$name) 
                    <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-span-6">
            <x-jet-label value="{{ __('Select Site') }}" />
            <div class="flex items-center mt-2">
                <select name="sites" id="sites" class="mt-1 block w-full" wire:model="site_id" wire:change="change">
                    @foreach($sites as $id=>$site_name) 
                    <option value="{{ $id }}">{{ $site_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-span-6">
            <x-jet-label value="{{ __('App Icon') }}" />
            <div class="flex items-center mt-2">
                <img class="w-12 h-12 rounded-full object-cover" src="{{ $teamapp['appicon'] }}" alt="{{ $teamapp['app_name'] }}">

            </div>
        </div>

        <!-- App Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="app_name" value="{{ __('App Name') }}" />

            <x-jet-input id="app_id" type="hidden" wire:model="teamapp.id" />
            <x-jet-input id="app_name" type="text" class="mt-1 block w-full" wire:model="teamapp.app_name" />

            <x-jet-input-error for="app_name" class="mt-2" />
        </div>

        <!-- Page Title -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="page_title" value="{{ __('Page Title') }}" />

            <x-jet-input id="page_title" type="text" class="mt-1 block w-full" wire:model="teamapp.page_title" />

            <x-jet-input-error for="page_title" class="mt-2" />
        </div>

        <!-- Page Url -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="page_url" value="{{ __('Page Url') }}" />

            <x-jet-input id="page_url" type="text" class="mt-1 block w-full" wire:model="teamapp.page_url" />

            <x-jet-input-error for="page_url" class="mt-2" />
        </div>

        <!-- Sort Order -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="sort_order" value="{{ __('Sort Order') }}" />

            <x-jet-input id="sort_order" type="text" class="mt-1 block w-full" wire:model="teamapp.sort_order" />

            <x-jet-input-error for="sort_order" class="mt-2" />
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