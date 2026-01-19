@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('filament.site-admin.resources.msg-deliveries.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Back to Messages
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Conversation</h1>
    </div>

    <!-- Outbound Message -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-blue-500">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $message->subject ?? 'SMS Message' }}</h2>
                <p class="text-sm text-gray-500 mt-1">Sent {{ $delivery->sent_at->diffForHumans() }}</p>
            </div>
            <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                @if($delivery->status === 'sent') bg-green-100 text-green-800
                @elseif($delivery->status === 'delivered') bg-green-100 text-green-800
                @elseif($delivery->status === 'failed') bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ ucfirst($delivery->status) }}
            </span>
        </div>
        
        <div class="bg-gray-50 rounded p-4 mb-4">
            <p class="text-gray-800 whitespace-pre-wrap">{{ $message->body }}</p>
        </div>
        
        <div class="text-sm text-gray-600 space-y-1">
            <p><strong>To:</strong> 
                @if($delivery->recipient_type === 'guest')
                    {{ $delivery->recipient->name ?? 'Guest' }} ({{ $delivery->recipient->phone ?? $delivery->recipient->email }})
                @else
                    {{ $delivery->recipient->name ?? $delivery->recipient->email }}
                @endif
            </p>
            <p><strong>Channel:</strong> {{ ucfirst($delivery->channel) }}</p>
            <p><strong>Sent:</strong> {{ $delivery->sent_at->format('M d, Y H:i:s') }}</p>
            @if($delivery->delivered_at)
                <p><strong>Delivered:</strong> {{ $delivery->delivered_at->format('M d, Y H:i:s') }}</p>
            @endif
        </div>
    </div>

    <!-- Replies Section -->
    <div>
        <h3 class="text-2xl font-bold text-gray-900 mb-4">
            Replies <span class="text-lg text-gray-500">({{ $replies->count() }})</span>
        </h3>
        
        @if($replies->count() > 0)
            <div class="space-y-4">
                @foreach($replies as $reply)
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <p class="font-semibold text-gray-900">
                                    @if($reply->msg_guest_id)
                                        {{ $reply->guest->name ?? 'Guest' }}
                                    @else
                                        Unknown Sender
                                    @endif
                                </p>
                                <p class="text-sm text-gray-500">{{ $reply->from }}</p>
                            </div>
                            <span class="text-sm text-gray-500">{{ $reply->received_at->diffForHumans() }}</span>
                        </div>
                        
                        <div class="bg-gray-50 rounded p-4">
                            <p class="text-gray-800 whitespace-pre-wrap">{{ $reply->body }}</p>
                        </div>
                        
                        @if($reply->media && count($reply->media) > 0)
                            <div class="mt-3 pt-3 border-t">
                                <p class="text-sm font-medium text-gray-700 mb-2">Attachments:</p>
                                <ul class="space-y-1">
                                    @foreach($reply->media as $mediaUrl)
                                        <li>
                                            <a href="{{ $mediaUrl }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm break-all">
                                                {{ basename($mediaUrl) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <p class="text-xs text-gray-500 mt-3">Received: {{ $reply->received_at->format('M d, Y H:i:s') }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-gray-50 rounded-lg p-8 text-center">
                <p class="text-gray-500 text-lg">No replies yet</p>
            </div>
        @endif
    </div>
</div>
@endsection
