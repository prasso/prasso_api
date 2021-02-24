<div class="col-span-6">
    <div class="max-w-xl text-sm bg-gray">
        @foreach($apps as $app)
        <div class="grid my-4 grid-cols-3 mb-2">
            <div>
                <label class="items-left"> 
                    @if( !empty($app['appicon']))
                    <img src="{{ $app['appicon'] }}" alt="app icon" class="my-0" />
                    @else
                        No appicon Given                     
                    @endif
                </label>
            </div>
            <div>
                <label class="float-left">
                    @if( !empty($app['app_name']))
                        {{ $app['app_name'] }} 
                    @else
                        No app_name Given                     
                    @endif
                </label>
            </div>
            <div class='mr-0'>
            <label class="float-right">
                <x-jet-responsive-nav-link class="sm-btn hover:bg-white-900 focus:bg-white-900"
                href="{{ route('apps.edit',['teamid' => $selected_team, 'appid' => $app['id']])   }}">
                        <i class="material-icons md-36">mode_edit</i>
                </x-jet-responsive-nav-link>
            </label>
                <label class="float-right">
                <x-jet-responsive-nav-link class="sm-btn hover:bg-white-900 focus:bg-white-900"
                title="active mobile app"
                href="{{ route('apps.activate',['teamid' => $selected_team, 'appid' => $app['id']])   }}">
                        <i class="material-icons md-36">notifications_active</i>
                </x-jet-responsive-nav-link>
                </label>
            </div>
        </div>

        @endforeach
    </div>
</div>
