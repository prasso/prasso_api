<!-- if this is show_modal, then show the modal -->
@if(!isset($show_modal) || $show_modal)
<div class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400" x-data>
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" 
             role="dialog" 
             aria-modal="true" 
             aria-labelledby="modal-headline"
             @click.away="$wire.closeModal()">
@else
<div>
    <div>
        <div class="align-bottom bg-white rounded-lg text-left transition-all sm:my-8 sm:align-middle">
@endif
            <div class="border-t border-gray-200"></div>
            <div class="block px-4 py-2 text-lg font-semibold text-gray-600">
                @if(isset($site_id) && $site_id)
                <!-- Collapsible section for image library and site actions -->
                <div x-data="{ isToolsOpen: false }">
                    <button @click="isToolsOpen = !isToolsOpen" type="button" class="flex justify-between items-center w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md mb-2 transition-all duration-200">
                        <span class="font-medium text-gray-700">Site Tools & Resources</span>
                        <svg x-show="!isToolsOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        <svg x-show="isToolsOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    
                    <div x-show="isToolsOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
                        <!-- Image Library Section -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-2">{{ __('Add to Image Library') }}</h3>
                            @include('partials._image-upload-styles')
                            <div class="py-4">
                                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                                    @include('partials._image-upload', ['site_id' => $site_id])
                                </div>
                            </div>
                        </div>
                        
                        <!-- Site Action Buttons -->
                        <div class="block px-4 py-2">
                            <h3 class="text-lg font-medium text-gray-700 mb-2">{{ __('Site Management Tools') }}</h3>
                            <div class="grid grid-cols-3 gap-4">
                        <!-- Site Map -->
                        <div class="text-center">
                            <a href="{{ route('sites.site-map.edit', ['site' => $site]) }}" 
                               class="flex flex-col items-center text-gray-600 hover:text-gray-900 p-4 rounded-lg hover:bg-gray-50"
                               title="Edit Site Map">
                                <svg class="w-8 h-8 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                                <span class="text-sm font-semibold">{{ __('Edit Site Map') }}</span>
                            </a>
                        </div>

                        <!-- Mobile App -->
                        <div class="text-center">
                            <x-responsive-nav-link href="{{ route('apps.show', Auth::user()->current_team_id) }}" 
                                                 class="flex flex-col items-center text-gray-600 hover:text-gray-900 p-4 rounded-lg hover:bg-gray-50">
                                <svg class="w-8 h-8 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 2.75a2 2 0 00-2 2v14.5a2 2 0 002 2h10a2 2 0 002-2V4.75a2 2 0 00-2-2H7zM7 2.75h10M11 18h2M12 10.25h.01" />
                                </svg>
                                <span class="text-sm font-semibold">{{ __('Edit Mobile App') }}</span>
                            </x-responsive-nav-link>
                        </div>

                        <!-- ERP Products -->
                        <div class="text-center">
                            <x-responsive-nav-link href="{{ url('/admin/erp-products') }}"
                                                 class="flex flex-col items-center text-gray-600 hover:text-gray-900 p-4 rounded-lg hover:bg-gray-50">
                                <svg class="w-8 h-8 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3.5a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2v-14zM10 8h4M10 12h4" />
                                </svg>
                                <span class="text-sm font-semibold">{{ __('Edit Erp Products') }}</span>
                            </x-responsive-nav-link>
                        </div>
                    </div>
                </div>
                    </div>
                </div>
                @endif

                <form>
                    <input type="hidden" wire:model="site_id" />
                    @include('sites.site-inputs', ['team_selection' => $team_selection])
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                            <button wire:click.prevent="store()" type="button" class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                Save
                            </button>
                        </span>
                        <span class="mt-3 flex w-full rounded-md shadow-sm sm:mt-0 sm:w-auto">
                            <button wire:click="closeModal()" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                                Cancel
                            </button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
        <script src="{{ asset('js/image-upload.js') }}"></script>
    </div>
</div>
@if(!isset($show_modal) || $show_modal)
    </div>
</div>
@endif
