<x-app-layout :site="$site ?? null">

<x-slot name="title">Dashboard</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <x-dashboard :user-content="$user_content"></x-dashboard>                          
</x-app-layout>
