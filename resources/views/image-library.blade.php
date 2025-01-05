<x-app-layout :site="$site ?? null">
    <x-slot name="title">{{ isset($site) ? $site->site_name . ' - ' : '' }}Image Library</x-slot>
   
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ isset($site) ? $site->site_name . ' - ' : '' }}Image Library
            </h1>
            <button onclick="window.history.back()" class="teambutton teambutton-hover rounded px-6 py-3 ml-4">
                <i class="fas fa-arrow-left"></i> Return to previous page
            </button>
        </div>
    </x-slot>
    @include('partials._image-upload-styles')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Upload Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    @include('partials._image-upload', ['site_id' => $site->id])
                </div>
            </div>

            <!-- Alert Messages -->
            <div id="alertContainer" class="mb-6 hidden">
                <div id="alertContent" class="p-4 rounded-md">
                </div>
            </div>

            <!-- Resize Dialog -->
            <div id="resizeDialog" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden flex items-center justify-center">
                <div class="bg-white p-6 rounded-lg shadow-xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Resize Image?</h3>
                    <p id="resizeMessage" class="text-sm text-gray-500 mb-4"></p>
                    <div class="flex justify-end space-x-4">
                        <button onclick="confirmResize()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                            Yes, resize it
                        </button>
                        <button onclick="cancelResize()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                            No, I'll upload a smaller image
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-center mx-6">
                @if(session('warning') && session('show_resize_options'))
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                        <p>{{ session('warning') }}</p>
                        <div class="mt-3">
                            <form action="{{ route('images.confirm-resize') }}" method="POST">
                                @csrf
                                <button type="submit" class="teambutton teambutton-hover text-white font-bold py-2 px-4 rounded mr-2">
                                    Yes, resize it
                                </button>
                                <a href="{{ route('image.library') }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded inline-block">
                                    No, I'll upload a smaller image
                                </a>
                            </form>
                        </div>
                    </div>
                @endif
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
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/image-upload.js') }}"></script>
    @endpush
</x-app-layout>