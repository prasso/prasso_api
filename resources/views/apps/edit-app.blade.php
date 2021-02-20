<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('App: '.$teamapp['app_name']) }}
        </h2>
        <x-jet-dropdown-link href="{{ route('apps.show', Auth::user()->allTeams()->first()->id)  }}">
                    {{ __('Return to Apps') }}
                </x-jet-responsive-nav-link>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('apps.app-info-form',[ 'team_selection'=> $team_selection, 'team' => $team, 
                        'teamapp' => $teamapp,'teamapps' => $teamapps,
                        'team_id' => $team['id'] 
                        ]);
    
        </div>
    </div>

    <x-jet-section-border />

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        @livewire('apps.tab-list-form',['selected_app'=>$selected_app, 'apptabs'=>$apptabs, 'selectedteam'=>$team['id']])
    
        </div>
    </div>
</x-app-layout>
