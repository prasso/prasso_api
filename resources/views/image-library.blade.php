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
                <button id="recolorAiImageBtn" class="bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-medium rounded-full px-6 py-2.5 mr-4 transition-all duration-300 ease-in-out hover:shadow-lg hover:scale-105 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-opacity-50 flex items-center">
                    <i class="fas fa-palette mr-2"></i> Recolor Image with AI
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

    <!-- AI Image Recoloring Modal -->
    <div id="aiRecolorModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="teambutton text-lg font-medium">Recolor Image with AI</h3>
                <button onclick="closeAiRecolorModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="aiRecolorForm">
                @csrf
                <input type="hidden" id="recolor_site_id" name="site_id" value="{{ $site->id }}" />
                
                <div class="mb-4">
                    <label for="imageSelector" class="block text-sm font-medium text-gray-700 mb-2">
                        Select an image to recolor
                    </label>
                    <select id="imageSelector" name="image_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Select an image --</option>
                        @foreach ($images as $image)
                            <option value="{{ $image->id }}" data-src="{{ config('constants.CLOUDFRONT_ASSET_URL').$image->path }}">{{ basename($image->path) }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="selectedImagePreview" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selected Image:</label>
                    <div class="flex justify-center relative">
                        <canvas id="imageCanvas" class="max-h-64 max-w-full hidden"></canvas>
                        <img id="previewImage" src="" alt="Selected image" class="max-h-64 max-w-full">
                        <div id="eyedropperCursor" class="hidden absolute pointer-events-none border-2 border-white rounded-full w-6 h-6" style="box-shadow: 0 0 0 1px black; transform: translate(-50%, -50%); z-index: 10;"></div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="oldColor" class="block text-sm font-medium text-gray-700 mb-2">
                        Old Color (color to replace)
                    </label>
                    <div class="flex items-center">
                        <input type="color" id="oldColorPicker" class="h-12 w-12 mr-3 cursor-pointer border-2 border-gray-300 rounded shadow-sm" style="padding: 0;">
                        <button type="button" id="oldColorEyedropper" class="bg-gray-200 hover:bg-gray-300 text-gray-700 p-2 rounded-md mr-2 flex items-center justify-center" title="Pick color from image">
                            <i class="fas fa-eye-dropper"></i>
                        </button>
                        <div class="flex-1">
                            <input type="text" id="oldColor" name="old_color" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="#000000" pattern="^#[0-9A-Fa-f]{6}$">
                            <p class="text-xs text-gray-500 mt-1">Click the eyedropper to select a color from the image</p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="newColor" class="block text-sm font-medium text-gray-700 mb-2">
                        New Color (replacement color)
                    </label>
                    <div class="flex items-center">
                        <input type="color" id="newColorPicker" class="h-12 w-12 mr-3 cursor-pointer border-2 border-gray-300 rounded shadow-sm" style="padding: 0;">
                        <div class="flex-1">
                            <input type="text" id="newColor" name="new_color" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="#FF5733" pattern="^#[0-9A-Fa-f]{6}$">
                            <p class="text-xs text-gray-500 mt-1">Click the color box to open the color picker</p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6 p-3 bg-gray-50 rounded-md border border-gray-200">
                    <h4 class="font-medium text-gray-700 mb-2">Color Preview</h4>
                    <div class="flex space-x-4">
                        <div class="text-center">
                            <div id="oldColorPreview" class="h-16 w-16 rounded-md border border-gray-300 mx-auto mb-1"></div>
                            <span class="text-xs">Old Color</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-arrow-right text-gray-500"></i>
                        </div>
                        <div class="text-center">
                            <div id="newColorPreview" class="h-16 w-16 rounded-md border border-gray-300 mx-auto mb-1"></div>
                            <span class="text-xs">New Color</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="button" onclick="closeAiRecolorModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 mr-3">
                        Cancel
                    </button>
                    <button type="submit" id="recolorImageBtn" class="teambutton text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Recolor Image
                    </button>
                </div>
            </form>
            
            <div id="aiRecoloringStatus" class="mt-4 hidden">
                <div class="flex items-center justify-center">
                    <div class="spinner mr-3"></div>
                    <p>Recoloring your image... This may take a moment.</p>
                </div>
            </div>
            
            <div id="aiRecoloringResult" class="mt-4 hidden">
                <div id="aiRecoloringSuccess" class="hidden">
                    <div class="text-green-600 mb-2">Image successfully recolored!</div>
                    <div class="flex justify-center">
                        <img id="recoloredImage" src="" alt="Recolored image" class="max-h-64 max-w-full">
                    </div>
                </div>
                <div id="aiRecoloringError" class="hidden text-red-600">
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/image-upload.js') }}"></script>
        <script src="{{ asset('js/ai-image-generation.js') }}"></script>
        <script src="{{ asset('js/ai-image-recoloring.js') }}"></script>
    @endpush
</x-app-layout>