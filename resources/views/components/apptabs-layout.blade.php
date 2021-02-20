<div class="col-span-6">

    <div class="max-w-xl text-sm text-gray-600" >
    <span class="mx-1 float-right ">  <a href="{{ route('apps.add-tab',['teamid' => $selected_team, 'appid' => $selected_app])   }}">
                <i class="material-icons md-36">playlist_add</i>
            </a>
    </div></span>
    <div class="max-w-xl text-sm text-gray-600" >
        @foreach($apptabs as $tab)
        <div>
        
            <span class="mx-1 float-right ">  
                <a   href="{{ route('apps.delete-tab',['teamid' => $selected_team,'appid' => $selected_app,'tabid' => $tab['id']])   }}">
                    <i class="material-icons md-36">delete_forever</i>
                </a>
                <a 
                    href="{{ route('apps.edit-tab',['teamid' => $selected_team, 'appid' => $selected_app,'tabid' => $tab['id']])   }}">
                    <i class="material-icons md-36">mode_edit</i>
                </a>
            </span>
            <div class="flex my-4" >
            <label class="items-left"> 
                <span class="mx-4">  
                    @if( !empty($tab['icon']))
                    <i class="material-icons md-36">{{ $tab['icon'] }}</i>
                    @else
                        No icon Given                     
                    @endif
                </span>
            </label>
            <label class="items-left">
                 <span class="ml-2">  
                @if( !empty($tab['label']))
                    {{ $tab['label'] }} 
                @else
                    No Label Given                     
                @endif
                </span>
            </label>
           
            <label class=" items-left">
                <span class="ml-2">  
                @if( !empty($tab['page_title']))
                    {{ $tab['page_title'] }} 
                @else
                    No page_title Given                     
                @endif
                </span>
            </label>

            <label class=" items-left">
                <span class="ml-2">  
                @if( !empty($tab['page_url']))
                 <a href="{{ $tab['page_url'] }}" target="_blank">   {{ $tab['page_url'] }} </a>
                @else
                    No page_url Given                     
                @endif
                </span>
            </label>
            <label class=" items-center">
                <span class="ml-2">  
                @if( !empty($tab['sort_order']))
                    {{ $tab['sort_order'] }} 
                @else
                    No sort_order Given                     
                @endif
                </span>
            </label>
        </div>
        </div>
        @endforeach
    </div>
</div>
