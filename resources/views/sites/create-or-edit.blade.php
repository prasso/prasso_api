        <!-- if this is show_modal, then show the modal -->
        @if( !isset($show_modal) || $show_modal)
            <div class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
            <!-- This element is to trick the browser into centering the modal contents. -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>?
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
    @else
    <div><div>
            <div class="align-bottom bg-white rounded-lg text-left transition-all sm:my-8 sm:align-middle " >
        @endif
        <div class="border-t border-gray-200"></div>
            <div class="block px-4 py-2 text-lg font-semibold text-gray-600">
                {{ __('Add to Image Library') }}
                <div class="flex">
                    <div class="px-4 py-2 border-r border-gray-200" x-data="{fileSelected: false}">
                        <form action="{{ route('images.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" type="file" id="fileInput" name="image" x-ref="fileInput"
                                @change="fileSelected = $refs.fileInput.files.length > 0">
                            <button :disabled="!fileSelected" class="teambutton cursor-pointer mt-2 border p-2 rounded-xl px-2 py-1  text-sm  text-white transition duration-500 ease-in-out transform rounded-lg shadow-xl" type="submit">Upload</button>
                        </form>
                    </div>
                    <div class="px-4 py-2 ml-2">
                        <a class="cursor-pointer rounded-xl transition duration-500 ease-in-out transform rounded-lg shadow-xl" href="{{ route('image.library') }}" title="Image Library"><i alt="image library" class="material-icons">photo_library</i></a>
                    </div>
                </div>
            </div>
            <form> 
                
                <input type="hidden" wire:model="site_id" />
                          
                @include('sites.site-inputs', ['team_selection' => $team_selection])
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                        <button wire:click.prevent="store()" type="button" class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">
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