
<div class="col-span-6">
    <div class="text-sm bg-gray">
        @foreach($apps as $app)
        @if (Auth::user()->id == 1 || $app['app_name'] == 'Prasso')

        {{ $activeAppId=$app['id'] }}
        <div class="grid my-4 grid-cols-3 mb-2 @if(isset($activeAppId) && $app['id'] == $activeAppId) bg-blue-50 @endif">
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
                <label class="block mx-10 my-5 text-xs font-bold tracking-widest uppercase title-font">
                    @if( !empty($app['app_name']))
                        {{ $app['app_name'] }} 
                    @else
                        No app_name Given                     
                    @endif
                </label>
            </div>
            <div class='mr-0'>
            <label class="float-right">
               <x-responsive-nav-link  onclick="return window.confirm('Are you sure you want to delete this app?')" class="sm-btn hover:bg-white-900 focus:bg-white-900"
                title="active mobile app"
                href="{{ route('apps.delete',['teamid' => $app['team_id'], 'appid' => $app['id']])   }}">
                        <i class="material-icons md-36">delete_forever</i>
                </x-responsive-nav-link>
               </label>
            <label class="float-right">
                <x-responsive-nav-link class="sm-btn hover:bg-white-900 focus:bg-white-900"
                href="{{ route('apps.edit',['teamid' => $app['team_id'], 'appid' => $app['id']])   }}">
                        <i class="material-icons md-36">mode_edit</i>
                </x-responsive-nav-link>

            </label>
            <label class="float-right">

            @if(isset($activeAppId) && $app['id'] != $activeAppId)
                <x-responsive-nav-link class="sm-btn hover:bg-white-900 focus:bg-white-900"
                title="active mobile app"
                href="{{ route('apps.activate',['teamid' => $app['team_id'], 'appid' => $app['id']])   }}">
                        <i class="material-icons md-36">notifications_active</i>
                </x-responsive-nav-link>
                @else
                    <i class="material-icons md-36 mr-4 mt-2 text-blue-500">notifications_active</i>
            @endif
                </label>
             
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>

