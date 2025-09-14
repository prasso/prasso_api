<x-filament-panels::page>
    <div class="flex justify-end mb-4">
        <x-filament::button
            wire:click="resetImport"
            color="danger"
            icon="heroicon-o-trash"
        >
            Reset Import Data
        </x-filament::button>
    </div>
    
    {{ $this->form }}

    @if($hasUploadedFile)
    <div id="preview-section"></div>
    <!-- Data Quality Information -->
    <div class="space-y-4 my-4">
        @if(isset($dataQuality['recommendations']) && count($dataQuality['recommendations']) > 0)
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h3 class="text-sm font-medium text-yellow-800">Recommendations</h3>
                <ul class="mt-2 list-disc list-inside text-sm text-yellow-700">
                    @foreach($dataQuality['recommendations'] as $recommendation)
                        <li>{{ $recommendation }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(isset($dataQuality['stats']))
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="text-sm font-medium text-blue-800">Data Statistics</h3>
                <ul class="mt-2 list-disc list-inside text-sm text-blue-700">
                    <li>Total rows: {{ $dataQuality['stats']['total_rows'] }}</li>
                    @if(isset($dataQuality['stats']['empty_emails']))
                        <li>Empty emails: {{ $dataQuality['stats']['empty_emails'] }}</li>
                    @endif
                    @if(isset($dataQuality['stats']['empty_names']))
                        <li>Empty names: {{ $dataQuality['stats']['empty_names'] }}</li>
                    @endif
                    @if(isset($dataQuality['stats']['duplicate_emails']))
                        <li>Duplicate emails: {{ count($dataQuality['stats']['duplicate_emails']) }}</li>
                    @endif
                    @if(isset($dataQuality['stats']['invalid_emails']))
                        <li>Invalid emails: {{ count($dataQuality['stats']['invalid_emails']) }}</li>
                    @endif
                </ul>
            </div>
        @endif
    </div>
    
    <!-- Selected Team Information -->
    <div class="p-4 my-4 bg-blue-50 border border-blue-200 rounded-lg">
        <h3 class="text-lg font-medium text-blue-800">Selected Team</h3>
        <div class="mt-2">
            @php
                $selectedTeam = $this->getSelectedTeam();
            @endphp
            <p class="text-blue-700">
                <span class="font-semibold">Team:</span> {{ $selectedTeam['name'] }}
            </p>
            <p class="text-blue-700">
                <span class="font-semibold">Team ID:</span> {{ $selectedTeam['id'] ?? 'Not set' }}
            </p>
            <p class="text-sm text-blue-600 mt-2">
                Users will be imported to this team. If a user already exists, they will be updated and added to this team if not already a member.
            </p>
        </div>
    </div>
    
    <!-- Data Preview -->
    <div class="p-4 my-4 bg-gray-100 rounded-lg">
        <h3 class="text-lg font-medium">Data Preview (First 5 Rows)</h3>
        <div class="overflow-x-auto mt-2">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Row</th>
                        @foreach($headers as $header)
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach(array_slice($csvData, 0, 5) as $index => $row)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 2 }}</td>
                            @foreach($headers as $header)
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $row[$header] ?? '' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Field Mapping -->
    <div class="p-4 my-4 bg-gray-100 rounded-lg">
        <h3 class="text-lg font-medium">Field Mapping</h3>
        <div class="overflow-x-auto mt-2">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CSV Column</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Field</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($mappings as $userField => $csvHeader)
                        @if($csvHeader)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $csvHeader }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $userField }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <form wire:submit.prevent="import({{ $selectedTeam['id'] ?? 0 }})" class="mt-6">
        <input type="hidden" wire:model="team_id" value="{{ $team_id }}" />
        
        <div class="p-4 my-4 bg-gray-100 rounded-lg">
            <h3 class="text-lg font-medium">Import Information</h3>
            <p class="mt-2">You are about to import {{ $total_rows }} users to team: <strong>{{ $selectedTeam['name'] }} (ID: {{ $selectedTeam['id'] ?? 'Not set' }})</strong></p>
        </div>
        
        <div class="flex items-center justify-end gap-x-3 mt-6">
            <x-filament::button 
                type="button" 
                color="gray" 
                wire:click="cancel"
            >
                Cancel
            </x-filament::button>

            <x-filament::button 
                type="submit" 
                color="success"
            >
                Import {{ $total_rows }} Users
            </x-filament::button>
        </div>
    </form>
    @endif
    <script>
        document.addEventListener('livewire:initialized', function() {
            Livewire.on('scroll-to-preview', function() {
                // Add a small delay to ensure the DOM is updated
                setTimeout(function() {
                    const previewSection = document.getElementById('preview-section');
                    if (previewSection) {
                        previewSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 300);
            });
        });
    </script>
</x-filament-panels::page>
