<x-app-layout :site="$site ?? null">
    <x-slot name="title">Sync Pages to App Tabs</x-slot>
    
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sync Pages to App: ' . $teamapp->app_name) }}
        </h2>
        <a href="{{ route('apps.edit', ['teamid' => $team->id, 'appid' => $teamapp->id]) }}" class="text-sm text-gray-700 block mt-2">
            {{ __('← Back to App') }}
        </a>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                @if (isset($error))
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <p>{{ $error }}</p>
                    </div>
                @endif

                @if ($sitePages->count() > 0)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            {{ __('Select Pages to Sync') }}
                        </h3>
                        <p class="text-sm text-gray-600 mb-6">
                            {{ __('Choose which site pages you want to convert into app tabs. Each selected page will become a navigation tab in the mobile app.') }}
                        </p>

                        <form action="{{ route('apps.sync-pages-submit', ['teamid' => $team->id, 'appid' => $teamapp->id]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="site_id" value="{{ $selectedSiteId }}">
                            
                            <div class="space-y-3 mb-6">
                                @foreach ($sitePages as $page)
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input 
                                                id="page_{{ $page->id }}" 
                                                name="selected_pages[]" 
                                                type="checkbox" 
                                                value="{{ $page->id }}"
                                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                                            >
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="page_{{ $page->id }}" class="font-medium text-gray-700">
                                                {{ $page->title }}
                                            </label>
                                            <p class="text-gray-500 text-xs mt-1">
                                                {{ __('Section: ') }} <span class="font-mono">{{ $page->section }}</span>
                                                @if ($page->login_required)
                                                    <span class="ml-2 inline-block bg-red-100 text-red-800 text-xs px-2 py-1 rounded">
                                                        {{ __('Login Required') }}
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <button type="button" onclick="selectAll()" class="text-sm text-blue-600 hover:text-blue-800 mr-4">
                                        {{ __('Select All') }}
                                    </button>
                                    <button type="button" onclick="deselectAll()" class="text-sm text-blue-600 hover:text-blue-800">
                                        {{ __('Deselect All') }}
                                    </button>
                                </div>
                                <div class="flex gap-3">
                                    <a href="{{ route('apps.edit', ['teamid' => $team->id, 'appid' => $teamapp->id]) }}" 
                                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                        {{ __('Sync Selected Pages') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">
                            {{ __('No Pages Available') }}
                        </h3>
                        <p class="text-blue-700 mb-4">
                            {{ __('There are no site pages available to sync. Please create some pages in your site first.') }}
                        </p>
                        <a href="{{ route('apps.edit', ['teamid' => $team->id, 'appid' => $teamapp->id]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            {{ __('Back to App') }}
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <script>
        function selectAll() {
            document.querySelectorAll('input[name="selected_pages[]"]').forEach(checkbox => {
                checkbox.checked = true;
            });
        }

        function deselectAll() {
            document.querySelectorAll('input[name="selected_pages[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    </script>
</x-app-layout>
