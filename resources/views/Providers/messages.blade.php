@extends('Providers.layout')

@section('content')
<div class="h-[calc(100vh-120px)] flex bg-white rounded-lg shadow overflow-hidden">

    <!-- LEFT: Conversations List -->
    <div class="w-1/3 border-r bg-gray-50 flex flex-col overflow-y-auto">
        <h2 class="p-4 font-semibold text-gray-700 border-b">Messages</h2>

        <!-- Search input -->
        <div class="p-2 border-b">
            <input type="text" id="conversationSearch"
                   placeholder="Search by name..."
                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
        </div>

        <!-- Conversation list container -->
        <div id="conversationsList" class="flex-1 overflow-y-auto">
            @forelse($conversations as $conversation)
                @php
                    $unreadCount = $conversation->messages
                        ->where('sender_type', '!=', 'provider')
                        ->where('is_read', false)
                        ->count();
                    $lastMsg = $conversation->messages->sortByDesc('created_at')->first();
                    $lastTime = $lastMsg?->created_at ?? $conversation->created_at;
                @endphp
                <a href="{{ route('provider.messages.show', $conversation->conversation_id) }}"
                   data-conversation-id="{{ $conversation->conversation_id }}"
                   data-last-time="{{ $lastTime }}"
                   class="conversation-item p-4 border-b hover:bg-gray-100 flex items-center gap-3
                          {{ isset($selectedConversation) && $selectedConversation->conversation_id === $conversation->conversation_id ? 'bg-gray-200' : '' }}">
                    <div class="w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center font-bold flex-shrink-0">
                        {{ strtoupper(substr($conversation->user->full_name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-gray-900 {{ $unreadCount > 0 ? 'font-semibold' : '' }}">
                                {{ $conversation->user->full_name ?? 'Unknown' }}
                            </h3>
                            @if($unreadCount > 0)
                                <span class="unread-badge ml-2 min-w-[20px] h-5 px-1 bg-orange-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
                                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                </span>
                            @else
                                <span class="unread-badge ml-2 min-w-[20px] h-5 px-1 bg-orange-500 text-white text-xs rounded-full items-center justify-center font-bold hidden">0</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 truncate conversation-preview {{ $unreadCount > 0 ? 'font-medium text-gray-700' : '' }}">
                            @if($lastMsg)
                                @if($lastMsg->sender_type === 'provider')
                                    <span class="text-gray-400">You: </span>{{ \Illuminate\Support\Str::limit($lastMsg->message, 25) }}
                                @else
                                    {{ \Illuminate\Support\Str::limit($lastMsg->message, 25) }}
                                @endif
                            @else
                                <span class="text-gray-300">· · ·</span>
                            @endif
                        </p>
                    </div>
                </a>
            @empty
                <p class="p-4 text-gray-400">No conversations yet.</p>
            @endforelse
        </div>
    </div>

    <!-- RIGHT: Chat Area -->
    <div class="flex-1 flex flex-col">

        @if($selectedConversation ?? false)

        <!-- Chat Header -->
        <div class="p-4 border-b bg-white flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center font-bold">
                {{ strtoupper(substr($selectedConversation->user->full_name ?? 'U', 0, 2)) }}
            </div>
            <div>
                <h3 class="font-medium text-gray-900">
                    {{ $selectedConversation->user->full_name }}
                </h3>
                <p class="text-xs text-green-500">Online</p>
            </div>
        </div>

        <!-- Messages Area -->
        @php
            $lastMsgTs = $selectedConversation->messages->last()?->created_at?->toIso8601String() ?? '';
            $groupedMessages = $selectedConversation->messages->groupBy(fn($m) => $m->created_at->toDateString());
        @endphp
        <div id="messagesArea"
             data-last-ts="{{ $lastMsgTs }}"
             class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50">

            @foreach($groupedMessages as $date => $messages)
                {{-- Date Separator --}}
                <div class="date-separator flex items-center gap-3 my-2" data-date="{{ $date }}">
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <span class="text-xs text-gray-400 font-medium px-2 whitespace-nowrap">
                        @php
                            $d = \Carbon\Carbon::parse($date);
                            if ($d->isToday()) echo 'Today';
                            elseif ($d->isYesterday()) echo 'Yesterday';
                            else echo $d->format('F j, Y');
                        @endphp
                    </span>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>

                @foreach($messages as $message)
                    @php $isProvider = $message->sender_type === 'provider'; @endphp
                    <div data-id="{{ $message->message_id }}"
                         data-sender="{{ $message->sender_type }}"
                         data-read="{{ $message->is_read ? 'true' : 'false' }}"
                         class="{{ $isProvider ? 'flex justify-end' : 'flex items-start gap-2' }}">
                        <div class="{{ $isProvider
                            ? 'flex flex-col items-end bg-orange-500 text-white'
                            : 'bg-white flex flex-col text-gray-800' }}
                            px-4 py-2 rounded-lg shadow max-w-xs">

                            <p class="text-sm">{{ $message->message }}</p>

                            <div class="flex items-center gap-1 mt-1">
                                <span class="{{ $isProvider ? 'text-xs text-orange-100' : 'text-xs text-gray-500' }}"
                                      title="{{ $message->created_at }}">
                                    {{ $message->created_at->format('H:i') }}
                                </span>
                                @if($isProvider)
                                    <span class="read-receipt ml-0.5">
@if($message->is_read)
<svg class="w-4 h-4 text-orange-200" viewBox="0 0 20 16" fill="none">
  <path d="M2 8L6 12L11 3" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M8 8L12 12L17 3" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
@else
<svg class="w-4 h-4 text-orange-200" viewBox="0 0 20 16" fill="none">
  <path d="M4 8L8 12L16 3" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
@endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
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
                <svg id="sendSpinner" class="hidden w-4 h-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </form>

        @else
        <div class="flex-1 flex items-center justify-center text-gray-400">
            <p>Select a conversation to start messaging</p>
        </div>
        @endif

    </div>
</div>

<div id="toast" class="fixed bottom-4 right-4 z-50 hidden bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg transition-opacity duration-300"></div>

<style>
    .fade-in { animation: fadeIn 0.3s ease-in; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ----- Search / Filter Conversations -----
    const searchInput = document.getElementById('conversationSearch');
    const conversationsList = document.getElementById('conversationsList');

    function applySearch() {
        if (!searchInput || !conversationsList) return;
        const term = searchInput.value.toLowerCase().trim();
        conversationsList.querySelectorAll('a.conversation-item').forEach(link => {
            const name = (link.querySelector('h3')?.textContent ?? '').toLowerCase();
            link.style.display = (term === '' || name.includes(term)) ? 'flex' : 'none';
        });
    }
    if (searchInput) searchInput.addEventListener('keyup', applySearch);

    // ----- Messaging -----
    const messagesArea = document.getElementById('messagesArea');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const spinner = document.getElementById('sendSpinner');
    const toast = document.getElementById('toast');

    // Track seen message IDs to avoid duplicates
    const seenMessageIds = new Set();
    messagesArea?.querySelectorAll('[data-id]').forEach(el => {
        seenMessageIds.add(el.getAttribute('data-id'));
    });

    let lastCreatedAt = messagesArea ? (messagesArea.dataset.lastTs || '') : '';
    const activeConversationId = messageForm ? messageForm.conversation_id.value : null;

    // SVG ticks
const singleTickSVG = `
<svg class="w-4 h-4 text-orange-200" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M4 8L8 12L16 3" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
</svg>`;
const doubleTickSVG = `
<svg class="w-4 h-4 text-orange-200" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M2 8L6 12L11 3" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M8 8L12 12L17 3" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
</svg>`;

    // Helper: check if user is near bottom (within 50px)
    function isNearBottom() {
        if (!messagesArea) return true;
        const threshold = 50;
        return messagesArea.scrollHeight - messagesArea.scrollTop - messagesArea.clientHeight < threshold;
    }

    function scrollToBottom(force = false) {
        if (!messagesArea) return;
        if (force || isNearBottom()) {
            messagesArea.scrollTo({ top: messagesArea.scrollHeight, behavior: 'smooth' });
        }
    }

    function escapeHTML(str) {
        return str.replace(/[&<>"]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
    }

    function showToast(message, type = 'error') {
        toast.textContent = message;
        toast.className = `fixed bottom-4 right-4 z-50 px-4 py-2 rounded-lg shadow-lg transition-opacity duration-300 ${type === 'error' ? 'bg-red-500' : 'bg-green-500'} text-white`;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3000);
    }

    // Date handling
    function formatDateLabel(dateStr) {
        const d = new Date(dateStr);
        const today = new Date();
        const yesterday = new Date();
        yesterday.setDate(today.getDate() - 1);
        const toDateStr = (dt) => dt.toISOString().split('T')[0];

        if (toDateStr(d) === toDateStr(today)) return 'Today';
        if (toDateStr(d) === toDateStr(yesterday)) return 'Yesterday';
        return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    function getOrCreateDateSeparator(dateStr) {
        let sep = messagesArea.querySelector(`.date-separator[data-date="${dateStr}"]`);
        if (!sep) {
            sep = document.createElement('div');
            sep.className = 'date-separator flex items-center gap-3 my-2 fade-in';
            sep.setAttribute('data-date', dateStr);
            sep.innerHTML = `
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs text-gray-400 font-medium px-2 whitespace-nowrap">${formatDateLabel(dateStr)}</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            `;
            messagesArea.appendChild(sep);
        }
        return sep;
    }

    // Append a single message to the messages area
    function appendMessage(msg, isMine) {
        if (!messagesArea) return;
        const msgId = String(msg.id);
        if (seenMessageIds.has(msgId)) return;

        const msgDate = (msg.created_at || '').split('T')[0] || new Date().toISOString().split('T')[0];
        getOrCreateDateSeparator(msgDate);

        const div = document.createElement('div');
        div.setAttribute('data-id', msgId);
        div.setAttribute('data-sender', msg.sender_type || (isMine ? 'provider' : 'customer'));
        div.setAttribute('data-read', msg.is_read ? 'true' : 'false'); // ✅ use actual read status
        div.className = `flex ${isMine ? 'justify-end' : 'items-start'} fade-in`;

        const tick = msg.is_read ? doubleTickSVG : singleTickSVG; // ✅ show correct tick immediately

        div.innerHTML = `
            <div class="${isMine
                ? 'flex flex-col items-end bg-orange-500 text-white'
                : 'bg-white flex flex-col text-gray-800'}
                px-4 py-2 rounded-lg shadow max-w-xs">
                <p class="text-sm">${escapeHTML(msg.message)}</p>
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-xs ${isMine ? 'text-orange-100' : 'text-gray-500'}">${msg.time}</span>
                    ${isMine ? `<span class="read-receipt ml-0.5">${tick}</span>` : ''}
                </div>
            </div>
        `;
        messagesArea.appendChild(div);
        seenMessageIds.add(msgId);

        if (msg.created_at && msg.created_at > lastCreatedAt) {
            lastCreatedAt = msg.created_at;
        }
    }

    // Update read receipts for messages that became read
    function updateReadReceipts(readMessageIds) {
        if (!readMessageIds || !readMessageIds.length) return;
        readMessageIds.forEach(id => {
            const el = messagesArea?.querySelector(`[data-id="${id}"]`);
            if (!el) return;
            if (el.getAttribute('data-read') === 'true') return;
            el.setAttribute('data-read', 'true');
            const receipt = el.querySelector('.read-receipt');
            if (receipt) receipt.innerHTML = doubleTickSVG;
        });
    }

    // Update conversation list badge and preview, then reorder list
    function setUnreadBadge(conversationId, count) {
        const link = conversationsList?.querySelector(`a[data-conversation-id="${conversationId}"]`);
        if (!link) return;
        const badge = link.querySelector('.unread-badge');
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
            badge.classList.add('flex');
        } else {
            badge.classList.add('hidden');
            badge.classList.remove('flex');
        }
    }

    function updateConversationPreview(conversationId, messageText, isMine, lastTime) {
        const link = conversationsList?.querySelector(`a[data-conversation-id="${conversationId}"]`);
        if (!link) return;

        // Update preview text
        const preview = link.querySelector('.conversation-preview');
        if (preview) {
            const truncated = messageText.length > 25 ? messageText.substring(0, 25) + '…' : messageText;
            preview.innerHTML = isMine
                ? `<span class="text-gray-400">You: </span>${escapeHTML(truncated)}`
                : escapeHTML(truncated);
        }

        // Update last message time attribute for sorting
        if (lastTime) {
            link.setAttribute('data-last-time', lastTime);
        }
    }

    // Reorder conversation list based on data-last-time (descending)
    function reorderConversationList() {
        const container = conversationsList;
        if (!container) return;
        const items = Array.from(container.querySelectorAll('a.conversation-item'));
        items.sort((a, b) => {
            const timeA = a.getAttribute('data-last-time') || '0';
            const timeB = b.getAttribute('data-last-time') || '0';
            return timeB.localeCompare(timeA); // descending
        });
        // Re-attach in new order
        items.forEach(item => container.appendChild(item));
        // Re-apply search filter after reorder
        applySearch();
    }

    async function markMessagesRead(conversationId) {
        try {
            await fetch(`/providers/messages/${conversationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        || messageForm?._token?.value || ''
                }
            });
            setUnreadBadge(conversationId, 0);
        } catch (e) {
            console.error('Mark read error:', e);
        }
    }

    if (activeConversationId) {
        markMessagesRead(activeConversationId);
    }

    // Poll for new messages in the active conversation
    async function fetchMessages() {
        if (!activeConversationId) return;

        try {
            const url = `/providers/messages/${activeConversationId}/latest`
                + (lastCreatedAt ? `?after=${encodeURIComponent(lastCreatedAt)}` : '');

            const res = await fetch(url);
            if (!res.ok) return;
            const data = await res.json();

            requestAnimationFrame(() => {
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        const isMine = msg.sender_type === 'provider';
                        appendMessage(msg, isMine);
                        // Update conversation preview with the latest message
                        updateConversationPreview(activeConversationId, msg.message, isMine, msg.created_at);
                    });
                    // Reorder list because the active conversation might now be the most recent
                    reorderConversationList();
                    scrollToBottom(); // auto-scroll only if user was near bottom
                    markMessagesRead(activeConversationId);
                }

                if (data.read_message_ids && data.read_message_ids.length > 0) {
                    updateReadReceipts(data.read_message_ids);
                }
            });
        } catch (err) {
            console.error('Polling error:', err);
        }
    }

    // Poll for conversation list updates (unread counts, previews, ordering)
    async function fetchConversationList() {
        try {
            const res = await fetch('/providers/messages/list', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();

            data.conversations.forEach(conv => {
                const convId = String(conv.id);

                // Update badge for non-active conversations only
                if (convId !== String(activeConversationId)) {
                    setUnreadBadge(convId, conv.unread_count);
                }

                // Update preview and last-time attribute
                updateConversationPreview(convId, conv.last_message, conv.last_sender === 'provider', conv.last_time);
            });

            // Reorder the whole list based on updated last-time
            reorderConversationList();
        } catch (err) {
            console.error('Conversation list polling error:', err);
        }
    }

    // Polling intervals
    const pollInterval = setInterval(fetchMessages, 3000);
    const listPollInterval = setInterval(fetchConversationList, 5000);

    window.addEventListener('beforeunload', () => {
        clearInterval(pollInterval);
        clearInterval(listPollInterval);
    });

    // Send message
    if (messageForm) {
        messageForm.addEventListener('submit', async e => {
            e.preventDefault();

            const message = messageInput.value.trim();
            if (!message) return;

            messageInput.disabled = true;
            sendButton.disabled = true;
            spinner.classList.remove('hidden');

            try {
                const res = await fetch("{{ route('provider.messages.send') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': messageForm._token.value,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        conversation_id: activeConversationId,
                        message: message
                    })
                });

                if (!res.ok) throw new Error('Send failed');

                const data = await res.json();
                const newMsg = data.message;

                appendMessage(newMsg, true);
                scrollToBottom(true); // always scroll when sending
                updateConversationPreview(activeConversationId, newMsg.message, true, newMsg.created_at);
                reorderConversationList(); // move this conversation to top

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
    }

    // Initial scroll
    scrollToBottom();
});
</script>
@endpush
