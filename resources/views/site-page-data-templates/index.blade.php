
<x-app-layout>

<x-slot name="title">View and Edit Site Page Data Templates</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Site Page Data Templates') }}
        </h2>
        <div class="flex bg-gray-100">
        <x-jet-responsive-nav-link href="{{ route('site-page-data-templates.create') }}" :active="request()->routeIs('site-page-data-templates.create')">
            {{ __('Create a new data template') }}
        </x-jet-responsive-nav-link>
        </div>
    </x-slot>
    <div>
        
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        @if (session()->has('message'))
        <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md my-3 mb-0" role="alert">
            <div>
                <p class="m-auto text-center text-sm">{{ session('message') }}</p>
            </div>
        </div>
        @endif
        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            {!! implode('', $errors->all('<div>:message</div>')) !!}
        </div>
        @endif
        <table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($templates as $template)
            <tr >
                <td>{{ $template->templatename }}</td>
                <td>{{ $template->description }}</td>
                <td><div class="flex">
                    <a class=" py-2 px-3 " href="/site-page-data-templates/{{ $template->id }}/edit"><i class="material-icons md-36">mode_edit</i></a>
   
                    <form method="POST" action="/site-page-data-templates/{{ $template->id }}">
                        @csrf
                        @method('DELETE')

                        <button  class="ml-3 py-2 px-3 rounded" onclick="return window.confirm('Are you sure you want to delete this template?')"  type="submit"><i class="material-icons md-36">delete_forever</i></button>
                    </form>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
        </div>
    </div>




</x-app-layout>
  