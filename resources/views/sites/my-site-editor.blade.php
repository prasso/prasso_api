<x-app-layout :site="$site ?? null"> 
    <div>
        @livewire('site.create-or-edit',['show_modal' => 0, 'site' => $site,'user'=>$user,'team'=>$team, 'team_selection'=>$team_selection ])
    </div>
    <div>
    @livewire('site-page-editor',['siteid'=>$site->id]);
    </div>
</x-app-layout>