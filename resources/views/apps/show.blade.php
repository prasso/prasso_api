<x-app-layout>

<x-slot name="title">My Teams and Apps</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Teams and Apps') }}
        </h2>
    </x-slot>

    <div class="items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0">
   
        <x-section-border />
       
<section class="text-gray-700 body-font">
   
    <div class="container  pt-20 pb-24 mx-auto px-4">
        <div class="shadow overflow-hidden sm:rounded-md">
            <div class="p-6 bg-white col-span-6">
                <div class="flex flex-col w-full mb-12 text-left lg:text-center">
                    <h2 class="mb-5 text-xs font-semibold tracking-widest text-blue-600 uppercase title-font">
                    <img src="{{ $user->getProfilePhoto() }}" alt="{{ $user->name }}" class="rounded-full h-20 w-20 m-auto">
                    {{ $user->name }}
                </h2>   

                <h2 class="mb-5 text-xs font-semibold tracking-widest text-blue-600 uppercase title-font">
                {{ __('My Personal Team(s) and Info') }}
                </h2> 
                <div class="flex flex-col w-full p-5 mx-auto m-5 border rounded-lg l md:w-1/2 md:ml-auto md:mt-0">
            
                    <x-teams-layout :selectedteam="$user->currentTeam->id" :teams="$teams" />
                </div>
                <div class="flex flex-col w-full p-5 mx-auto m-5 border rounded-lg l ">
                <div class="p-6 bg-white col-span-6">
                <div class="text-sm text-gray-600" >
                    <span class="mt-5 float-right ">  <a href="{{ route('apps.edit',['teamid' => $teams[0]['id'], 'appid' => 0])   }}">
                            <i class="material-icons md-36">playlist_add</i>
                        </a>
                    </span>
                </div>  
                @if ($teamapps->isNotEmpty())
                        <h2 class="mb-5 text-xs font-semibold tracking-widest text-blue-600 uppercase title-font">
                        {{ __('Edit Application Configuration') }}
                        </h2>  
                       
                        <x-apps-layout :activeAppId="$activeappid" :selectedapp="$teamapps[0]['id']"  :apps="$teamapps"/>
                @else
                        <h2 class="mb-5 text-xs font-semibold tracking-widest text-blue-600 uppercase title-font">
                        {{ __('Add an App Configuration') }}
                        </h2>
                        <x-apps-layout :activeAppId="$activeappid" :selectedapp="0"  :apps="$teamapps"/>                       
                @endif
                </div>
            </div>    
        </div> 
      </div>
    </div> 
</section>  
</div>
</x-app-layout>
