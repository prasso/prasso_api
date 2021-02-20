<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Teams and Apps') }}
        </h2>
    </x-slot>

    <div class="items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0">
   
        <x-jet-section-border />
       
<section class="text-gray-700 body-font">
    <div class="container px-8 pt-20 pb-24 mx-auto lg:px-4">

        <div class="flex flex-col w-full mb-12 text-left lg:text-center">
            <h2 class="mb-1 text-xs font-semibold tracking-widest text-blue-600 uppercase title-font">
            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="rounded-full h-20 w-20 m-auto">
            {{ $user->name }}
        </h2>   

        <h2 class="mb-1 text-xs font-semibold tracking-widest text-blue-600 uppercase title-font">
        {{ __('My Team(s) and Info') }}
        </h2> 
        <div class="flex flex-col w-full p-8 mx-auto mt-10 border rounded-lg l md:w-1/2 md:ml-auto md:mt-0">
      
        <x-teams-layout :selectedteam="$teams[0]['id']" :teams="$teams" />
</div>
<div class="flex flex-col w-full p-8 mx-auto mt-10 border rounded-lg l md:w-1/2 md:ml-auto md:mt-0">
      
        <h2 class="mb-1 text-xs font-semibold tracking-widest text-blue-600 uppercase title-font">
        {{ __('Selected Team\'s Apps') }}
        </h2>  

        <x-apps-layout :selectedapp="$teamapps[0]['id']" :selectedteam="$teams[0]['id']" :apps="$teamapps"/>
</div>

        </div>    
    </div>    
</div>
</x-app-layout>
