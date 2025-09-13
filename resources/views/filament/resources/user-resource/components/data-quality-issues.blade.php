<div class="space-y-4">
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

    @if(isset($dataQuality['issues']) && count($dataQuality['issues']) > 0)
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <h3 class="text-sm font-medium text-red-800">Issues Found</h3>
            <div class="mt-2 max-h-60 overflow-y-auto">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach(array_slice($dataQuality['issues'], 0, 10) as $issue)
                        <li>{{ $issue }}</li>
                    @endforeach
                    
                    @if(count($dataQuality['issues']) > 10)
                        <li class="font-medium">... and {{ count($dataQuality['issues']) - 10 }} more issues</li>
                    @endif
                </ul>
            </div>
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
