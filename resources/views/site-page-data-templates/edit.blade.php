
<x-app-layout :site="$site ?? null">

<x-slot name="title">Edit Site Page Data Template {{ $template->templatename }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Site Page Data Template '.$template->templatename) }}
        </h2>
        <div class="flex bg-gray-100">
        <x-responsive-nav-link href="{{ route('site-page-data-templates.index') }}" :active="request()->routeIs('site-page-data-templates.index')">
            {{ __('Cancel and go back') }}
        </x-responsive-nav-link>
        </div>
    </x-slot>
    <div>
        
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
       

    @livewire('site-page-data-template-form-inputs',['template' => $template])


</div>
    </div>




</x-app-layout>
  