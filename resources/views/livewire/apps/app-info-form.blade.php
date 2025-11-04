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
                <div class="flex items-center justify-between">
                    <x-label for="pwa_server_url" value="{{ __('PWA Server URL') }}" />
                    <button type="button" wire:click="$set('show_deployment_instructions', true)" class="text-xs text-primary-600 hover:text-primary-800 hover:underline">
                        {{ __('View Setup Instructions') }}
                    </button>
                </div>
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

            <x-button class="bg-primary hover:bg-primary-700 text-white focus:ring-2 focus:ring-primary-500">
            {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-form-section>

    <!-- Sync Pages to App Section -->
    @if ($teamapp->id ?? false && $site_id)
    <x-section-border />
    <div class="mt-12">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <x-section-title>
                <x-slot name="title">{{ __('Sync Site Pages to App Tabs') }}</x-slot>
                <x-slot name="description">{{ __('Convert your site pages into mobile app navigation tabs.') }}</x-slot>
            </x-section-title>

            <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="px-4 py-5 bg-white dark:bg-gray-800 sm:p-6 shadow sm:rounded-md">
                    <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6">
                            <p class="text-sm text-gray-600 mb-4">
                                {{ __('Select which pages you want to sync as app tabs from your site.') }}
                            </p>
                            <a href="{{ route('apps.sync-pages-with-site', ['teamid' => $team_id, 'appid' => $teamapp->id, 'siteid' => $site_id]) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs bg-primary-600 hover:bg-primary-700 text-primary-foreground focus:ring-2 focus:ring-primary-500 uppercase tracking-widest transition ease-in-out duration-150"
                               >
                                {{ __('Sync Pages to Tabs') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Deployment Instructions Modal -->
    @if ($show_deployment_instructions)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full flex flex-col max-h-screen">
                <!-- Modal Header (Fixed) -->
                <div class="bg-primary-600 text-white px-6 py-4 flex justify-between items-center flex-shrink-0">
                    <h3 class="text-lg font-semibold">{{ __('PWA Deployment Instructions') }}</h3>
                    <button type="button" wire:click="closeDeploymentInstructions" class="text-white hover:text-gray-200 flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="px-6 py-4 text-gray-700 overflow-y-auto flex-1">
                    <p class="mb-4 font-semibold">{{ __('Your PWA Server URL has been configured. Here\'s how to deploy your Node.js app:') }}</p>

                    <div class="space-y-4">
                        <!-- Step 1: Configure Your App -->
                        <div class="border-l-4 border-primary-500 pl-4">
                            <h4 class="font-semibold text-gray-900">{{ __('Step 1: Configure Your App to Use the Server URL') }}</h4>
                            <p class="text-sm text-gray-600 mt-1">{{ __('Your app must listen on the configured server URL. Set the PORT environment variable:') }}</p>
                            
                            <!-- Extract port from pwa_server_url -->
                            @php
                                $serverUrl = $teamapp->pwa_server_url ?? 'http://localhost:3001';
                                $parsedUrl = parse_url($serverUrl);
                                $port = $parsedUrl['port'] ?? 3001;
                                $host = $parsedUrl['host'] ?? 'localhost';
                            @endphp
                            
                            <p class="text-xs text-gray-600 mt-2 font-semibold">{{ __('For React (Create React App):') }}</p>
                            <div class="bg-gray-100 p-3 rounded mt-1 font-mono text-sm overflow-x-auto">
                                <code>PORT={{ $port }} npm start</code>
                            </div>
                            
                            <p class="text-xs text-gray-600 mt-2 font-semibold">{{ __('For Next.js:') }}</p>
                            <div class="bg-gray-100 p-3 rounded mt-1 font-mono text-sm overflow-x-auto">
                                <code>npm run build<br>PORT={{ $port }} npm start</code>
                            </div>
                            
                            <p class="text-xs text-gray-600 mt-2 font-semibold">{{ __('Or set in .env.local:') }}</p>
                            <div class="bg-gray-100 p-3 rounded mt-1 font-mono text-sm overflow-x-auto">
                                <code>PORT={{ $port }}<br>HOST={{ $host }}</code>
                            </div>
                        </div>

                        <!-- Step 2: Start Your Server -->
                        <div class="border-l-4 border-primary-500 pl-4">
                            <h4 class="font-semibold text-gray-900">{{ __('Step 2: Start Your Node.js Server') }}</h4>
                            <p class="text-sm text-gray-600 mt-1">{{ __('Navigate to your app directory and start the server:') }}</p>
                            <div class="bg-gray-100 p-3 rounded mt-2 font-mono text-sm overflow-x-auto">
                                <code>cd /path/to/your/app<br>npm start</code>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">{{ __('Server will run on: ') }}<span class="font-mono font-semibold">{{ $serverUrl }}</span></p>
                        </div>

                        <!-- Step 3: Verify Server is Running -->
                        <div class="border-l-4 border-primary-500 pl-4">
                            <h4 class="font-semibold text-gray-900">{{ __('Step 3: Verify Server is Running') }}</h4>
                            <p class="text-sm text-gray-600 mt-1">{{ __('Test that your server is accessible:') }}</p>
                            <div class="bg-gray-100 p-3 rounded mt-2 font-mono text-sm overflow-x-auto">
                                <code>curl {{ $serverUrl }}/</code>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">{{ __('You should see your app\'s HTML response.') }}</p>
                        </div>

                        <!-- Step 4: Access Your App -->
                        <div class="border-l-4 border-primary-500 pl-4">
                            <h4 class="font-semibold text-gray-900">{{ __('Step 4: Access Your App') }}</h4>
                            <p class="text-sm text-gray-600 mt-1">{{ __('Your app is now accessible at:') }}</p>
                            <div class="bg-gray-100 p-3 rounded mt-2 font-mono text-sm overflow-x-auto">
                                <code>{{ $teamapp->pwa_app_url ?? 'https://myapp.example.com' }}</code>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">{{ __('Prasso will proxy all requests to your Node.js server running on port ') }}<span class="font-mono">{{ $port }}</span>.</p>
                        </div>

                        <!-- Step 5: Deploy Frontend with deploy.py (Pre-filled) -->
                        <div class="border-l-4 border-primary-500 pl-4">
                            <h4 class="font-semibold text-gray-900">{{ __('Step 5: Deploy Frontend to Server (copy & run)') }}</h4>
                            @php
                                $siteName = $site_name ?? 'site';
                                $appDir = \Illuminate\Support\Str::of($siteName)->slug('_').'_app';
                                $sudoUser = env('DEPLOY_SUDO_USER', 'ubuntu');
                                $appUser = env('DEPLOY_APP_USER', 'appuser');
                                $webUser = env('DEPLOY_WEB_USER', 'www-data');
                                $serverHost = env('PRASSO_SERVER_HOST', 'your-ec2-public-ip');
                                $sshKeyPath = env('DEPLOY_SSH_KEY_PATH', '~/.ssh/prasso-deploy.pem');
                            @endphp
                            <p class="text-sm text-gray-600 mt-1">{{ __('Run this from your prasso_web folder on your workstation after building:') }}</p>
                            <div class="bg-gray-100 p-3 rounded mt-2 font-mono text-sm overflow-x-auto">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs text-gray-600">{{ __('Full deployment command') }}</span>
                                    <button type="button" class="px-2 py-1 text-xs bg-primary-600 text-primary-foreground rounded hover:bg-primary-700"
                                        data-copied="{{ __('Copied') }}" data-copy="{{ __('Copy') }}"
                                        onclick="copyCmd('fullDeployCmd', this)">{{ __('Copy') }}</button>
                                </div>
                                <pre id="fullDeployCmd"><code>npm run build 
./deploy.py \
  --server {{ $serverHost }} \
  --sudo-user {{ $sudoUser }} \
  --app-user {{ $appUser }} \
  --web-user {{ $webUser }} \
  --app-dir-name {{ $appDir }} \
  --port {{ $port }} \
  --key {{ $sshKeyPath }} </code></pre>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">{{ __('Subsequent updates (files only):') }}</p>
                            <div class="bg-gray-50 p-3 rounded mt-1 font-mono text-xs overflow-x-auto">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] text-gray-600">{{ __('Update-only command') }}</span>
                                    <button type="button" class="px-2 py-1 text-[10px] bg-primary text-primary-foreground rounded hover:bg-primary-700"
                                        data-copied="{{ __('Copied') }}" data-copy="{{ __('Copy') }}"
                                        onclick="copyCmd('updateDeployCmd', this)">{{ __('Copy') }}</button>
                                </div>
                                <pre id="updateDeployCmd"><code>npm run build 
./deploy.py \
  --update-only \
  --server {{ $serverHost }} \
  --sudo-user {{ $sudoUser }} \
  --app-dir-name {{ $appDir }} \
  --key {{ $sshKeyPath }}</code></pre>
                            </div>
                            <ul class="text-xs text-gray-600 mt-3 space-y-1 list-disc list-inside">
                                <li>{{ __('Deploy path: /var/www/html/prasso_api/public/hosted_sites/') }}<span class="font-mono">{{ $appDir }}</span></li>
                                <li>{{ __('PM2 process name:') }} <span class="font-mono">{{ $appDir }}</span></li>
                                <li>{{ __('Port:') }} <span class="font-mono">{{ $port }}</span></li>
                            </ul>
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

                <!-- Modal Footer (Fixed) -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end border-t flex-shrink-0">
                    <button type="button" wire:click="closeDeploymentInstructions" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700 transition">
                        {{ __('Got it!') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
