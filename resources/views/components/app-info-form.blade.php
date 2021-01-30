<x-jet-form-section submit="updateAppName">
    <x-slot name="title">
        {{ __('App Name') }}
    </x-slot>

    <x-slot name="description">
        {{ __('The app\'s name and logo information.') }}
    </x-slot>

    <x-slot name="form">
       
 
        <!-- App Owner Information -->
        <div class="col-span-6">
            <x-jet-label value="{{ __('Teams') }}" />
            <div class="flex items-center mt-2">
            <select name="teams" id="teams" class="mt-1 block w-full"  wire:model="hall_id" wire:change="change">
            @foreach($teams as $item)
                <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
            @endforeach
            </select>
            </div>
        </div>
        <div class="col-span-6">
            <x-jet-label value="{{ __('App Name') }}" />
            <div class="flex items-center mt-2">
                <img class="w-12 h-12 rounded-full object-cover" src="{{ $teamapp->appicon }}" alt="{{ $teamapp->app_name }}">

                <div class="ml-4 leading-tight">
                    <div>{{ $teamapp->app_name }}</div>
                    <div class="text-gray-700 text-sm">{{ $teamapp->page_title }}</div>
                </div>
            </div>
        </div>

        <!-- App Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="name" value="{{ __('App Name') }}" />

            <x-jet-input id="name"
                        type="text"
                        class="mt-1 block w-full"
                        wire:model.defer="state.name"
                        :disabled="! Gate::check('update', $team)" />

            <x-jet-input-error for="name" class="mt-2" />
        </div>

    </x-slot>

    @if (Gate::check('update', $team))
        <x-slot name="actions">
            <x-jet-action-message class="mr-3" on="saved">
                {{ __('Saved.') }}
            </x-jet-action-message>

            <x-jet-button>
                {{ __('Save') }}
            </x-jet-button>
        </x-slot>
    @endif
</x-jet-form-section>
