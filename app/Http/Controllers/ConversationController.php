<?php

namespace App\Http\Controllers;

use Prasso\Messaging\Models\MsgDelivery;
use Prasso\Messaging\Models\MsgInboundMessage;
use Prasso\Messaging\Models\MsgGuest;
use Illuminate\Auth\Access\AuthorizationException;

class ConversationController extends Controller
{
    /**
     * Show a conversation (outbound message + all replies)
     */
    public function show($deliveryId)
    {
        $delivery = MsgDelivery::with('message')->findOrFail($deliveryId);
        
        // Authorization: only allow viewing if user owns the team
        $this->authorize('viewDelivery', $delivery);
        
        // Load recipient based on type
        if ($delivery->recipient_type === 'guest') {
            $delivery->recipient = MsgGuest::find($delivery->recipient_id);
        } else {
            // For other types, try to load dynamically
            $modelClass = $delivery->recipient_type;
            if (class_exists($modelClass)) {
                $delivery->recipient = $modelClass::find($delivery->recipient_id);
            }
        }
        
        // Get all replies for this delivery
        $replies = MsgInboundMessage::where('msg_delivery_id', $deliveryId)
            ->with('guest')
            ->orderBy('received_at', 'asc')
            ->get();
        
        return view('conversations.show', [
            'delivery' => $delivery,
            'message' => $delivery->message,
            'replies' => $replies,
        ]);
    }
}
