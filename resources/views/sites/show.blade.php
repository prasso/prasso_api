<x-app-layout :site="$site ?? null">

<x-slot name="title">View and Edit Sites</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sites') }}
        </h2>
        <div class="flex bg-gray-100">
        <x-responsive-nav-link href="{{ route('site-page-data-templates.index') }}" :active="request()->routeIs('site-page-data-templates.index')">
            {{ __('View / Edit Data Templates for Site Pages') }}
        </x-responsive-nav-link>
        </div>
    </x-slot>
    <div>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            
            @livewire('site-editor',['team_selection' => $team_selection]);
        </div>
    </div>




</x-app-layout>
  