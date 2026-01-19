<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
    <h2 class="text-xl font-semibold mb-4">Create Page with AI</h2>
    
    @if(!$showPreview)
        <form wire:submit.prevent="generateContent">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="mb-4">
                    <label for="section" class="block text-gray-700 text-sm font-bold mb-2">Unique Name:</label>
                    <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="section" placeholder="Enter Unique Name" wire:model="section">
                    @error('section') <span class="text-red-500">{{ $message }}</span>@enderror
                </div>
                
                <div class="mb-4">
                    <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title:</label>
                    <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="title" placeholder="Enter Title" wire:model="title">
                    @error('title') <span class="text-red-500">{{ $message }}</span>@enderror
                </div>
            </div>
            
            <div class="mb-4">
                <label for="type" class="block text-gray-700 text-sm font-bold mb-2">Page Type:</label>
                <select wire:model="type" name="type" id="type" class="mt-1 block w-full border-2 border-indigo-600/100 p-2">
                    <option value="1">HTML Content</option>
                    <option value="2">S3 File</option>
                </select>
                @error('type') <span class="text-red-500">{{ $message }}</span>@enderror
            </div>
            
            <div class="mb-4">
                <label for="prompt" class="block text-gray-700 text-sm font-bold mb-2">Describe what you want to create:</label>
                <textarea rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="prompt" wire:model="prompt" placeholder="Describe the page content you want to create with AI..."></textarea>
                @error('prompt') <span class="text-red-500">{{ $message }}</span>@enderror
            </div>
            
            <div class="flex items-center justify-between">
                <button wire:loading.attr="disabled" type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    <span wire:loading.remove wire:target="generateContent">Generate Content</span>
                    <span wire:loading wire:target="generateContent">Generating...</span>
                </button>
            </div>
        </form>
    @else
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">Preview Generated Content</h3>
            
            <div class="mb-4">
                <div class="border rounded-md p-4 bg-white min-h-[400px] overflow-auto">
                    {!! $htmlContent !!}
                </div>
            </div>
            
            <div class="mb-4">
                <label for="htmlContent" class="block text-gray-700 text-sm font-bold mb-2">HTML Content (Edit if needed):</label>
                <textarea rows="10" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono" id="htmlContent" wire:model="htmlContent"></textarea>
                @error('htmlContent') <span class="text-red-500">{{ $message }}</span>@enderror
            </div>
            
            <div class="flex space-x-4">
                <button wire:click="savePage" wire:loading.attr="disabled" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    <span wire:loading.remove wire:target="savePage">Save Page</span>
                    <span wire:loading wire:target="savePage">Saving...</span>
                </button>
                
                <button wire:click="cancelPreview" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Back to Editor
                </button>
            </div>
        </div>
    @endif
    
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('notify', (event) => {
                alert(event.message);
            });
            
            Livewire.on('pageCreated', () => {
                // Refresh the page list or notify parent component
                window.location.reload();
            });
        });
    </script>
</div>
