<div class="col-span-6">
    <div class="max-w-xl text-sm text-gray-600">
        @foreach($apptabs as $tab)
        <div class="flex">
            <label class="items-center">
                <input type="radio" class="form-radio" name="tabradio" value="{{$tab['id']}}">
                <span class="ml-2">  
                @if( !empty($tab['label']))
                    {{ $tab['label'] }} 
                @else
                    No Label Given                     
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
        @endforeach
    </div>
</div>
