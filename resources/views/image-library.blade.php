<x-app-layout :site="$site ?? null">
    <x-slot name="title">{{ isset($site) ? $site->site_name . ' - ' : '' }}Image Library</x-slot>
   
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ isset($site) ? $site->site_name . ' - ' : '' }}Image Library
            </h1>
            <div class="flex items-center">
                <button id="generateAiImageBtn" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-medium rounded-full px-6 py-2.5 mr-4 transition-all duration-300 ease-in-out hover:shadow-lg hover:scale-105 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 flex items-center">
                    <i class="fas fa-magic mr-2"></i> Generate Image with AI
                </button>
                <button onclick="window.history.back()" class="bg-gradient-to-r from-gray-500 to-gray-700 text-white font-medium rounded-full px-6 py-2.5 transition-all duration-300 ease-in-out hover:shadow-lg hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Return to previous page
                </button>
            </div>
        </div>
    </x-slot>
    @include('partials._image-upload-styles')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Upload Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    @include('partials._image-upload', ['site_id' => $site->id])
                </div>
            </div>

            <!-- Alert Messages -->
            <div id="alertContainer" class="mb-6 hidden">
                <div id="alertContent" class="p-4 rounded-md">
                </div>
            </div>

            <!-- Resize Dialog -->
            <div id="resizeDialog" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden flex items-center justify-center">
                <div class="bg-white p-6 rounded-lg shadow-xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Resize Image?</h3>
                    <p id="resizeMessage" class="text-sm text-gray-500 mb-4"></p>
                    <div class="flex justify-end space-x-4">
                        <button onclick="confirmResize()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                            Yes, resize it
                        </button>
                        <button onclick="cancelResize()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                            No, I'll upload a smaller image
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-center mx-6">
                @if(session('warning') && session('show_resize_options'))
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                        <p>{{ session('warning') }}</p>
                        <div class="mt-3">
                            <form action="{{ route('images.confirm-resize') }}" method="POST">
                                @csrf
                                <button type="submit" class="teambutton teambutton-hover text-white font-bold py-2 px-4 rounded mr-2">
                                    Yes, resize it
                                </button>
                                <a href="{{ route('image.library') }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded inline-block">
                                    No, I'll upload a smaller image
                                </a>
                            </form>
                        </div>
                    </div>
                @endif
                @if ($images->count() === 0)
                    <div class="text-center bg-gray-50 border-2 border-indigo-600/100">
                        <div class="font-sans text-lg font-semibold text-gray-600">
                            No images found.
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-3 gap-4 justify-center">
                        @foreach ($images as $image)
                            <div class="flex items-center justify-center">
                                <div class="relative inline-block">
                                    <img class="block" src="{{ config('constants.CLOUDFRONT_ASSET_URL').$image->path }}" alt="{{ $image->path }}">
                                    <button type="button"
                                        class="absolute top-2 right-2 z-10 bg-black bg-opacity-60 text-white rounded-full w-10 h-10 flex items-center justify-center shadow-md border border-white border-opacity-20 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500"
                                        data-image-id="{{ $image->id }}">
                                        <span class="sr-only">Delete</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                            <path fill-rule="evenodd" d="M9 3.75A.75.75 0 0 1 9.75 3h4.5a.75.75 0 0 1 .75.75V6h3a.75.75 0 0 1 0 1.5h-.75v12A2.25 2.25 0 0 1 15 21.75H9A2.25 2.25 0 0 1 6.75 19.5v-12H6a.75.75 0 0 1 0-1.5h3V3.75Zm1.5 2.25h3V4.5h-3V6Zm-2.25 1.5v12c0 .414.336.75.75.75h6a.75.75 0 0 0 .75-.75v-12H8.25Zm3 2.25a.75.75 0 0 1 .75.75v6a.75.75 0 0 1-1.5 0v-6a.75.75 0 0 1 .75-.75Zm3 0a.75.75 0 0 1 .75.75v6a.75.75 0 0 1-1.5 0v-6a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- AI Image Generation Modal -->
    <div id="aiImageModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="teambutton text-lg font-medium">Generate Image with AI</h3>
                <button onclick="closeAiImageModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="aiImageForm">
                @csrf
                <input type="hidden" id="ai_site_id" name="site_id" value="{{ $site->id }}" />
                
                <div class="mb-4">
                    <label for="imagePrompt" class="block text-sm font-medium text-gray-700 mb-2">
                        Describe the image you want to generate
                    </label>
                    <textarea id="imagePrompt" name="prompt" rows="4" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Example: A professional logo with blue and green colors featuring a mountain and sun"></textarea>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" onclick="closeAiImageModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 mr-3">
                        Cancel
                    </button>
                    <button type="submit" id="generateImageBtn" class="teambutton text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Generate Image
                    </button>
                </div>
            </form>
            
            <div id="aiGenerationStatus" class="mt-4 hidden">
                <div class="flex items-center justify-center">
                    <div class="spinner mr-3"></div>
                    <p>Generating your image... This may take a moment.</p>
                </div>
            </div>
            
            <div id="aiGenerationResult" class="mt-4 hidden">
                <div id="aiGenerationSuccess" class="hidden">
                    <div class="text-green-600 mb-2">Image successfully generated!</div>
                    <div class="flex justify-center">
                        <img id="generatedImage" src="" alt="Generated image" class="max-h-64 max-w-full">
                    </div>
                </div>
                <div id="aiGenerationError" class="hidden text-red-600">
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/image-upload.js') }}"></script>
        <script src="{{ asset('js/ai-image-generation.js') }}"></script>
        <script>
            document.addEventListener('click', function(e) {
                const target = e.target instanceof Element ? e.target : null;
                if (!target) return;

                const button = target.closest('button[data-image-id]');
                if (!button) return;

                const id = button.getAttribute('data-image-id');
                const imageId = id ? parseInt(id, 10) : null;
                if (!imageId) return;
                deleteImageFromLibrary(imageId);
            });

            async function deleteImageFromLibrary(imageId) {
                if (!imageId) return;
                if (!confirm('Delete this image? This cannot be undone.')) return;

                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;
                const siteIdEl = document.getElementById('ai_site_id');
                const siteId = siteIdEl ? siteIdEl.value : null;

                try {
                    const response = await fetch(`/images/${imageId}?site_id=${encodeURIComponent(siteId || '')}`, {
                        method: 'DELETE',
                        headers: {
                            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });

                    const contentType = response.headers.get('content-type') || '';
                    const result = contentType.includes('application/json') ? await response.json() : null;

                    if (!response.ok) {
                        alert(result?.error || result?.message || `Delete failed (${response.status}).`);
                        return;
                    }

                    if (result?.success) {
                        window.location.reload();
                        return;
                    }

                    alert(result?.error || 'Delete failed.');
                } catch (e) {
                    console.error(e);
                    alert('Delete failed due to a network error.');
                }
            }
        </script>
    @endpush
</x-app-layout>