<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProviderMessageController extends Controller
{
    public function startConversation($customerId)
    {
        $provider = Auth::user()->providerProfile;

        if (!$provider) {
            abort(403, 'Provider profile not found.');
        }

        $customer = User::where('user_id', $customerId)->firstOrFail();

        $conversation = Conversation::firstOrCreate(
            [
                'user_id' => $customer->user_id,
                'provider_id' => $provider->provider_id,
            ],
            [
                'last_message_time' => now(),
            ]
        );

        return redirect()->route('provider.messages.show', $conversation->conversation_id);
    }

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

        return view('Providers.messages', compact('conversations', 'selectedConversation'));
    }

    public function show(Conversation $conversation)
    {
        $providerId = Auth::user()->providerProfile->provider_id;

        if ($conversation->provider_id !== $providerId) {
            abort(403, 'Unauthorized access.');
        }

        // Mark all customer messages in this conversation as read
        $conversation->messages()
            ->where('sender_type', '!=', 'provider')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $conversations = Conversation::with(['user', 'messages'])
            ->where('provider_id', $providerId)
            ->orderByDesc('last_message_time')
            ->get();

        $conversation->load(['messages' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }]);

        return view('Providers.messages', compact('conversations'))
            ->with('selectedConversation', $conversation);
    }

    public function send(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,conversation_id',
            'message' => 'required|string'
        ]);

        $providerProfile = Auth::user()->providerProfile;
        if (!$providerProfile) {
            abort(403, 'Provider profile not found.');
        }

        $conversation = Conversation::query()
            ->where('conversation_id', $request->conversation_id)
            ->where('provider_id', $providerProfile->provider_id)
            ->first();

        if (!$conversation) {
            abort(403, 'Unauthorized access.');
        }

        $message = Message::create([
            'conversation_id' => $conversation->conversation_id,
            'sender_id' => $providerProfile->provider_id,
            'sender_type' => 'provider',
            'message' => $request->message,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_time' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->message_id,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'is_read' => false,
                    'created_at' => $message->created_at->toIso8601String(),
                    'time' => $message->created_at->format('H:i'),
                    'human_time' => $message->created_at->diffForHumans(),
                ]
            ]);
        }

        return back();
    }

    /**
     * Return all conversations as JSON for sidebar polling.
     * GET /providers/messages/list
     */
    public function conversationList()
    {
        $user = Auth::user();
        if (!$user || !$user->providerProfile) {
            abort(403);
        }

        $providerId = $user->providerProfile->provider_id;

        $conversations = Conversation::with(['user', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->where('provider_id', $providerId)
            ->orderByDesc('last_message_time')
            ->get()
            ->map(function ($conv) {
                $lastMsg = $conv->messages->first();
                $unread  = $conv->messages()
                    ->where('sender_type', '!=', 'provider')
                    ->where('is_read', false)
                    ->count();

                return [
                    'id'          => $conv->conversation_id,
                    'name'        => $conv->user->full_name ?? 'Unknown',
                    'last_message'=> $lastMsg?->message ?? '',
                    'last_sender' => $lastMsg?->sender_type ?? '',
                    'unread_count'=> $unread,
                ];
            });

        return response()->json(['conversations' => $conversations]);
    }


  
    public function markRead(Conversation $conversation)
    {
        $providerId = Auth::user()->providerProfile->provider_id;

        if ($conversation->provider_id !== $providerId) {
            abort(403);
        }

        $conversation->messages()
            ->where('sender_type', '!=', 'provider')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Poll for new messages after a given timestamp.
     * Also returns IDs of the provider's sent messages that have been read.
     */
    public function latest(Conversation $conversation)
    {
        $providerId = Auth::user()->providerProfile->provider_id;

        if ($conversation->provider_id !== $providerId) {
            abort(403);
        }

        $after = request('after');

        $query = $conversation->messages()->orderBy('created_at', 'asc');

        if ($after) {
            $query->where('created_at', '>', $after);
        }

        $messages = $query->get()->map(function ($message) {
            return [
                'id'          => $message->message_id,
                'message'     => $message->message,
                'sender_type' => $message->sender_type,
                'is_read'     => (bool) $message->is_read,
                'created_at'  => $message->created_at->toIso8601String(),
                'time'        => $message->created_at->format('H:i'),
            ];
        });

        // Return IDs of the provider's own sent messages that the customer has read
        $readMessageIds = $conversation->messages()
            ->where('sender_type', 'provider')
            ->where('is_read', true)
            ->pluck('message_id')
            ->map(fn($id) => (string) $id)
            ->values();

        return response()->json([
            'messages'         => $messages,
            'read_message_ids' => $readMessageIds,
        ]);
    }
}


