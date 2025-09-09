<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">AI HTML Editor - {{ $sitePage->title }}</h1>
        <a href="{{ route('site.edit.mysite') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to Site Pages
        </a>
    </div>

    <div class="mb-6">
        <div class="flex space-x-4 mb-4">
            <button 
                wire:click="setViewMode('edit')" 
                class="px-4 py-2 rounded {{ $viewMode === 'edit' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}"
            >
                Edit
            </button>
            <button 
                wire:click="setViewMode('preview')" 
                class="px-4 py-2 rounded {{ $viewMode === 'preview' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}"
            >
                Preview
            </button>
            <button 
                wire:click="setViewMode('history')" 
                class="px-4 py-2 rounded {{ $viewMode === 'history' ? 'bg-blue-600 text-white' : 'bg-gray-200' }}"
            >
                History
            </button>
        </div>
    </div>

    @if($viewMode === 'edit')
        <div class="mb-6">
            <label for="prompt" class="block text-sm font-medium text-gray-700 mb-2">AI Prompt</label>
            <div class="flex">
                <textarea 
                    id="prompt" 
                    wire:model="prompt" 
                    rows="3" 
                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    placeholder="Enter your instructions for modifying or creating HTML content..."
                ></textarea>
            </div>
            @error('prompt') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex space-x-4 mb-6">
            <button 
                wire:click="modifyHtml" 
                wire:loading.attr="disabled" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                {{ empty($htmlContent) ? 'disabled' : '' }}
            >
                <span wire:loading.remove wire:target="modifyHtml">Modify HTML</span>
                <span wire:loading wire:target="modifyHtml">Processing...</span>
            </button>
            <button 
                wire:click="createHtml" 
                wire:loading.attr="disabled" 
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="createHtml">Create New HTML</span>
                <span wire:loading wire:target="createHtml">Processing...</span>
            </button>
        </div>

        <div class="mb-6">
            <label for="htmlContent" class="block text-sm font-medium text-gray-700 mb-2">HTML Content</label>
            <div class="relative">
                <textarea 
                    id="htmlContent" 
                    wire:model="htmlContent" 
                    rows="20" 
                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md font-mono"
                ></textarea>
            </div>
        </div>
    @elseif($viewMode === 'preview')
        <div class="mb-6 border rounded-md p-4 bg-gray-50">
            <h2 class="text-lg font-semibold mb-4">Preview</h2>
            <div class="border rounded-md p-4 bg-white min-h-[400px]">
                <div id="html-preview">
                    {!! $htmlContent !!}
                </div>
            </div>
        </div>
    @elseif($viewMode === 'confirm')
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-4">Review Changes</h2>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Please review the changes before applying them. You can accept or reject the modifications.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="border rounded-md p-2">
                    <h3 class="font-medium text-gray-700 mb-2">Original HTML</h3>
                    <div class="bg-gray-50 p-2 rounded overflow-auto h-64">
                        <pre class="text-xs">{{ $originalHtml }}</pre>
                    </div>
                </div>
                <div class="border rounded-md p-2">
                    <h3 class="font-medium text-gray-700 mb-2">Modified HTML</h3>
                    <div class="bg-gray-50 p-2 rounded overflow-auto h-64">
                        <pre class="text-xs">{{ $modifiedHtml }}</pre>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <h3 class="font-medium text-gray-700 mb-2">Preview of Changes</h3>
                <div class="border rounded-md p-4 bg-white min-h-[200px] overflow-auto">
                    {!! $modifiedHtml !!}
                </div>
            </div>
            
            <div class="flex space-x-4">
                <button 
                    wire:click="confirmChanges" 
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                >
                    Apply Changes
                </button>
                <button 
                    wire:click="rejectChanges" 
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                >
                    Reject Changes
                </button>
            </div>
        </div>
    @elseif($viewMode === 'history')
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-4">Modification History</h2>
            @if(count($modificationHistory) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prompt</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($modificationHistory as $modification)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($modification['created_at'])->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ \Illuminate\Support\Str::limit($modification['prompt'], 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button 
                                            wire:click="applyModification('{{ $modification['id'] }}')"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Apply
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-gray-500">No modification history available.</div>
            @endif
        </div>
    @endif

    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('notify', (event) => {
                alert(event.message);
            });
        });
    </script>
</div>
