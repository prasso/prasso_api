

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">

            <button wire:click="create()" class="teambutton text-white font-bold py-2 px-4 rounded my-3">Create New Site</button>
            @if($isOpen)
                @include('sites.create-or-edit')
            @endif          
            @if($showSyncDialog)
                @include('sites.sync-site-and-app')
            @endif
            <table class="table-fixed w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 w-20">No.</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Web Address</th>
                        <th class="px-4 py-2">Logo</th>
                        <th class="px-4 py-2 w-25">Actions Available</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sites as $site)
                    <tr>
                        <td class="border px-4 py-2">{{ $site->id }}</td>
                        <td class="border px-4 py-2 overflow-hidden">{{ $site->site_name }}</td>
                        <td class="border px-4 py-2 overflow-hidden">{{ $site->host }}</td>
                        <td class="border px-4 py-2"><img src="{{ $site->logo_image }}" class="block h-9 w-auto" /></td>
                        <td class="border px-2 py-2">
                        <button wire:click="edit({{ $site->id }})" class="py-2 px-3 "> <i class="material-icons md-36">mode_edit</i></button>
                        <a href="/sitepages/{{ $site->id }}" ><i class="material-icons md-36 text-black ">list</i></a>
                        <button onclick="return window.confirm('Are you sure you want to delete this site?')" wire:click="delete({{ $site->id }})" class="ml-3 py-2 px-3 rounded"><i class="material-icons md-36">delete_forever</i></button>
                       
                        <button wire:click="showTheSyncDialog({{ $site->id }})" class="py-2 px-3"><i class="material-icons md-36">sync</i></button>
                        
                        @if ($site->livestream_settings != null)
                        <a href="/site/{{ $site->id }}/livestream-mtce" class="py-2 px-3"><i class="material-icons md-36">live_tv</i></a>
                        @endif
                    </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
            
        </div>
    </div>




