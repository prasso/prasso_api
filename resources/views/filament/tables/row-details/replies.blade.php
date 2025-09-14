@php
    $replies = $record->replies;
@endphp

@if ($replies && $replies->count() > 0)
    <div class="p-4">
        <h3 class="text-lg font-semibold">Replies</h3>
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">From</th>
                    <th scope="col" class="px-6 py-3">Message</th>
                    <th scope="col" class="px-6 py-3">Received</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($replies as $reply)
                    <tr class="bg-white border-b">
                        <td class="px-6 py-4">{{ $reply->from }}</td>
                        <td class="px-6 py-4">{{ $reply->body }}</td>
                        <td class="px-6 py-4">{{ $reply->received_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="p-4 text-center text-gray-500">
        No replies for this message.
    </div>
@endif
