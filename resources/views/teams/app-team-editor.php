<x-app-layout>

    <x-slot name="title">Team</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Team Editor') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('teams.team-member-manager', ['team' => $team])
        </div>
    </div>
</x-app-layout>