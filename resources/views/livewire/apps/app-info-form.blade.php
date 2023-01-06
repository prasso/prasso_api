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
                @if (isset($teamapps))
                <select name="teams" id="teams" class="mt-1 block w-full border-2 border-indigo-600/100 p-2" wire:model="team_id" wire:change="updateApp">
                    @foreach($team_selection as $id=>$name) 
                    <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                @else
                Team: {{ $teamapp['team_name'] }}
                @endif
            </div>
        </div>
        <div class="col-span-6">
            <x-jet-label value="{{ __('Select Site') }}" />
            <div class="flex items-center mt-2">
            @if (isset($sites))
            <select name="sites" id="sites" class="mt-1 block w-full border-2 border-indigo-600/100 p-2" wire:model="site_id" wire:change="updateApp">
                    @foreach($sites as $id=>$site_name) 
                    <option value="{{ $id }}">{{ $site_name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
        <div class="col-span-6">
            <x-jet-label value="{{ __('App Icon') }}" />
            <div class="flex items-center mt-2">
                @if (isset($photo) && !empty ($photo->temporaryUrl()) )
                <img class=" h-12 rounded-full object-cover" src="{{ $photo->temporaryUrl() }}" alt="{{ $teamapp['app_name'] }}">
                @else
                @if( !empty($teamapp['appicon']))
                <img class=" h-12 rounded-full object-cover" src="{{ $teamapp['appicon'] }}" alt="{{ $teamapp['app_name'] }}">
                @else
                    No appicon Given                     
                @endif
                @endif
                {{ $teamapp['appicon'] }}
        
                    <input type="file" wire:model="photo"  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
 
                     @error('photo') <span class="error">{{ $message }}</span> @enderror
 
            </div>
        </div>

        <div class="col-span-6">
    @include('apps.app-inputs')
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