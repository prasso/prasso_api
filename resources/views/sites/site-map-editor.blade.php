<x-app-layout :site="$site ?? null">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Site Map Editor') }} - {{ $site->name }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('sites.show', $site) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Back to Site') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Site Map Editor</h2>
                
                <div id="site-map-editor" class="space-y-4">
                    <!-- Hidden Pages Section -->
                    <div class="space-y-2">
                        <h3 class="font-medium">Hidden Pages</h3>
                        <div class="space-y-2" id="hidden-pages">
                            @foreach($hiddenPages as $page)
                                <div class="flex items-center space-x-4 p-2 bg-gray-50 rounded" 
                                     data-page-id="{{ $page->id }}">
                                    <span class="cursor-move">⋮⋮</span>
                                    <span>{{ $page->title }}</span>
                                    <select class="menu-level-select rounded border-gray-300">
                                        <option value="-1" selected>Hidden</option>
                                        <option value="0">Top Level</option>
                                        @foreach($topLevelPages as $parent)
                                            <option value="{{ $parent->id }}">
                                                Under {{ $parent->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Top Level Pages -->
                    <div class="space-y-2">
                        <h3 class="font-medium">Main Navigation</h3>
                        <div class="space-y-2" id="top-level-pages">
                            @foreach($topLevelPages as $page)
                                <div class="flex items-center space-x-4 p-2 bg-gray-50 rounded" 
                                     data-page-id="{{ $page->id }}">
                                    <span class="cursor-move">⋮⋮</span>
                                    <span>{{ $page->title }}</span>
                                    <select class="menu-level-select">
                                        <option value="-1">Hidden</option>
                                        <option value="0" selected>Top Level</option>
                                        @foreach($topLevelPages as $parent)
                                            @if($parent->id !== $page->id)
                                                <option value="{{ $parent->id }}">
                                                    Under {{ $parent->title }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="button" 
                            id="save-site-map" 
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize drag and drop
            new Sortable(document.getElementById('top-level-pages'), {
                handle: '.cursor-move',
                animation: 150
            });

            new Sortable(document.getElementById('hidden-pages'), {
                handle: '.cursor-move',
                animation: 150
            });

            // Save changes
            document.getElementById('save-site-map').addEventListener('click', function() {
                const pages = [];
                document.querySelectorAll('[data-page-id]').forEach(pageEl => {
                    pages.push({
                        id: pageEl.dataset.pageId,
                        menu_id: pageEl.querySelector('.menu-level-select').value
                    });
                });

                fetch(`/sites/{{ $site->id }}/site-map`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ pages })
                })
                .then(response => response.json())
                .then(data => {
                    alert('Site map updated successfully');
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving the site map');
                });
            });
        });
    </script>
    @endpush
</x-app-layout> 