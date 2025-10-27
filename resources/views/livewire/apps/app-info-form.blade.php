<div>
    <x-form-section submit="updateApp">
        <x-slot name="title">
            {{ __('App Information') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Update the app configuration including name, URLs, and PWA settings.') }}
        </x-slot>

        <x-slot name="form">
            @if ($show_success)
                <div class="col-span-6 p-4 text-green-700 bg-green-100 rounded">
                    {{ __('App updated successfully.') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="col-span-6 alert alert-danger text-sm text-red-600 mt-2">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- App Name -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="app_name" value="{{ __('App Name') }}" />
                <x-input id="app_name" type="text" class="mt-1 block w-full" wire:model.defer="teamapp.app_name" />
                <x-input-error for="teamapp.app_name" class="mt-2" />
            </div>

            <!-- Page Title -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="page_title" value="{{ __('Page Title') }}" />
                <x-input id="page_title" type="text" class="mt-1 block w-full" wire:model.defer="teamapp.page_title" />
                <x-input-error for="teamapp.page_title" class="mt-2" />
            </div>

            <!-- Page URL -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="page_url" value="{{ __('Page URL') }}" />
                <x-input id="page_url" type="text" class="mt-1 block w-full" wire:model.defer="teamapp.page_url" />
                <x-input-error for="teamapp.page_url" class="mt-2" />
            </div>

            <!-- PWA App URL -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="pwa_app_url" value="{{ __('PWA App URL') }}" />
                <x-input id="pwa_app_url" type="url" class="mt-1 block w-full" wire:model.defer="teamapp.pwa_app_url" placeholder="https://app.example.com" />
                <p class="mt-1 text-sm text-gray-500">{{ __('Optional: Public-facing URL for the Progressive Web App (PWA). Example: https://myapp.example.com') }}</p>
                <x-input-error for="teamapp.pwa_app_url" class="mt-2" />
            </div>

            <!-- PWA Server URL -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="pwa_server_url" value="{{ __('PWA Server URL') }}" />
                <x-input id="pwa_server_url" type="url" class="mt-1 block w-full" wire:model="teamapp.pwa_server_url" placeholder="http://localhost:3001" />
                <p class="mt-1 text-sm text-gray-500">{{ __('Internal URL where the Node.js server runs. Auto-populated with next available port. Example: http://localhost:3001') }}</p>
                <x-input-error for="teamapp.pwa_server_url" class="mt-2" />
            </div>

            <!-- Site Selection -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="site_id" value="{{ __('Site') }}" />
                <select id="site_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" wire:model.defer="site_id">
                    <option value="">{{ __('Select a site') }}</option>
                    @foreach ($sites as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                <x-input-error for="teamapp.site_id" class="mt-2" />
            </div>

            <!-- Sort Order -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="sort_order" value="{{ __('Sort Order') }}" />
                <x-input id="sort_order" type="number" class="mt-1 block w-full" wire:model.defer="teamapp.sort_order" />
                <x-input-error for="teamapp.sort_order" class="mt-2" />
            </div>

            <!-- App Icon -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="appicon" value="{{ __('App Icon') }}" />
                @if ($teamapp->appicon)
                    <div class="mt-2 mb-4">
                        <img src="{{ $teamapp->appicon }}" alt="App Icon" class="h-16 w-16 rounded-lg">
                    </div>
                @endif
                <x-input id="photo" type="file" class="mt-1 block w-full" wire:model="photo" accept="image/*" />
                <x-input-error for="photo" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="actions">
            <x-action-message class="mr-3" on="saved">
                {{ __('Saved.') }}
            </x-action-message>

            <x-button>
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-form-section>

    <!-- Deployment Instructions Modal -->
    @if ($show_deployment_instructions)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
                <!-- Modal Header -->
                <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold">{{ __('PWA Deployment Instructions') }}</h3>
                    <button wire:click="closeDeploymentInstructions" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4 text-gray-700">
                    <p class="mb-4 font-semibold">{{ __('Your PWA Server URL has been configured. Here\'s how to deploy your Node.js app:') }}</p>

                    <div class="space-y-4">
                        <!-- Step 1 -->
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-semibold text-gray-900">{{ __('Step 1: Start Your Node.js Server') }}</h4>
                            <p class="text-sm text-gray-600 mt-1">{{ __('Start your Node.js/React/Next.js application on the configured port:') }}</p>
                            <div class="bg-gray-100 p-3 rounded mt-2 font-mono text-sm overflow-x-auto">
                                <code>cd /path/to/your/app<br>npm start</code>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">{{ __('Server will run on: ') }}<span class="font-mono">{{ $teamapp->pwa_server_url ?? 'http://localhost:3001' }}</span></p>
                        </div>

                        <!-- Step 2 -->
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-semibold text-gray-900">{{ __('Step 2: Verify Server is Running') }}</h4>
                            <p class="text-sm text-gray-600 mt-1">{{ __('Test that your server is accessible:') }}</p>
                            <div class="bg-gray-100 p-3 rounded mt-2 font-mono text-sm overflow-x-auto">
                                <code>curl {{ $teamapp->pwa_server_url ?? 'http://localhost:3001' }}/</code>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-semibold text-gray-900">{{ __('Step 3: Access Your App') }}</h4>
                            <p class="text-sm text-gray-600 mt-1">{{ __('Your app is now accessible at:') }}</p>
                            <div class="bg-gray-100 p-3 rounded mt-2 font-mono text-sm overflow-x-auto">
                                <code>{{ $teamapp->pwa_app_url ?? 'https://myapp.example.com' }}</code>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">{{ __('Prasso will proxy all requests to your Node.js server.') }}</p>
                        </div>

                        <!-- Important Notes -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mt-4">
                            <p class="text-sm font-semibold text-yellow-800">{{ __('Important Notes:') }}</p>
                            <ul class="text-xs text-yellow-700 mt-2 space-y-1 list-disc list-inside">
                                <li>{{ __('Keep your Node.js server running at all times') }}</li>
                                <li>{{ __('Use PM2 or systemd to manage the process in production') }}</li>
                                <li>{{ __('Your server must handle all HTTP methods (GET, POST, PUT, DELETE)') }}</li>
                                <li>{{ __('DNS setup is automatic for faxt.com domains') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end border-t">
                    <button wire:click="closeDeploymentInstructions" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        {{ __('Got it!') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
