<x-app-layout> 
    <div>
        @livewire('site.create-or-edit',['show_modal' => 0, 'site' => $site,'user'=>$user,'team'=>$team ])
    </div>
    <div>
    @livewire('site-page-editor',['siteid'=>$site->id]);
    </div>
</x-app-layout>