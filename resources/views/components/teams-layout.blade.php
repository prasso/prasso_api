<div class="col-span-6">
    <div class="max-w-xl text-sm">

        @foreach($teams as $team)

        <x-jet-responsive-nav-link class="sm-btn-blue hover:bg-blue-900 focus:bg-blue-900"
                href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" >
                    {{ __('Team Settings') }}
                </x-jet-responsive-nav-link>
         <div class="flex my-4">
            <label class="flex items-center">
                <input type="radio" class="form-radio" name="teamradio" value="{{$team['id']}}" @if($team['id'] == $selected_team) checked @endif >
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