          <!-- if this is show_modal, then show the modal -->
          @if( !isset($show_modal) || $show_modal)
            <div class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400" x-data>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>?
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" role="dialog" aria-modal="true" aria-labelledby="modal-headline"
        @click.away="$wire.closeModal()">
@else
<div><div>
        <div class="align-bottom bg-white rounded-lg text-left transition-all sm:my-8 sm:align-middle " >
@endif
    <div class="border-t border-gray-200"></div>
    <div class="block px-4 py-2 text-lg font-semibold text-gray-600">
        {{ __('Add to Image Library') }}
        <div class="flex">

        </div>
    </div>

        @include('partials._image-upload-styles')

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="px-4 py-2 ml-2">
                        <a class="cursor-pointer rounded-xl transition duration-500 ease-in-out transform rounded-lg shadow-xl" href="{{ route('image.library') }}" title="Image Library"><i alt="image library" class="material-icons">photo_library</i></a>
                    </div>
                @include('partials._image-upload')

                <div class="block px-4 py-2 text-lg font-semibold text-gray-600">
    <!-- a link to this user's app -->
    <x-responsive-nav-link href="{{ route('apps.show', Auth::user()->current_team_id) }}">
        <div class="flex items-center">
            <!-- Mobile phone icon -->
            <svg class="h-5 w-5 text-gray-600 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 2.75a2 2 0 00-2 2v14.5a2 2 0 002 2h10a2 2 0 002-2V4.75a2 2 0 00-2-2H7zM7 2.75h10M11 18h2M12 10.25h.01"/>
            </svg>
            {{ __('Edit Mobile App') }}
        </div>
    </x-responsive-nav-link>
    <!-- Link to the ErpProduct Editor -->
    <x-responsive-nav-link href="{{ url('/admin/erp-products') }}">
    <div class="flex items-center">
        <!-- Inventory icon (you can replace this with any icon you want) -->
        <svg class="h-5 w-5 text-gray-600 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3.5a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2v-14zM10 8h4M10 12h4"/>
        </svg>
        {{ __('Edit Erp Products') }}
    </div>
</x-responsive-nav-link>

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

    @push('scripts')
    <script src="{{ asset('js/image-upload.js') }}"></script>
@endpush
</div>