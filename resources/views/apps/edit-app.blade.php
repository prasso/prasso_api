<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('App') }}
        </h2>
    </x-slot>

    <div>

    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('apps.app-info-form',[ 'team' => $team, 'teamapp' => $teamapp])
            <x-jet-section-border />

            <div class="mt-10 sm:mt-0">
                @livewire('apps.app-manager',[ 'team' => $team,  'teamapp' => $teamapp])
            </div>

        </div>
    </div>
</x-app-layout>
