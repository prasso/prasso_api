<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tab') }}
        </h2>
        <x-jet-dropdown-link href="{{ route('apps.show', Auth::user()->allTeams()->first()->id)  }}">
                    {{ __('Return to Apps') }}
                </x-jet-responsive-nav-link>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('apps.tab-info-form',[ 'tab_data'=>$tab_data, 'team' => $team, 
                        'teamapp' => $teamapp,'teamapps' => $teamapps,
                        'team_id' => $team['id'] , 'sort_orders' => $sort_orders 
                        , 'more_data' => $more_data 
                        ]);
            <x-jet-section-border />

        </div>
    </div>
</x-app-layout>
