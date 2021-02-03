<div class="col-span-6">
    <div class="text-sm">
 
        @foreach($apps as $app)
        <div class="flex">
            <label class="items-center">
                <input type="radio" class="form-radio" name="appradio" value="{{$app['id']}}" @if ($selected_app == $app['id'] ) checked @endif >
                <span class="ml-2">  
                @if( !empty($app['appicon']))
                    {{ $app['appicon'] }} 
                @else
                    No appicon Given                     
                @endif
                </span>
            </label>

            <label class=" items-center">
                <span class="ml-2">  
                @if( !empty($app['app_name']))
                    {{ $app['app_name'] }} 
                @else
                    No app_name Given                     
                @endif
                </span>
            </label>

            <label class=" items-center">
                <span class="ml-2">  
                @if( !empty($app['page_title']))
                    {{ $app['page_title'] }} 
                @else
                    No page_title Given                     
                @endif
                </span>
            </label>

            <label class=" items-center">
                <span class="ml-2">  
                @if( !empty($app['page_url']))
                    {{ $app['page_url'] }} 
                @else
                    No page_url Given                     
                @endif
                </span>
            </label>

            <label class=" items-center">
                <span class="ml-2">  
                @if( !empty($app['sort_order']))
                    {{ $app['sort_order'] }} 
                @else
                    No sort_order Given                     
                @endif
                </span>
            </label>
        </div>

        @endforeach
    </div>
</div>
