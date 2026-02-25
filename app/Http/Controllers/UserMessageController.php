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
    public function index()
    {
        $userId = Auth::user()->user_id;

        $conversations = Conversation::with(['provider'])
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

        
        $conversations = Conversation::with(['provider'])
            ->where('user_id', $userId)
            ->orderByDesc('last_message_time')
            ->get();

       
        $conversation->load('messages');

       
        return view('users.messages', compact('conversations'))
            ->with('selectedConversation', $conversation);
    }
public function send(Request $request)
{
    $request->validate([
        'conversation_id' => 'required|exists:conversations,conversation_id',
        'message' => 'required|string'
    ]);

    $userId = Auth::id(); 
    $message = Message::create([
        'conversation_id' => $request->conversation_id,
        'sender_id' => $userId,
        'sender_type' => 'user',
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
            ]
        ]);
    }

    return back();
}

public function latest(Conversation $conversation)
{
    $userId = Auth::id();

    if ($conversation->user_id !== $userId) {
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
}