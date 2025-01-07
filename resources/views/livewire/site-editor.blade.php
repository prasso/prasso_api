    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">

            <button wire:click="create()" class="teambutton text-white font-bold py-2 px-4 rounded my-3">Create New Site</button>
            @if($isOpen)
                @include('sites.create-or-edit', ['team_selection' => $team_selection])
            @endif          
            @if($showSyncDialog)
                @include('sites.sync-site-and-app')
            @endif
            <table class="table-fixed w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 w-15">No.</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Web Address</th>
                        <th class="px-4 py-2 w-15">Logo</th>
                        <th class="px-4 py-2 w-30">Actions Available</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sites as $site)
                    <tr>
                        <td class="border px-2 py-2">{{ $site->id }}</td>
                        <td class="border px-2 py-2 overflow-hidden">{{ $site->site_name }}</td>
                        <td class="border px-2 py-2 overflow-hidden">{{ $site->host }}</td>
                        <td class="border px-2 py-2"><img src="{{ $site->logo_image }}" class="block h-9 " /></td>
                        <td class="border px-2 py-2">
                        <button wire:click="edit({{ $site->id }})" class="py-2 px-3 rounded hover:bg-gray-100" title="Edit Site"> <i class="material-icons md-36">mode_edit</i></button>
                        <a title="Edit Site Pages" href="/sitepages/{{ $site->id }}" class="py-2 px-3 rounded hover:bg-gray-100"><i class="material-icons md-36 text-black ">list</i></a>
                        <button title="Delete Site" onclick="confirmDeletion({{ $site->id }})" class="ml-3 py-2 px-3 rounded hover:bg-gray-100">
                            <i class="material-icons md-36">delete_forever</i>
                        </button>
                        <button title="Sync Site and App" wire:click="showTheSyncDialog({{ $site->id }})" class="py-2 px-3 rounded hover:bg-gray-100"><i class="material-icons md-36">sync</i></button>
                        
                        @if ($site->livestream_settings != null)
                        <a title="Edit Live Stream" href="/site/{{ $site->id }}/livestream-mtce" class="py-2 px-3 rounded hover:bg-gray-100"><i class="material-icons md-36">live_tv</i></a>
                        @endif
                        
                        <!-- Site Map -->
                        
                            @if($site)
                            <a title="Edit Site Map" href="{{ route('sites.site-map.edit', ['site' => $site]) }}" 
                                class="py-2 px-3 rounded-lg hover:bg-gray-100"
                                title="Edit Site Map">
                                <svg class="inline-block w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                             </svg>
                            </a>
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



    <script>
    function confirmDeletion(siteId) {
        if (window.confirm('Are you sure you want to delete this site?')) {
            Livewire.dispatch('deleteSite', [siteId]);
        }
    }
</script>
