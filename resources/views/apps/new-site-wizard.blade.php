<x-app-layout>
    
<div>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        @livewire('new-site-and-app',[ 'user' => $user, 'team' => $team, 'team_selection'=>$team_selection ])
    </div>
</div>
</x-app-layout>