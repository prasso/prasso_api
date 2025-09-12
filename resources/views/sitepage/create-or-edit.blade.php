<div class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div x-data="{ template_selection_made: {{ $masterpage ? 'true' : 'false' }}, dataTemplateSelectionMade: {{ $template ? 'true' : 'false' }} }" 
             x-init="template_selection_made = {{ ($masterpage || $template || $type == 1) ? 'true' : 'false' }};" 
             @click.away="$wire.closeModal()" 
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle" 
             role="dialog" aria-modal="true" aria-labelledby="modal-headline">
            <form> <input type="hidden" wire:model="fk_site_id" />
            @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                {!! implode('', $errors->all('<div>:message</div>')) !!}
            </div>
            @endif
            <input type="hidden" wire:model="sitePage_id" />                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="">
                        <div x-data="{}" x-init="$refs.sectionInput.focus()" class="mb-4">
                            <label for="sectionInput" class="block text-gray-700 text-sm font-bold mb-2">Unique Name:</label>
                            <input type="text"  x-ref="sectionInput" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="sectionInput" placeholder="Enter Unique Name" wire:model="section">
                            @error('section') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="titleInput" class="block text-gray-700 text-sm font-bold mb-2">Title: (this is in the top too)</label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="titleInput" placeholder="Enter Title" wire:model="title">
                            @error('title') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="typeInput" class="block text-gray-700 text-sm font-bold mb-2">Page Type:</label>
                            <select wire:model="type" name="typeInput" id="typeInput" class="mt-1 block w-full border-2 border-indigo-600/100 p-2">
                                <option value="1">HTML Content</option>
                                <option value="2">S3 File</option>
                                <option value="3">External URL</option>
                            </select>
                            @error('type') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4" x-show="$wire.type == 3">
                            <label for="externalUrlInput" class="block text-gray-700 text-sm font-bold mb-2">External URL:</label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="externalUrlInput" placeholder="Enter External URL" wire:model="external_url">
                            @error('external_url') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4" x-show="$wire.type == 2">
                            <label for="s3FileInput" class="block text-gray-700 text-sm font-bold mb-2">S3 File Content:</label>
                            <div class="space-y-2">
                                @if($s3_content)
                                    <div class="bg-gray-100 p-4 rounded">
                                        <h3 class="font-bold mb-2">Current Content Preview:</h3>
                                        <pre class="text-sm overflow-auto max-h-40">{{ Str::limit($s3_content, 500) }}</pre>
                                    </div>
                                @endif
                                <input type="file" class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                                    file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100" 
                                    id="s3FileInput" 
                                    wire:model="s3_file"
                                    accept=".html,.htm">
                                <p class="text-sm text-gray-500 mt-1">Upload HTML file to store in S3. Path will be: {{ App\Models\Site::find($fk_site_id)?->site_name ?? 'sitename' }}/pages/{{ $section }}_{{$sitePage_id ?? '0'}}.html</p>
                                @error('s3_file') <span class="text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="masterpageInput" class="block text-gray-700 text-sm font-bold mb-2">Wrapper: </label>
                            <select wire:model="masterpage" name="masterpageInput" id="masterpageInput" class="mt-1 block w-full border-2 border-indigo-600/100 p-2"  @change="template_selection_made = $event.target.value !== ''">
                                <option value="">No Master Page</option>
                                @foreach($masterpage_recs as $g)
                                    <option value="{{$g->pagename}}" @if($masterpage==$g->pagename) selected="selected" @endif >{{$g->title}}</option>
                                @endforeach
                            </select>
                            @error('masterpage') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div x-data="{ template_info_open: false}" class="mb-4">
                            <i x-show="true" wire:ignore  x-on:click="template_info_open = !template_info_open"  class="float-right material-icons text-gray-600">info</i><label for="templateInput" class="block text-gray-700 text-sm font-bold mb-2">Template : </label>
                                    <div x-show="template_info_open" ><b>Template or custom. if template then the template name is in a field of sitepage and the html value is used to fill in placeholders in the template
                                        if custom then html=html</b>
                                    </div>
                            <select wire:model="template" name="templateInput"
                            @change="dataTemplateSelectionMade = $event.target.value !== ''"
                             id="templateInput" class="mt-1 block w-full border-2 border-indigo-600/100 p-2" >
                                <option value="" >No Template</option>
                            @foreach($template_recs as $g)
                                <option value="{{$g->templatename}}" @if($template==$g->templatename) selected="selected" @endif >{{$g->title}}</option>
                            @endforeach
                            </select>
                            @error('template') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div x-show="(template_selection_made || dataTemplateSelectionMade || $wire.type == 1) && $wire.type != 3" class="mb-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" >
                            <label for="descriptionInput" class="min-h-[10%] block text-gray-700 text-sm font-bold mb-2">Description: (enter html if no template is selected)</label>
                            <textarea rows="35" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="descriptionInput" wire:model.defer="description" placeholder="Enter Description"></textarea>
                            @error('description') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div x-show="(template_selection_made || dataTemplateSelectionMade) && $wire.type != 3" class="mb-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" >
                            <label for="styleInput" class="min-h-[10%] block text-gray-700 text-sm font-bold mb-2">Style: (css shown between &lt;style&gt; tags)</label>
                            <textarea rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="styleInput" wire:model.defer="style" placeholder="Enter style"></textarea>
                            @error('style') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div x-show="(template_selection_made || dataTemplateSelectionMade) && $wire.type != 3" class="mb-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" >
                            <label for="whereValue" class="block text-gray-700 text-sm font-bold mb-2">Where Value (if needed for where clause will replace ??? example: a site page id)</label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="whereValue" placeholder="Enter Where Value if used" wire:model="where_value">
                            @error('where_value') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div x-show="$wire.type == 3" class="mb-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" >
                            <label for="urlInput" class="block text-gray-700 text-sm font-bold mb-2">Url: (if this is an outside page )</label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="urlInput" placeholder="Enter Url" wire:model="url">
                            @error('url') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="login_requiredInput" class="block text-gray-700 text-sm font-bold mb-2">Requires Authentication: </label>
                            <input type="radio"  name="login_requiredInput" wire:model.defer="login_required" value="1"  />Yes
                            <input type="radio"  name="login_requiredInput" wire:model.defer="login_required" value="0" />No
                            @error('login_required') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="user_levelInput" class="block text-gray-700 text-sm font-bold mb-2">Requires Admin Level: </label>
                            <input type="radio"  name="user_levelInput"  wire:model.defer="user_level" value="1"  />Yes
                            <input type="radio"  name="user_levelInput"  wire:model.defer="user_level" value="0" />No
                            @error('user_level') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="page_notifications_onInput" class="block text-gray-700 text-sm font-bold mb-2">Notify Admin on Change</label>
                            <input type="radio"  name="page_notifications_onInput" wire:model.defer="page_notifications_on" value="1"  />Yes
                            <input type="radio"  name="page_notifications_onInput" wire:model.defer="page_notifications_on" value="0" />No
                            @error('page_notifications_on') <span class="text-red-500">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                        <button wire:click.prevent="store()"  wire:loading.remove wire:processing.attr="disabled"  type="button" class="teambutton inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                        Save
                        </button>
                    </span>
                    <span class="mt-3 flex w-full rounded-md shadow-sm sm:mt-0 sm:w-auto">
                        <button wire:click="closeModal()" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                            Cancel
                        </button>
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>