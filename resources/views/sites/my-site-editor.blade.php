<x-app-layout :site="$site ?? null">
    <div id='mysiteeditor'>
        <div style="margin-bottom: -90px">
            @livewire('site.create-or-edit',['show_modal' => false, 'site' => $site,'user'=>$user,'team'=>$team, 'team_selection'=>$team_selection ])
        </div>
        <div class="max-w-7xl ml-10 mx-auto sm:px-6 lg:px-8">
            <div x-data="{ isOpen: true }" class="mb-4 border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                <button @click="isOpen = !isOpen" type="button" class="flex justify-between items-center w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-gray-50 hover:from-blue-100 hover:to-gray-100 rounded-t-lg transition-all duration-200 border-b border-gray-200">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="font-semibold text-gray-800 ml-2 text-lg">Site Pages</span>
                    </div>
                    <svg x-show="!isOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    <svg x-show="isOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div x-show="isOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
                    @livewire('site-page-editor',['siteid'=>$site->id])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>