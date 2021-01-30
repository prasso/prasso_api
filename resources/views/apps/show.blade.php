<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Teams and Apps') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
           
        <x-jet-section-border />
       
        <x-jet-label value="{{ __('My Name and Profile Image') }}" />

        <x-jet-label value="{{ __('My Team(s) and Info') }}" />
        <x-team>
                    {{ $team->name }}
        </x-team>
        <x-jet-label value="{{ __('Selected Teams App') }}" />

        <x-jet-label value="{{ __('Tab(s) of Selected App') }}" />

    </div>
</x-app-layout>
