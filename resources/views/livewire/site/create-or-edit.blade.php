<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Manage Site Pages for {{$site_name}}
    </h2>
    <x-responsive-nav-link href="{{ route('sites.show') }}" :active="request()->routeIs('sites.show')">
            {{ __('Back to Sites List') }}
        </x-responsive-nav-link>
</x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div >
        @include('sites.create-or-edit', ['team_selection' => $team_selection])
        </div>
    </div>
</div>
            
