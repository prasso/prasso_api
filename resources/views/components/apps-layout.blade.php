<div class="col-span-6">
    <div class="max-w-xl text-sm">
        @foreach($apps as $app)
        <x-jet-responsive-nav-link class="sm-btn-blue"
                    href="{{ route('apps.edit',['teamid' => $selected_team, 'appid' => $app['id']])   }}">
                        
                {{ __('Edit App') }}
            </x-jet-responsive-nav-link>

        <div class="flex my-4">
            <input type="radio" class="form-radio" name="appradio" value="{{$app['id']}}" @if ($selected_app == $app['id'] ) checked @endif >
                
            <label class="items-left"> 
            <span class="mx-4">  
                @if( !empty($app['appicon']))
                   <img src="{{ $app['appicon'] }}" alt="app icon" class="my-0" />
                @else
                    No appicon Given                     
                @endif
                </span>
            </label>

            <label class="items-left">
                <span class="ml-2">  
                @if( !empty($app['app_name']))
                    {{ $app['app_name'] }} 
                @else
                    No app_name Given                     
                @endif
                </span>
            </label>

        </div>

        @endforeach
    </div>
</div>
