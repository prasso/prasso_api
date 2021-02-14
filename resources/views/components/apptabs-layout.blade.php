<div class="col-span-6">
    <div class="max-w-xl text-sm text-gray-600" >
        @foreach($apptabs as $tab)
        <div x-data="{ isShowing: false }">
            <x-jet-responsive-nav-link x-show="isShowing" class="sm-btn-blue hover:bg-blue-900 focus:bg-blue-900"
                href="{{ route('apps.edit-tab',['tabid' => $tab['id'],'teamid' => $selected_team, 'appid' => $selected_app])   }}">
                        
                {{ __('Edit tab') }}
            </x-jet-responsive-nav-link>
            <div class="flex my-4" >
            <label class="items-center">
                <input type="radio" x-bind:checked="isShowing"  @click.away="isShowing = false" x-model="isShowing" class="form-radio" name="tabradio" value="{{$tab['id']}}">
                <span class="ml-2">  
                @if( !empty($tab['label']))
                    {{ $tab['label'] }} 
                @else
                    No Label Given                     
                @endif
                </span>
            </label>
            <label class="items-left"> 
                <span class="mx-4">  
                    @if( !empty($tab['icon']))
                    <img src="{{ $tab['icon'] }}" alt=" icon" class="my-0" />
                    @else
                        No icon Given                     
                    @endif
                </span>
            </label>
            <label class=" items-center">
                <span class="ml-2">  
                @if( !empty($tab['page_title']))
                    {{ $tab['page_title'] }} 
                @else
                    No page_title Given                     
                @endif
                </span>
            </label>

            <label class=" items-center">
                <span class="ml-2">  
                @if( !empty($tab['page_url']))
                    {{ $tab['page_url'] }} 
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
