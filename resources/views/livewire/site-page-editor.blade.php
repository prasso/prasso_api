<div>
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
        <div class="mb-4">
            <button wire:click="create()" class="teambutton text-white font-bold py-2 px-4 rounded my-3" title="Create New Site Page">Create New Site Page</button>
        </div>
        
        <x-dialog-modal wire:model="isOpen" maxWidth="2xl">
            <x-slot name="title">
                {{ __('Site Page Editor') }}
            </x-slot>
            
            <x-slot name="content">
                <!-- Regular Editor Only -->
                <div class="min-h-[60vh]">
                    @include('sitepage.create-or-edit', ['team_selection' => $team_selection, 'embedded' => true])
                </div>
            </x-slot>
            
            <x-slot name="footer">
                <x-secondary-button wire:click="$set('isOpen', false)" wire:loading.attr="disabled">
                    {{ __('Close') }}
                </x-secondary-button>
            </x-slot>
        </x-dialog-modal>
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
                        <button wire:click="edit({{ $sitePage->id }})" class="py-2  px-2" title="Edit Site Page"><i class="material-icons md-36">mode_edit</i></button>
                        <a href="/sitepages/{{ $siteid }}/{{ $sitePage->id }}/bedrock-html-editor" class="py-2  px-2" title="AI HTML Editor"><i class="material-icons md-36">smart_toy</i></a>
                        <a href="/sitepages/{{ $siteid }}/{{ $sitePage->id }}/edit-site-page-json-data" class="py-2  px-2" title="Edit Site Page Json Data"><i class="material-icons md-36">edit_note</i></a>
                        <a href="/sitepages/{{ $siteid }}/{{ $sitePage->id }}/read-tsv-into-site-page-data" title="Import Data" class="py-2  px-2"><i class="material-icons md-36">file_upload</i></a>

                        <button wire:click="delete({{ $sitePage->id }})" class="py-2  px-2" title="Delete Site Page"><i class="material-icons md-36">delete_forever</i></button>
                        <a href="{{ $https_host }}/page/{{ $sitePage->section }}" target="new" class="py-2" title="Preview Site Page"><i class="material-icons md-36">preview</i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>