<x-app-layout>

<x-slot name="title">View and Edit Sites</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sites') }}
        </h2>
       
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('site-editor');
        </div>
    </div>




</x-app-layout>
  