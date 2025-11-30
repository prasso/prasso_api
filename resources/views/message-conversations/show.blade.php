@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <a href="{{ route('filament.site-admin.resources.msg-deliveries.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Back to Messages
            </a>
            <a href="{{ route('message-conversations.export', ['messageId' => $message->id]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2m0 0v-8m0 8H3m15 0h3"></path>
                </svg>
                Export CSV
            </a>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Message Conversation</h1>
    </div>

    <!-- Message -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-blue-500">
        <div class="mb-4">
            <h2 class="text-xl font-semibold text-gray-900">{{ $message->subject ?? 'SMS Message' }}</h2>
        </div>
        
        <div class="bg-gray-50 rounded p-4 mb-4">
            <p class="text-gray-800 whitespace-pre-wrap">{{ $message->body }}</p>
        </div>
        
        <div class="text-sm text-gray-600 space-y-1">
            <p><strong>Type:</strong> {{ ucfirst($message->type) }}</p>
            <p><strong>Total Deliveries:</strong> {{ $message->deliveries()->count() }}</p>
        </div>
    </div>

    <!-- Replies Section -->
    <div>
        <h3 class="text-2xl font-bold text-gray-900 mb-4">
            All Replies <span class="text-lg text-gray-500">({{ $replies->count() }})</span>
        </h3>
        
        @if($replies->count() > 0)
            <div class="space-y-4">
                @foreach($replies as $reply)
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <p class="font-semibold text-gray-900">
                                    @if($reply->guest)
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
