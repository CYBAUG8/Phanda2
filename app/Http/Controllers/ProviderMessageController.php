<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ProviderMessageController extends Controller
{
        public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->providerProfile) {
            abort(403, 'Provider profile not found.');
        }

        $providerId = $user->providerProfile->provider_id;

        $conversations = Conversation::with(['user', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->where('provider_id', $providerId)
            ->orderByDesc('last_message_time')
            ->get();

        $selectedConversation = null;

        return view('providers.messages', compact('conversations', 'selectedConversation'));
    }

        public function show(Conversation $conversation)
        {
            $providerId = Auth::user()->providerProfile->provider_id;

            if ($conversation->provider_id !== $providerId) {
                abort(403, 'Unauthorized access.');
            }

        
            $conversations = Conversation::with(['user'])
                ->where('provider_id', $providerId)
                ->orderByDesc('last_message_time')
                ->get();

            $conversation->load('messages');

        
            return view('providers.messages', compact('conversations'))
                ->with('selectedConversation', $conversation);
        }

    public function send(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,conversation_id',
            'message' => 'required|string'
        ]);

        $providerId = Auth::user()->providerProfile->provider_id;

        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => $providerId,
            'sender_type' => 'provider',
            'message' => $request->message,
        ]);

        Conversation::where('conversation_id', $request->conversation_id)
            ->update(['last_message_time' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id'         => $message->id,
                    'message'    => $message->message,
                    'created_at' => $message->created_at->toDateTimeString(),
                    'time'       => $message->created_at->format('H:i'),       
                    'human_time' => $message->created_at->diffForHumans(),      
                ]
            ]);
        }

        return back();
    }

    public function latest(Conversation $conversation)
    {
        $providerId = Auth::user()->providerProfile->provider_id;

        if ($conversation->provider_id !== $providerId) {
            abort(403);
        }

        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'time' => $message->created_at->format('H:i'),
                ];
            });

        return response()->json([
            'messages' => $messages
        ]);
    }
    public function latestByUser($userId)
    {
        $providerId = Auth::user()->providerProfile->provider_id;

        $conversation = Conversation::where('provider_id', $providerId)
            ->where('user_id', $userId)
            ->first();

        if(!$conversation) return response()->json(['messages'=>[]]);

        return $this->latest($conversation);
    }
    public function confirm($id) {
    $booking = Booking::findOrFail($id);
    $booking->status = 'in_progress';
    $booking->save();

    return response()->json([
        'success' => true,
        'status' => $booking->status,
        'user_id' => $booking->user_id,
        'user_name' => $booking->user->name
    ]);
    }

    public function cancel($id) {
        $booking = Booking::findOrFail($id);
        $booking->status = 'cancelled';
        $booking->save();

        return response()->json([
            'success' => true,
            'status' => $booking->status,
            'user_id' => $booking->user_id,
            'user_name' => $booking->user->name
        ]);
    }

    public function complete($id) {
        $booking = Booking::findOrFail($id);
        $booking->status = 'completed';
        $booking->save();

        return response()->json([
            'success' => true,
            'status' => $booking->status,
            'user_id' => $booking->user_id,
            'user_name' => $booking->user->name
        ]);
    }
}