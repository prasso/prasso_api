<div class="col-span-6">
    <div class="max-w-xl text-sm">

        @foreach($teams as $team)
        <div class="grid my-4 grid-cols-3">
            <div>
            <label class="flex items-center">
                <span class="ml-2">  
                @if( !empty($team['name']))
                    {{ $team['name'] }} 
                @else
                    No Team Name Given
                @endif                    
                </span>
            </label>
            </div>
            <div></div>
            <div>
            <x-jet-responsive-nav-link href="{{ route('teams.show', $team['id']) }}" >
                <i class="material-icons md-36">mode_edit</i>
                </x-jet-responsive-nav-link>
            </div>
        </div>

        @endforeach
    </div>
</div>