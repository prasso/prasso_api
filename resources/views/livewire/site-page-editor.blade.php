<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Manage Site Pages
    </h2>
</x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
            @if (session()->has('message'))
                <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md my-3" role="alert">
                  <div class="flex">
                    <div>
                      <p class="text-sm">{{ session('message') }}</p>
                    </div>
                  </div>
                </div>
            @endif
            <button wire:click="create()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded my-3">Create New Site Page</button>
            @if($isOpen)
                @include('sitepage.create')
            @endif          
            @if($isVisualEditorOpen)
                @include('sitepage.visual-editor')
            @endif
            <table class="table-fixed w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 w-20">No.</th>
                        <th class="px-4 py-2">Section</th>
                        <th class="px-4 py-2">Title</th>
                        <th class="px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sitePages as $sitePage)
                    <tr>
                        <td class="border px-4 py-2">{{ $sitePage->id }}</td>
                        <td class="border px-4 py-2">{{ $sitePage->section }}</td>
                        <td class="border px-4 py-2">{{ $sitePage->title }}</td>
                        <td class="border px-4 py-2">
                        <button wire:click="edit({{ $sitePage->id }})" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</button>
                        <a href="/visual-editor/{{ $sitePage->id }}" class="bg-blue-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Visualize</a>
                        <!-- <button wire:click="visualEditor({{ $sitePage->id }})" class="bg-blue-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Visualize</button>-->
                        <button wire:click="delete({{ $sitePage->id }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
