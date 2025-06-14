<div x-data="{ isOpen: true }">
    <button @click="isOpen = !isOpen" type="button" class="flex justify-between items-center w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md mb-2 transition-all duration-200">
        <span class="font-medium text-gray-700">Site Configuration</span>
        <svg x-show="!isOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
        <svg x-show="isOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
    </button>
    
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
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
                        @if(isset($site_id) && $site_id)
                        <div class="mb-4">
                            <label for="hostInput" class="block text-gray-700 text-sm font-bold mb-2">Host:</label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="hostInput" placeholder="Enter Host" wire:model="host">
                            @error('host') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="image_folderInput" class="block text-gray-700 text-sm font-bold mb-2">Image Foldername:</label>
                            <input type="text" id="image_folderInput" placeholder="Enter Image Folder Name"  wire:model="image_folder" >
                            @error('image_folder') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        @endif
                        <div class="mb-4 flex justify-between items-center">
                            <button type="button" wire:click="generateAIAssets" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-900 focus:outline-none focus:border-purple-900 focus:ring focus:ring-purple-300 disabled:opacity-25 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                </svg>
                                AI Generate
                            </button>
                            <div wire:loading wire:target="generateAIAssets" class="text-sm text-gray-500">
                                Generating assets...
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="main_colorInput" class="block text-gray-700 text-sm font-bold mb-2">Main Color:</label>
                            <input type="color"  id="main_colorInput" placeholder="Enter Main Color"  wire:model="main_color" >
                            @error('main_color') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        
                        <div class="mb-4">
                            <x-label value="{{ __('Logo Image') }}" />
                            <div class=" items-center mt-2"   style="max-width:400px;">
                                @if (isset($photo) && !empty ($photo->temporaryUrl()) )
                                <img class=" h-12 rounded-full object-cover" src="{{ $photo->temporaryUrl() }}" alt="{{ $site_name }}">
                                @else
                                    @if( !empty($logo_image))
                                    <img class=" h-12 rounded-full object-cover" src="{{ $logo_image ?? '' }}" alt="{{ $site_name }}">
                                    @else
                                        No logo supplied                    
                                    @endif
                                @endif
                                <div class="text-xs">@if(!empty($logo_image))<a href="{{ $logo_image }}" target="_blank" class="text-blue-500 hover:underline">{{ $logo_image }}</a>@else{{ $logo_image ?? '' }}@endif</div>
                                <div>
                                    <input type="file" wire:model="photo"  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                
                                    @error('photo') <span class="text-red-500">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-4 ">
                            <label for="faviconInput" class="block text-gray-700 text-sm font-bold mb-2">favicon: </label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="faviconInput" placeholder="Enter favicon" wire:model="favicon">
                            @error('favicon') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <x-label value="{{ __('Team') }}" />
                            <div class="flex items-center mt-2">
                                @if (Auth::user()->isSuperAdmin())
                                <select name="teams" id="teams" class="mt-1 block w-full border-2 border-indigo-600/100 p-2" wire:model="team_id" >
                                    @foreach($team_selection as $id=>$name) 
                                    <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @else
                                    {{ $team->user_id . ": ". $team->name }}
                                @endif
                            </div>
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
                            <label for="github_repositoryInput" class="block text-gray-700 text-sm font-bold mb-2">GitHub Repository:<br><sm>(e.g. username/repository)</sm></label>
                            <div class="flex">
                                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="github_repositoryInput" placeholder="Enter GitHub repository" wire:model="github_repository">
                                <button type="button" class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150" 
                                    onclick="window.dispatchEvent(new CustomEvent('open-github-repo-modal', { detail: { siteId: @if(isset($site_id)) {{ $site_id }} @else null @endif } }))">
                                    <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                        <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                                    </svg>
                                    Create from folder
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">If specified, site pages will be sourced from this repository instead of being configured manually.</p>
                            @error('github_repository') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="supports_registrationInput" class="block text-gray-700 text-sm font-bold mb-2">New users can register: </label>
                            <input type="radio" name="supports_registrationInput" id="supports_registrationInput" wire:model.defer="supports_registration" value="1"  />Yes
                            <input type="radio" name="supports_registrationInput" id="supports_registrationInput" wire:model.defer="supports_registration" value="0" />No
                
                            @error('supports_registration') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="subteams_enabledInput" class="block text-gray-700 text-sm font-bold mb-2">Subteams Enabled: </label>
                            <input type="radio" name="subteams_enabledInput" id="subteams_enabledInput" wire:model.defer="subteams_enabled" value="1"  />Yes
                            <input type="radio" name="subteams_enabledInput" id="subteams_enabledInput" wire:model.defer="subteams_enabled" value="0" />No
                
                            @error('subteams_enabled') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="does_livestreamingInput" class="block text-gray-700 text-sm font-bold mb-2">Site hosts a live stream: </label>
                            <input type="radio" name="does_livestreamingInput" id="does_livestreamingInput" wire:model.defer="does_livestreaming" value="1"  />Yes
                            <input type="radio" name="does_livestreamingInput" id="does_livestreamingInput" wire:model.defer="does_livestreaming" value="0" />No
                
                            @error('does_livestreaming') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>


                        <div class="mb-4">
                            <label for="invitation_onlyInput" class="block text-gray-700 text-sm font-bold mb-2">Site registration is invitation only: </label>
                            <input type="radio" name="invitation_onlyInput" id="invitation_onlyInput" wire:model.defer="invitation_only" value="1"  />Yes
                            <input type="radio" name="invitation_onlyInput" id="invitation_onlyInput" wire:model.defer="invitation_only" value="0" />No
                
                            @error('invitation_only') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>


                    </div>
                     <!-- New Stripe Key and Secret fields -->
                    <div class="block px-4 py-2 text-lg font-semibold text-gray-600">
                        <label for="stripe_key" class="block text-sm font-medium text-gray-700">Stripe Key</label>
                        <input type="text" id="stripe_key" wire:model="stripe_key" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Enter Stripe Key">
                        
                        <label for="stripe_secret" class="block mt-4 text-sm font-medium text-gray-700">Stripe Secret</label>
                        <input type="password" id="stripe_secret" wire:model="stripe_secret" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Enter Stripe Secret">
                    </div>
                </div>
            </div>
        </div>
    </div>