<x-app-layout>
<x-slot name="title">Edit Site Page Json Data</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Site Page: '.$sitePage['section']) }}
        </h2>
        <x-dropdown-link href="{{ route('site-page.list',$site->id)  }}">
                    {{ __('Return to site editor') }}
                </x-responsive-nav-link>
    </x-slot>

    <div class="container mx-auto py-8 px-4 sm:px-6 lg:px-8 w-3/4">
    @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        {{ session('success') }}
    </div>
    @endif
    <form action="{{ route('sitepages.updateSitePageJsonData', [$siteId, $sitePageDataid]) }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="">
                        @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            {{ session('success') }}
                        </div>
                        @endif
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($jsonData as $key => $value)
                            <div class="mb-4">
                                <label for="json_data_{{ $key }}" class="block text-gray-700 text-sm font-bold mb-2">Item {{ $loop->index + 1 }}</label>
                                <textarea name="json_data[{{ $key }}]" id="json_data_{{ $key }}" class="shadow appearance-none border rounded h-32 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline w-full" >{{ $value }}</textarea>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if (!session('message'))
    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
            <button type="submit" class="teambutton inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                Update
            </button>
        </span>
        <span class="mt-3 flex w-full rounded-md shadow-sm sm:mt-0 sm:w-auto">
            <a href="{{ route('site-page.list', $site->id) }}" class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                Cancel
            </a>
        </span>
    </div>
@endif
            </form>
            </div>
</x-app-layout>

