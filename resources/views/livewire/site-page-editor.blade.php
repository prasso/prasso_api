<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Manage Site Pages for {{$site_name}}
    </h2>
</x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">

            <button wire:click="create()" class="teambutton text-white  font-bold py-2 px-4 rounded my-3">Create New Site Page</button>
            @if($isOpen)
                @include('sitepage.create-or-edit', ['team_selection' => $team_selection])
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
                        <button wire:click="edit({{ $sitePage->id }})" class="py-2 px-4 rounded"> <i class="material-icons md-36">mode_edit</i></button>
                        <a href="/visual-editor/{{ $sitePage->id }}" class="py-2 px-4 rounded"><i class="material-icons md-36">format_color_fill</i></a>
                        <button wire:click="delete({{ $sitePage->id }})" class="py-2 px-4 rounded"><i class="material-icons md-36">delete_forever</i></button>
                        <a href="{{ $https_host }}/page/{{ $sitePage->section }}" target="new" class="py-2 px-4 rounded"><i class="material-icons md-36">preview</i></a>
                        <a href="/sitepages/{{ $siteid }}/{{ $sitePage->id }}/read-tsv-into-site-page-data" data-tooltip="Import Data"><i class="material-icons md-36">file_upload</i></a>
                    </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
