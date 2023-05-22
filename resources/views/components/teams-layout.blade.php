<div class="col-span-6">
    <div class="max-w-xl text-sm">

        @foreach($teams as $team)
        <div class="grid my-4 grid-cols-3">
            <div class="col-span-2 flex items-center justify-between">
                @if (Auth::user()->currentTeam->id == $team['id'])
                <svg class="mr-2 h-5 w-5 text-green-400" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                @endif
                <div class="truncate">
                @if( !empty($team['name']))
                    {{ $team['name'] }} 
                @else
                    No Team Name Given
                @endif 
                </div>
            
                <div>
                <x-jet-responsive-nav-link href="{{ route('teams.show', $team['id']) }}" >
                    <i class="material-icons md-36">mode_edit</i>
                    </x-jet-responsive-nav-link>
                </div>
            </div>
        </div>

        @endforeach
    </div>
</div>