<div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="">
                        <!-- App Name -->
  <div class="mb-4">
            <x-label for="app_name" value="{{ __('App Name') }}" />

            <x-input id="app_id" type="hidden" wire:model="teamapp.id" />
            <x-input id="app_name" type="text" class="mt-1 block w-full" wire:model="teamapp.app_name" />

            <x-input-error for="app_name" class="mt-2" />
        </div>

        <!-- Page Title -->
        <div class="mb-4">
            <x-label for="page_title" value="{{ __('Page Title') }}" />

            <x-input id="page_title" type="text" class="mt-1 block w-full" wire:model="teamapp.page_title" />

            <x-input-error for="page_title" class="mt-2" />
        </div>

        <!-- Page Url -->
        <div class="mb-4">
            <x-label for="page_url" value="{{ __('Page Url') }}" />

            <x-input id="page_url" type="text" class="mt-1 block w-full" wire:model="teamapp.page_url" />

            <x-input-error for="page_url" class="mt-2" />
        </div>

        <!-- Sort Order -->
        <div class="mb-4">
            <x-label for="sort_order" value="{{ __('Sort Order') }}" />

            <x-input id="sort_order" type="text" class="mt-1 block w-full" wire:model="teamapp.sort_order" />

            <x-input-error for="sort_order" class="mt-2" />
        </div>
