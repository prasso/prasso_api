<!-- Image Upload Form -->
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6 bg-white border-b border-gray-200">
        <form id="uploadForm" class="space-y-4" data-site-id="{{ $site_id ?? '' }}">
            @csrf
            @if(isset($site))
            <input type="hidden" id="site_id" name="site_id" value="{{ $site->id }}" />
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700">
                    Upload Image
                </label>
                <div class="mt-1 flex items-center">
                    <input type="file" 
                           id="image" 
                           name="image[]" 
                           multiple
                           accept="image/*"
                           class="block w-full text-sm text-gray-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-full file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-indigo-50 file:text-indigo-700
                                  hover:file:bg-indigo-100">
                </div>
            </div>
            <div class="mt-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" 
                           id="resize" 
                           name="resize" 
                           class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                    <span class="ml-2 text-sm text-gray-600">Resize images if they are too large</span>
                </label>
            </div>
            <button type="submit" 
        id="uploadButton"
        class="teambutton inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
    Upload Image
</button>
        </form>
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
            <button onclick="confirmResize()" class="teambutton text-white px-4 py-2 rounded hover:bg-indigo-700">
                Yes, resize it
            </button>
            <button onclick="cancelResize()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                No, I'll upload a smaller image
            </button>
        </div>
    </div>
</div>
