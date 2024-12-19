<x-app-layout :site="$site ?? null">
<x-slot name="title">No Access</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('No Access') }}
        </h2>
    </x-slot>
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div>
                    No Access
                </div>
    </div>
    
</x-app-layout>