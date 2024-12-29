<x-app-layout :site="$site ?? null">
    <x-slot name="title">Livestream Maintenance</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Livestream Maintenance') }}
        </h2>
    </x-slot>

    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

    <!-- this is an admin page that will show a list of livestreams at S3 that have not been queued up for live viewing-->

    
    <div class='mt-20 mb-20 border-t-4 border-b-4'>
        <table>
            <thead>
            <tr class='pb-4 pt-4 bg-gray-200 border-t-2 border-b-2 mb-20'>
                    <th>Live Streams Need Queued</th>
                    <th>Queue. These streams area available to be added to the Recent Videos page.</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($livestreams))
                @php($counter = 1)
                @foreach($livestreams as $livestream)
                
                <tr>
                    <td>{{ $livestream }}</td>
                    <td><form method="post" action="{{ url('/site/'.$site->id.'/livestream-mtce/move-to-permanent-storage/') }}">
                    @csrf
                    <input type="hidden" id="media_to_queue{{ $counter }}" name="media_to_queue{{$counter++}}" value="{{ $livestream }}"><input type="submit" class="teambutton text-white btn-sm font-bold py-2 px-4 rounded my-3" value="Queue for access"/></form></td>
                </tr>
                @endforeach
                @endif
                @if ($counter == 1)
                <tr>
                    <td>No livestreams to queue</td>
                </tr>
                @endif
                </tbody>
        </table>
    </div>
    <div class='mt-20 mb-10'>
        <table class="table">
        <thead>
            <tr class='pb-4 pt-4 bg-gray-200 border-t-2 border-b-2 mb-20'>
                <th class="text-left">Live Streams Available for Viewing</th>
                <th>Thumbnail URL</th>
                <th>Video Duration</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @if (isset($site->media))
            @foreach ($site->media as $item)
                <tr>
                    <td>{{ $item->media_title }}</td>
                    <td><img class="max-w-sm" src="{{ $item->thumb_url }}" ></td>
                    <td>{{ $item->duration }}</td>
                    <td>
                        <a class="py-2 px-4 rounded" href="/site/{{ $site->id }}/livestream-mtce/{{ $item->id }}"> <i class="material-icons md-36">mode_edit</i></a>
                    </td>
                </tr>
            @endforeach
            @endif
        </tbody>
        </table>
    </div>

</div>
</x-app-layout>










