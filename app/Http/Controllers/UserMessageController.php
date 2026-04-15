<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProviderProfile;
use App\Models\User;

class UserMessageController extends Controller
{

public function startConversation(Request $request)
{
    $request->validate([
        'provider_id' => 'required|exists:provider_profiles,provider_id',
    ]);

    $userId = Auth::user()->user_id;

    // Find existing conversation or create a new one
    $conversation = Conversation::firstOrCreate(
        [
            'user_id'     => $userId,
            'provider_id' => $request->provider_id,
        ],
        [
            'last_message_time' => now(),
        ]
    );

    return redirect()->route('user.messages.show', $conversation->conversation_id);
}

    public function index()
    {
        $userId = Auth::user()->user_id;

        $conversations = Conversation::with(['provider', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->where('user_id', $userId)
            ->orderByDesc('last_message_time')
            ->get();

        $selectedConversation = null;

        return view('users.messages', compact('conversations', 'selectedConversation'));
    }

    public function show(Conversation $conversation)
    {
        $userId = Auth::user()->user_id;

        if ($conversation->user_id !== $userId) {
            abort(403, 'Unauthorized access.');
        }

        // Mark all provider messages in this conversation as read
        $conversation->messages()
            ->where('sender_type', '!=', 'customer')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $conversations = Conversation::with(['provider', 'messages'])
            ->where('user_id', $userId)
            ->orderByDesc('last_message_time')
            ->get();

        $conversation->load(['messages' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }]);

        return view('users.messages', compact('conversations'))
            ->with('selectedConversation', $conversation);
    }

    public function send(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,conversation_id',
            'message'         => 'required|string'
        ]);

        $userId = Auth::id();

        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'sender_id'       => $userId,
            'sender_type'     => 'customer',
            'message'         => $request->message,
            'is_read'         => false,
        ]);

        Conversation::where('conversation_id', $request->conversation_id)
            ->update(['last_message_time' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id'          => $message->message_id,
                    'message'     => $message->message,
                    'sender_type' => $message->sender_type,
                    'is_read'     => false,
                    'created_at'  => $message->created_at->toIso8601String(),
                    'time'        => $message->created_at->format('H:i'),
                    'human_time'  => $message->created_at->diffForHumans(),
                ]
            ]);
        }

        return back();
    }

    /**
     * Return all conversations as JSON for sidebar polling.
     * GET /users/messages/list
     */
    public function conversationList()
    {
        $userId = Auth::id();

        $conversations = Conversation::with(['provider.user', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->where('user_id', $userId)
            ->orderByDesc('last_message_time')
            ->get()
            ->map(function ($conv) {
                $lastMsg = $conv->messages->first();
                $unread  = $conv->messages()
                    ->where('sender_type', '!=', 'customer')
                    ->where('is_read', false)
                    ->count();

                return [
                    'id'          => $conv->conversation_id,
                    'name'        => $conv->provider->user->full_name ?? 'Unknown',
                    'last_message'=> $lastMsg?->message ?? '',
                    'last_sender' => $lastMsg?->sender_type ?? '',
                    'unread_count'=> $unread,
                ];
            });

        return response()->json(['conversations' => $conversations]);
    }


    
    
    public function markRead(Conversation $conversation)
    {
        $userId = Auth::id();

        if ($conversation->user_id !== $userId) {
            abort(403);
        }

        $conversation->messages()
            ->where('sender_type', '!=', 'customer')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Poll for new messages after a given timestamp.
     * Also returns IDs of the current user's sent messages that have been read.
     */
    public function latest(Conversation $conversation)
    {
        $userId = Auth::id();

        if ($conversation->user_id !== $userId) {
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

        // Return IDs of the user's own sent messages that the provider has read
        $readMessageIds = $conversation->messages()
            ->where('sender_type', 'customer')
            ->where('is_read', true)
            ->pluck('message_id')
            ->map(fn($id) => (string) $id)
            ->values();

        return response()->json([
            'messages'        => $messages,
            'read_message_ids' => $readMessageIds,
        ]);
    }
}