@extends('users.layout')

@section('content')
<div class="h-[calc(100vh-120px)] flex bg-white rounded-lg shadow overflow-hidden">

    <!-- LEFT: Conversations List -->
    <div class="w-1/3 border-r bg-gray-50 flex flex-col overflow-y-auto">
        <h2 class="p-4 font-semibold text-gray-700 border-b">Conversations</h2>

        @forelse($conversations as $conversation)
            <a href="{{ route('user.messages.show', $conversation->conversation_id) }}"
               class="p-4 border-b hover:bg-gray-100 flex items-center gap-3
                      {{ isset($selectedConversation) && $selectedConversation->conversation_id === $conversation->conversation_id ? 'bg-gray-200' : '' }}">
                <div class="w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center font-bold">
                    {{ strtoupper(substr($conversation->provider->user->full_name ?? 'P', 0, 2)) }}
                </div>
                <div class="flex-1">
                    <h3 class="font-medium text-gray-900">{{ $conversation->provider->user->full_name ?? 'Unknown' }}</h3>
                    <p class="text-xs text-gray-500">
                        @if($conversation->messages->last())
                            {{ \Illuminate\Support\Str::limit($conversation->messages->last()->message, 25) }}
                        @else
                            <span class="text-gray-300">· · ·</span> {{-- subtle placeholder instead of "No messages yet" --}}
                        @endif
                    </p>
                </div>
            </a>
        @empty
            <p class="p-4 text-gray-400">No conversations yet.</p>
        @endforelse
    </div>

    <!-- RIGHT: Chat Area -->
    <div class="flex-1 flex flex-col">

        @if($selectedConversation ?? false)

        <!-- Chat Header (shows the provider) -->
        <div class="p-4 border-b bg-white flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center font-bold">
                {{ strtoupper(substr($selectedConversation->provider->user->full_name ?? 'P', 0, 2)) }}
            </div>
            <div>
                <h3 class="font-medium text-gray-900">
                    {{ $selectedConversation->provider->user->full_name ?? 'Provider' }}
                </h3>
                <p class="text-xs text-green-500">Online</p>
            </div>
        </div>

        <!-- Messages Area -->
        <div id="messagesArea" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50">
            @foreach($selectedConversation->messages as $message)
                @php $isUser = $message->sender_type === 'user'; @endphp

                <div data-id="{{ $message->id }}" class="{{ $isUser ? 'flex justify-end' : 'flex items-start gap-2' }}">
                    <div class="{{ $isUser
                        ? 'flex flex-col items-end bg-orange-500 text-white'
                        : 'bg-white flex flex-col' }}
                        px-4 py-2 rounded-lg shadow max-w-xs">

                        <p class="text-sm">{{ $message->message }}</p>

                        <span class="{{ $isUser
                            ? 'text-xs text-orange-100 mt-1'
                            : 'text-xs text-gray-400 mt-1' }}"
                              title="{{ $message->created_at }}">
                            {{ $message->created_at->format('H:i') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Message Input Form -->
        <form id="messageForm" class="p-4 border-t bg-white flex items-center gap-3">
            @csrf
            <input type="hidden" name="conversation_id"
                   value="{{ $selectedConversation->conversation_id }}">

            <input id="messageInput" type="text" name="message"
                   placeholder="Type your message..."
                   class="flex-1 px-4 py-2 border rounded-full focus:ring-2 focus:ring-orange-500 focus:outline-none"
                   required>

            <button type="submit" id="sendButton"
                    class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-full transition flex items-center gap-2">
                <span>Send</span>
                <!-- Spinner (hidden by default) -->
                <svg id="sendSpinner" class="hidden w-4 h-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </form>

        @endif

    </div>
</div>

<!-- Toast notification container (hidden by default) -->
<div id="toast" class="fixed bottom-4 right-4 z-50 hidden bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg transition-opacity duration-300"></div>

<!-- Extra animation styles -->
<style>
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const messagesArea = document.getElementById('messagesArea');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const spinner = document.getElementById('sendSpinner');
    const toast = document.getElementById('toast');

    if (!messageForm) return;

    // Store loaded message IDs as numbers
    let loadedMessageIds = new Set();
    document.querySelectorAll('#messagesArea [data-id]').forEach(el => {
        loadedMessageIds.add(Number(el.dataset.id));
    });

    function scrollToBottom() {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }

    function escapeHTML(str) {
        return str.replace(/[&<>"]/g, m => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;'
        }[m]));
    }

    function showToast(message, type = 'error') {
        toast.textContent = message;
        toast.className = `fixed bottom-4 right-4 z-50 px-4 py-2 rounded-lg shadow-lg transition-opacity duration-300 ${type === 'error' ? 'bg-red-500' : 'bg-green-500'} text-white`;
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }

    // Polling for new messages (only from the other party)
    async function fetchMessages() {
        const conversationId = messageForm.conversation_id.value;
        if (!conversationId) return; // No conversation selected

        try {
            const res = await fetch(`/users/messages/${conversationId}/latest`);
            if (!res.ok) return;
            const data = await res.json();

            data.messages.forEach(msg => {
                const msgId = Number(msg.id);
                if (loadedMessageIds.has(msgId)) return;

                loadedMessageIds.add(msgId);
                const isMine = msg.sender_type === 'user'; // false => from provider

                const div = document.createElement('div');
                div.setAttribute('data-id', msgId);
                div.className = `flex ${isMine ? 'justify-end' : 'items-start'} fade-in`;

                div.innerHTML = `
                    <div class="${isMine
                        ? 'flex flex-col items-end bg-orange-500 text-white'
                        : 'bg-white flex flex-col'}
                        px-4 py-2 rounded-lg shadow max-w-xs">
                        <p class="text-sm">${escapeHTML(msg.message)}</p>
                        <span class="text-xs mt-1">${msg.time}</span>
                    </div>
                `;
                messagesArea.appendChild(div);
            });
            scrollToBottom();
        } catch (err) {
            console.error('Polling error:', err);
        }
    }

    // Start polling (every 3 seconds)
    const pollInterval = setInterval(fetchMessages, 3000);

    // Clean up interval when leaving the page (optional)
    window.addEventListener('beforeunload', () => clearInterval(pollInterval));

    // Send message
    messageForm.addEventListener('submit', async e => {
        e.preventDefault(); // ✅ Stops page refresh

        const message = messageInput.value.trim();
        if (!message) return;

        // Disable form
        messageInput.disabled = true;
        sendButton.disabled = true;
        spinner.classList.remove('hidden');

        try {
            const res = await fetch("{{ route('user.messages.send') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': messageForm._token.value,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    conversation_id: messageForm.conversation_id.value,
                    message: message
                })
            });

            if (!res.ok) throw new Error('Send failed');

            const data = await res.json();
            const newMsg = data.message;

            const newId = Number(newMsg.id);
            loadedMessageIds.add(newId);

            // Append sent message immediately
            const div = document.createElement('div');
            div.setAttribute('data-id', newId);
            div.className = 'flex justify-end fade-in';
            div.innerHTML = `
                <div class="flex flex-col items-end bg-orange-500 text-white px-4 py-2 rounded-lg shadow max-w-xs">
                    <p class="text-sm">${escapeHTML(newMsg.message)}</p>
                    <span class="text-xs text-orange-100 mt-1">${newMsg.time}</span>
                </div>
            `;
            messagesArea.appendChild(div);
            scrollToBottom();

            messageInput.value = '';
        } catch (err) {
            console.error(err);
            showToast('Failed to send message. Please try again.');
        } finally {
            messageInput.disabled = false;
            sendButton.disabled = false;
            spinner.classList.add('hidden');
        }
    });
});
</script>
@endpush