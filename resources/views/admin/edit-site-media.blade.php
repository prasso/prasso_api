<x-app-layout>

<x-slot name="title">Queue</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Media') }}
        </h2>
        <a href="#" onclick="location.href = document.referrer; return false;">Go Back</a>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        @livewire('admin.site-media-component',['media'=>$media])
        </div>
    </div>
</x-app-layout>
