<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Manage Site Pages for {{$site_name}}
    </h2>
    <x-responsive-nav-link href="{{ auth()->user()->hasRole('super-admin') ? route('sites.show') : route('dashboard') }}" :active="request()->routeIs(auth()->user()->hasRole('super-admin') ? 'sites.show' : 'dashboard')">
            {{ auth()->user()->hasRole('super-admin') ? __('Back to Sites List') : __('Back to Dashboard') }}
        </x-responsive-nav-link>
</x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div >
        @include('sites.create-or-edit', [
            'team_selection' => $team_selection,
            'site_id' => $site_id,
            'show_modal' => $show_modal
        ])
        </div>
    </div>
</div>
