@if( !isset($show_modal) || $show_modal)
    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
@else
    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
@endif
                    <div class="">
                    @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        {!! implode('', $errors->all('<div>:message</div>')) !!}
                    </div>
                    @endif
                    <div class="mb-4">
                            <label for="nameInput" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="nameInput" placeholder="Enter Name" wire:model="site_name">
                            @error('site_name') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="descriptionInput" class="block text-gray-700 text-sm font-bold mb-2">Description: </label>
                            <textarea rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="descriptionInput" wire:model.defer="description" placeholder="Enter Description"></textarea>
                            @error('description') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="hostInput" class="block text-gray-700 text-sm font-bold mb-2">Host:</label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="hostInput" placeholder="Enter Host" wire:model="host">
                            @error('host') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="main_colorInput" class="block text-gray-700 text-sm font-bold mb-2">Main Color:</label>
                            <input type="color"  id="main_colorInput" placeholder="Enter Main Color"  wire:model="main_color" >
                            @error('main_color') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <x-jet-label value="{{ __('Logo Image') }}" />
                            <div class="flex items-center mt-2">
                                @if (isset($photo) && !empty ($photo->temporaryUrl()) )
                                <img class=" h-12 rounded-full object-cover" src="{{ $photo->temporaryUrl() }}" alt="{{ $site_name }}">
                                @else
                                    @if( !empty($logo_image))
                                    <img class=" h-12 rounded-full object-cover" src="{{ $logo_image }}" alt="{{ $site_name }}">
                                    @else
                                        No logo supplied                    
                                    @endif
                                @endif
                                <div class="text-xs">{{ $logo_image }}</div>
                                <div>
                                    <input type="file" wire:model="photo"  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                
                                    @error('photo') <span class="text-red-500">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="faviconInput" class="block text-gray-700 text-sm font-bold mb-2">favicon: </label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="faviconInput" placeholder="Enter favicon" wire:model="favicon">
                            @error('favicon') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="app_specific_jsInput" class="block text-gray-700 text-sm font-bold mb-2">Custom Script:<br><sm>(file location or code)</sm> </label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="app_specific_jsInput" placeholder="Enter app_specific_js" wire:model="app_specific_js">
                            @error('app_specific_js') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="app_specific_cssInput" class="block text-gray-700 text-sm font-bold mb-2">Custom CSS:<br><sm>(file location or code)</sm></label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="app_specific_cssInput" placeholder="Enter app_specific_css" wire:model="app_specific_css">
                            @error('app_specific_css') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="supports_registrationInput" class="block text-gray-700 text-sm font-bold mb-2">New users can register: </label>
                            <input type="radio" name="supports_registrationInput" id="supports_registrationInput" wire:model.defer="supports_registration" value="1"  />Yes
                            <input type="radio" name="supports_registrationInput" id="supports_registrationInput" wire:model.defer="supports_registration" value="0" />No
                
                            @error('supports_registration') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="does_livestreamingInput" class="block text-gray-700 text-sm font-bold mb-2">Site hosts a live stream: </label>
                            <input type="radio" name="does_livestreamingInput" id="does_livestreamingInput" wire:model.defer="does_livestreaming" value="1"  />Yes
                            <input type="radio" name="does_livestreamingInput" id="does_livestreamingInput" wire:model.defer="does_livestreaming" value="0" />No
                
                            @error('does_livestreaming') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>