
<x-app-layout>
    <x-slot name="title">Image Library</x-slot>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            Image Library
        </h1>
    </x-slot>

    <div class="flex justify-center mx-6">
        @if ($images->count() === 0)
            <div class="text-center bg-gray-50 border-2 border-indigo-600/100">
                <div class="font-sans text-lg font-semibold text-gray-600">
                    No images found.
                </div>
            </div>
        @else
            <div class="grid grid-cols-3 gap-4 justify-center">
                @foreach ($images as $image)
                    <div class="flex items-center justify-center max-h-200">
                        <img src="{{ config('constants.CLOUDFRONT_ASSET_URL').$image->path }}" alt="{{ $image->path }}">
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>