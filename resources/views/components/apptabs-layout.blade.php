<div class="col-span-6">
    <div class="max-w-xl text-sm text-gray-600" >
        @foreach($apptabs as $tab)
        <div x-data="{ isShowing: false }">
            <x-jet-responsive-nav-link x-show="isShowing" class="sm-btn-blue hover:bg-blue-900 focus:bg-blue-900"
                href="{{ route('apps.edit-tab',['teamid' => $selected_team, 'appid' => $selected_app,'tabid' => $tab['id']])   }}">
                
                {{ __('Edit tab') }}
            </x-jet-responsive-nav-link>
            <div class="flex my-4" >
            <label class="items-left"> 
            <input type="radio" x-bind:checked="isShowing"  @click.away="isShowing = false" x-model="isShowing" class="form-radio" name="tabradio" value="{{$tab['id']}}">
               
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
