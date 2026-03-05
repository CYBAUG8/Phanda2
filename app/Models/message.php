<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProviderMessageController extends Controller
{
    /**
     * Show list of conversations for provider.
     */
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

    /**
     * Show a specific conversation.
     */
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

        return view('providers.messages', compact('conversations', 'selectedConversation'))
            ->with('selectedConversation', $conversation);
    }

    /**
     * Send a message to a user (creates conversation if needed).
     */
    public function send(Request $request)
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,user_id',
            'message' => 'required|string|max:1000',
        ]);

        $provider = Auth::user()->providerProfile;
        $providerId = $provider->provider_id;

        // Find or create conversation
        $conversation = Conversation::firstOrCreate(
            [
                'provider_id' => $providerId,
                'user_id' => $request->user_id,
            ],
            [
                'last_message_time' => now(),
            ]
        );

        // Create message
        $message = Message::create([
            'conversation_id' => $conversation->conversation_id,
            'sender_id'       => $providerId,
            'sender_type'     => 'provider',
            'message'         => $request->message,
        ]);

        // Update conversation timestamp
        $conversation->update(['last_message_time' => now()]);

        // Return JSON response for AJAX
        return response()->json([
            'success' => true,
            'message' => [
                'id'         => $message->id,
                'message'    => $message->message,
                'time'       => $message->created_at->format('H:i'),
                'human_time' => $message->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Fetch latest messages in a conversation.
     */
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
                    'id'          => $message->id,
                    'message'     => $message->message,
                    'sender_type' => $message->sender_type,
                    'time'        => $message->created_at->format('H:i'),
                ];
            });

        return response()->json(['messages' => $messages]);
    }
}