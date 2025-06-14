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
                            <div class="flex items-center justify-center max-h-200">
                                <img src="{{ config('constants.CLOUDFRONT_ASSET_URL').$image->path }}" alt="{{ $image->path }}">
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
    @endpush
</x-app-layout>