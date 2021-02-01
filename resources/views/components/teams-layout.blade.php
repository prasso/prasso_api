<div class="col-span-6">
    <div class="max-w-xl text-sm">
        @foreach($teams as $team)

        <x-jet-responsive-nav-link class="btn-blue" href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" :active="request()->routeIs('teams.show')">
                    {{ __('Team Settings') }}
                </x-jet-responsive-nav-link>
         <div class="flex my-4">
            <label class="flex items-center">
                <input type="checkbox" class="form-checkbox" value="{{$team['id']}}">
                <span class="ml-2">  
                @if( !empty($team['name']))
                    {{ $team['name'] }} 
                @else
                    No Team Name Given
                @endif                    
                </span>
            </label>
        </div>

        @endforeach
    </div>
</div>