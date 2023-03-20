<div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
    <form wire:submit.prevent="save">
        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            {!! implode('', $errors->all('<div>:message</div>')) !!}
        </div>
        @endif
        <input type="hidden" wire:model="media_id">

        <div class="mb-4">
            <label  class="block text-gray-700 text-sm font-bold mb-2" for="media_title">Media Title</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="media_title" wire:model.lazy="media_title">
            @error('media_title') <span class="text-red-500">{{ $message }}</span>@enderror
</div>
        <div class="mb-4">
            <label  class="block text-gray-700 text-sm font-bold mb-2" for="media_description">Media Description</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="media_description" wire:model.lazy="media_description">
            @error('media_description') <span class="text-red-500">{{ $message }}</span>@enderror

        <div class="mb-4">
            <label  class="block text-gray-700 text-sm font-bold mb-2" for="media_date">Media Date</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="media_date" wire:model.lazy="media_date">
            @error('media_date') <span class="text-red-500">{{ $message }}</span>@enderror
</div>

        <div class="mb-4">
            <label  class="block text-gray-700 text-sm font-bold mb-2" for="s3media_url">S3 Media URL</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="s3media_url" wire:model.lazy="s3media_url">
            @error('s3media_url') <span class="text-red-500">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label  class="block text-gray-700 text-sm font-bold mb-2" for="thumb_url">Thumbnail URL</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="thumb_url" wire:model.lazy="thumb_url">
            @error('thumb_url') <span class="text-red-500">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label  class="block text-gray-700 text-sm font-bold mb-2" for="video_duration">Video Duration</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="video_duration" wire:model.lazy="video_duration">
            @error('video_duration') <span class="text-red-500">{{ $message }}</span>@enderror
        </div>
        <div class="mb-4">
            <label  class="block text-gray-700 text-sm font-bold mb-2" for="dimensions">Dimensions</label>
            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="dimensions" wire:model.lazy="dimensions">
            @error('dimensions') <span class="text-red-500">{{ $message }}</span>@enderror
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                <button wire:click.prevent="store()" type="button" class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                Save
                </button>
            </span>
            <span class="gray-200 flex mr-10 text-sm shadow-sm sm:mt-0 sm:w-auto">
                <a class="mt-3 text-gray-700 " href="javascript:window.history.back()" @click="history.back()">Cancel</a>
            </span>
        </div>
    </form>

</div>
