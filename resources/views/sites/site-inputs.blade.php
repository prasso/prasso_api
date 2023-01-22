<div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="">
                    <div class="mb-4">
                            <label for="hostInput" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="nameInput" placeholder="Enter Name" wire:model="site_name">
                            @error('section') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="descriptionInput" class="block text-gray-700 text-sm font-bold mb-2">Description: </label>
                            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="descriptionInput" wire:model="description" placeholder="Enter Description"></textarea>
                            @error('description') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="hostInput" class="block text-gray-700 text-sm font-bold mb-2">Host:</label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="hostInput" placeholder="Enter Host" wire:model="host">
                            @error('section') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="main_colorInput" class="block text-gray-700 text-sm font-bold mb-2">Main Color:</label>
                            <input type="color"  id="main_colorInput" placeholder="Enter Main Color"  wire:model="main_color" >
                            @error('main_color') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="logo_imageInput" class="block text-gray-700 text-sm font-bold mb-2">logo_image: (enter html if this is a page of the site)</label>
                            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="logo_imageInput" wire:model="logo_image" placeholder="Enter Logo Url"></textarea>
                            @error('logo_image') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="databaseInput" class="block text-gray-700 text-sm font-bold mb-2">database: </label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="databaseInput" placeholder="Enter database" wire:model="database">
                            @error('database') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="faviconInput" class="block text-gray-700 text-sm font-bold mb-2">favicon: </label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="faviconInput" placeholder="Enter favicon" wire:model="favicon">
                            @error('favicon') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="supports_registrationInput" class="block text-gray-700 text-sm font-bold mb-2">New users can register: </label>
                            <input type="radio" name="supports_registrationInput" id="supports_registrationInput" wire:model.defer="supports_registration" value="1"  />Yes
                            <input type="radio" name="supports_registrationInput" id="supports_registrationInput" wire:model.defer="supports_registration" value="0" />No
                
                            @error('supports_registration') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        
                    </div>
                </div>