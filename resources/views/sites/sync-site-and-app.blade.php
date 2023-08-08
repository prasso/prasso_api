

<div class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
            <!-- This element is to trick the browser into centering the modal contents. -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>?
    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
<div class="text-lg text-center font-bold py-2">Sync Pages to App</div>
    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            {!! implode('', $errors->all('<div>:message</div>')) !!}
        </div>
        @endif
        <div class="mb-4">
        <form wire:submit.prevent="syncAppToSite">
            @foreach ($sitePages as $page)
                <div>
                    <input type="checkbox" wire:model="selectedPages" value="{{ $page->id }}">
                    <label>{{ $page->title }}</label>
                </div>
            @endforeach

            <button class="teambutton rounded" wire:click"syncAppToSite">Sync</button>
            <button type="button" wire:click="hideSyncDialog">Cancel</button>
        </form>
    </div>
</div>
            </div></div>