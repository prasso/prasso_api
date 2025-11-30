<div>
    <x-form-section submit="updateApp">
        <x-slot name="title">
            {{ __('App Information') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Update the app configuration including name and URLs.') }}
        </x-slot>

        <x-slot name="form">
            @if ($show_success)
                <div class="col-span-6 p-4 text-green-700 bg-green-100 rounded">
                    {{ __('App updated successfully.') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="col-span-6 alert alert-danger text-sm text-red-600 mt-2">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- App Name -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="app_name" value="{{ __('App Name') }}" />
                <x-input id="app_name" type="text" class="mt-1 block w-full" wire:model.defer="teamapp.app_name" />
                <x-input-error for="teamapp.app_name" class="mt-2" />
            </div>

            <!-- Page Title -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="page_title" value="{{ __('Page Title') }}" />
                <x-input id="page_title" type="text" class="mt-1 block w-full" wire:model.defer="teamapp.page_title" />
                <x-input-error for="teamapp.page_title" class="mt-2" />
            </div>

            <!-- Page URL -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="page_url" value="{{ __('Page URL') }}" />
                <x-input id="page_url" type="text" class="mt-1 block w-full" wire:model.defer="teamapp.page_url" />
                <x-input-error for="teamapp.page_url" class="mt-2" />
            </div>


            <!-- Site Selection -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="site_id" value="{{ __('Site') }}" />
                <select id="site_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" wire:model.defer="site_id">
                    <option value="">{{ __('Select a site') }}</option>
                    @foreach ($sites as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                <x-input-error for="teamapp.site_id" class="mt-2" />
            </div>

            <!-- Sort Order -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="sort_order" value="{{ __('Sort Order') }}" />
                <x-input id="sort_order" type="number" class="mt-1 block w-full" wire:model.defer="teamapp.sort_order" />
                <x-input-error for="teamapp.sort_order" class="mt-2" />
            </div>

            <!-- App Icon -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="appicon" value="{{ __('App Icon') }}" />
                @if ($teamapp->appicon)
                    <div class="mt-2 mb-4">
                        <img src="{{ $teamapp->appicon }}" alt="App Icon" class="h-16 w-16 rounded-lg">
                    </div>
                @endif
                <x-input id="photo" type="file" class="mt-1 block w-full" wire:model="photo" accept="image/*" />
                <x-input-error for="photo" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="actions">
            <x-action-message class="mr-3" on="saved">
                {{ __('Saved.') }}
            </x-action-message>

            <x-button class="bg-primary hover:bg-primary-700 text-white focus:ring-2 focus:ring-primary-500">
            {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-form-section>

    <!-- Sync Pages to App Section -->
    @if ($teamapp->id ?? false && $site_id)
    <x-section-border />
    <div class="mt-12">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <x-section-title>
                <x-slot name="title">{{ __('Sync Site Pages to App Tabs') }}</x-slot>
                <x-slot name="description">{{ __('Convert your site pages into mobile app navigation tabs.') }}</x-slot>
            </x-section-title>

            <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="px-4 py-5 bg-white dark:bg-gray-800 sm:p-6 shadow sm:rounded-md">
                    <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6">
                            <p class="text-sm text-gray-600 mb-4">
                                {{ __('Select which pages you want to sync as app tabs from your site.') }}
                            </p>
                            <a href="{{ route('apps.sync-pages-with-site', ['teamid' => $team_id, 'appid' => $teamapp->id, 'siteid' => $site_id]) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs bg-primary-600 hover:bg-primary-700 text-primary-foreground focus:ring-2 focus:ring-primary-500 uppercase tracking-widest transition ease-in-out duration-150"
                               >
                                {{ __('Sync Pages to Tabs') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
