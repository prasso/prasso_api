<x-app-layout>
    <x-slot name="title">Edit Site Page Json Data</x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Site Page: '.$sitePage['section']) }}
        </h2>
        <x-dropdown-link href="{{ route('site-page.list', $site->id) }}">
            {{ __('Return to site editor') }}
        </x-dropdown-link>
    </x-slot>

    <x-form-section submit="updateJsonData">
        <x-slot name="title">
            {{ __('Edit Site Page JSON Data') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Modify the data entries for the site page.') }}
        </x-slot>

        <x-slot name="form">
            @if (session('success'))
                <div class="p-5 text-green-400 bg-green-50">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="col-span-6 alert alert-danger text-sm text-red-600 mt-2">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- JSON Data Inputs -->
            @foreach ($jsonData as $key => $value)
                <div class="col-span-6 mt-4">
                    <x-label for="json_data_{{ $key }}" :value="__('Item ' . ($loop->index + 1))" />
                    <x-input type="text" name="json_data[{{ $key }}]" id="json_data_{{ $key }}" class="mt-1 block w-full" value="{{ $value }}" />
                </div>
            @endforeach
        </x-slot>

        <x-slot name="actions">
            <x-action-message class="mr-3" on="saved">
                {{ __('Saved.') }}
            </x-action-message>

            <x-button>
                {{ __('Update') }}
            </x-button>
        </x-slot>
    </x-form-section>
</x-app-layout>
