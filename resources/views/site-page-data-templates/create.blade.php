
<x-app-layout>

<x-slot name="title">New Site Page Data Templates</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Site Page Data Templates') }}
        </h2>
        <div class="flex bg-gray-100">
        <x-responsive-nav-link href="{{ route('site-page-data-templates.index') }}" :active="request()->routeIs('site-page-data-templates.index')">
            {{ __('Cancel and go back') }}
        </x-responsive-nav-link>
        </div>
    </x-slot>
    <div>
        
    @livewire('site-page-data-template-form-inputs',['template' => $template])


</div>
    </div>




</x-app-layout>
  