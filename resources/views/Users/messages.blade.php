@extends('Users.layout')

@section('content')
@php
    $hasSelectedConversation = isset($selectedConversation) && $selectedConversation;
    $activeConversationId = $hasSelectedConversation ? $selectedConversation->conversation_id : null;
@endphp

<div class="user-page-shell space-y-6">
    <section class="user-page-header">
        <div>
            <h1>Messages</h1>
            <p class="user-page-subtitle">Stay in touch with providers and keep your booking communication in one place.</p>
        </div>
    </section>

    @include('partials.ui.flash')

    <section class="user-two-pane">
        <aside id="conversationSidebar" class="user-pane-sidebar {{ $hasSelectedConversation ? 'hidden md:flex' : 'flex' }} flex-col">
            <div class="user-pane-header space-y-3">
                <h2 class="user-section-title">Conversations</h2>
                <div>
                    <label for="conversationSearch" class="sr-only">Search conversations</label>
                    <input
                        type="text"
                        id="conversationSearch"
                        placeholder="Search by provider name"
                        class="user-input"
                    >
                </div>
            </div>

            <div id="conversationSearchEmpty" class="hidden px-4 py-3 text-xs text-slate-500">
                No conversations match your search.
            </div>

            <div id="conversationsList" class="user-pane-body">
                @forelse($conversations as $conversation)
                    @php
                        $unreadCount = $conversation->messages
                            ->where('sender_type', '!=', 'customer')
                            ->where('is_read', false)
                            ->count();
                        $lastMsg = $conversation->messages->sortByDesc('created_at')->first();
                        $lastTime = $lastMsg?->created_at ?? $conversation->created_at;
                    @endphp
                    <a
                        href="{{ route('user.messages.show', $conversation->conversation_id) }}"
                        data-conversation-id="{{ $conversation->conversation_id }}"
                        data-last-time="{{ optional($lastTime)->toIso8601String() }}"
                        class="conversation-item flex items-center gap-3 border-b border-slate-100 px-4 py-3 no-underline transition hover:bg-slate-100 {{ $activeConversationId === $conversation->conversation_id ? 'bg-orange-50' : '' }}"
                    >
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-orange-500 text-sm font-semibold text-white">
                            {{ strtoupper(substr($conversation->provider->user->full_name ?? 'P', 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="truncate text-sm font-semibold text-slate-900 {{ $unreadCount > 0 ? 'font-semibold' : '' }}">
                                    {{ $conversation->provider->user->full_name ?? 'Unknown' }}
                                </h3>
                                @if($unreadCount > 0)
                                    <span class="unread-badge ml-2 min-h-5 min-w-5 rounded-full bg-orange-500 px-1 text-xs font-bold text-white inline-flex items-center justify-center">
                                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                    </span>
                                @else
                                    <span class="unread-badge ml-2 min-h-5 min-w-5 rounded-full bg-orange-500 px-1 text-xs font-bold text-white hidden items-center justify-center">0</span>
                                @endif
                            </div>
                            <p class="conversation-preview truncate text-xs text-slate-500 {{ $unreadCount > 0 ? 'font-semibold text-slate-700' : '' }}">
                                @if($lastMsg)
                                    @if($lastMsg->sender_type === 'customer')
                                        <span class="text-slate-400">You: </span>{{ \Illuminate\Support\Str::limit($lastMsg->message, 25) }}
                                    @else
                                        {{ \Illuminate\Support\Str::limit($lastMsg->message, 25) }}
                                    @endif
                                @else
                                    <span class="text-slate-300">No messages yet</span>
                                @endif
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="p-4">
                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-3 text-sm text-slate-500">
                            No conversations yet.
                        </div>
                    </div>
                @endforelse
            </div>
        </aside>

        <section id="threadPane" class="user-pane-main {{ $hasSelectedConversation ? 'flex' : 'hidden md:flex' }} flex-col">
            @if($selectedConversation ?? false)
                @php
                    $lastMsgTs = $selectedConversation->messages->last()?->created_at?->toIso8601String() ?? '';
                    $groupedMessages = $selectedConversation->messages->groupBy(fn($m) => $m->created_at->toDateString());
                @endphp
                <header class="user-pane-header">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <a href="{{ route('user.messages') }}" class="ui-btn-secondary px-3 py-2 text-xs md:hidden">
                                <i class="fa-solid fa-arrow-left"></i>
                                <span>Back</span>
                            </a>
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-orange-500 text-sm font-semibold text-white">
                                {{ strtoupper(substr($selectedConversation->provider->user->full_name ?? 'P', 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-semibold text-slate-900">
                                    {{ $selectedConversation->provider->user->full_name ?? 'Provider' }}
                                </h3>
                                <p class="text-xs text-slate-500">Conversation active</p>
                            </div>
                        </div>
                    </div>
                </header>

                <div id="messagesArea" data-last-ts="{{ $lastMsgTs }}" class="user-pane-body space-y-3 bg-slate-50 px-4 py-4" aria-live="polite">
                    @foreach($groupedMessages as $date => $messages)
                        <div class="date-separator my-2 flex items-center gap-3" data-date="{{ $date }}">
                            <div class="h-px flex-1 bg-slate-200"></div>
                            <span class="whitespace-nowrap px-2 text-xs font-medium text-slate-500">
                                @php
                                    $d = \Carbon\Carbon::parse($date);
                                    if ($d->isToday()) echo 'Today';
                                    elseif ($d->isYesterday()) echo 'Yesterday';
                                    else echo $d->format('F j, Y');
                                @endphp
                            </span>
                            <div class="h-px flex-1 bg-slate-200"></div>
                        </div>

                        @foreach($messages as $message)
                            @php $isUser = $message->sender_type === 'customer'; @endphp
                            <div data-id="{{ $message->message_id }}"
                                 data-sender="{{ $message->sender_type }}"
                                 data-read="{{ $message->is_read ? 'true' : 'false' }}"
                                 class="flex {{ $isUser ? 'justify-end' : 'justify-start' }}">
                                <div class="user-chat-bubble {{ $isUser ? 'user-chat-bubble-out' : 'user-chat-bubble-in' }}">
                                    <p class="text-sm">{{ $message->message }}</p>
                                    <div class="mt-1 flex items-center gap-1">
                                        <span class="{{ $isUser ? 'text-xs text-orange-100' : 'text-xs text-slate-500' }}"
                                              title="{{ $message->created_at }}">
                                            {{ $message->created_at->format('H:i') }}
                                        </span>
                                        @if($isUser)
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

                <footer class="user-pane-footer">
                    <form id="messageForm" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="conversation_id" value="{{ $selectedConversation->conversation_id }}">
                        <label for="messageInput" class="sr-only">Type your message</label>
                        <input
                            id="messageInput"
                            type="text"
                            name="message"
                            placeholder="Type your message..."
                            class="user-input"
                            required
                        >
                        <button type="submit" id="sendButton" class="ui-btn-primary min-h-11 justify-center px-4 py-2.5">
                            <svg id="sendSpinner" class="hidden w-4 h-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Send</span>
                        </button>
                    </form>
                </footer>
            @else
                <div class="flex h-full items-center justify-center p-6">
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-center text-sm text-slate-500">
                        Select a conversation to start messaging your provider.
                    </div>
                </div>
            @endif
        </section>
    </section>
</div>

<div id="toast" class="fixed bottom-4 right-4 z-50 hidden rounded-lg bg-rose-500 px-4 py-2 text-white shadow-lg transition-opacity duration-300"></div>

@push('styles')
<style>
    .fade-in { animation: fadeIn 0.3s ease-in; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ----- Search / Filter Conversations -----
    const searchInput = document.getElementById('conversationSearch');
    const conversationsList = document.getElementById('conversationsList');
    const searchEmptyState = document.getElementById('conversationSearchEmpty');

    function applySearch() {
        if (!searchInput || !conversationsList) return;
        const term = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;
        conversationsList.querySelectorAll('a.conversation-item').forEach(link => {
            const name = (link.querySelector('h3')?.textContent ?? '').toLowerCase();
            const isVisible = term === '' || name.includes(term);
            link.style.display = isVisible ? 'flex' : 'none';
            if (isVisible) {
                visibleCount += 1;
            }
        });

        if (searchEmptyState) {
            if (term !== '' && visibleCount === 0) {
                searchEmptyState.classList.remove('hidden');
            } else {
                searchEmptyState.classList.add('hidden');
            }
        }
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
        div.setAttribute('data-sender', msg.sender_type || (isMine ? 'customer' : 'provider'));
        div.setAttribute('data-read', msg.is_read ? 'true' : 'false'); // ✅ use actual read status
        div.className = `flex ${isMine ? 'justify-end' : 'items-start'} fade-in`;

        const tick = msg.is_read ? doubleTickSVG : singleTickSVG; // ✅ show correct tick immediately

        div.innerHTML = `
            <div class="user-chat-bubble ${isMine ? 'user-chat-bubble-out' : 'user-chat-bubble-in'}">
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
            await fetch(`/users/messages/${conversationId}/read`, {
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
            const url = `/users/messages/${activeConversationId}/latest`
                + (lastCreatedAt ? `?after=${encodeURIComponent(lastCreatedAt)}` : '');

            const res = await fetch(url);
            if (!res.ok) return;
            const data = await res.json();

            requestAnimationFrame(() => {
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        const isMine = msg.sender_type === 'customer';
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
            const res = await fetch('/users/messages/list', {
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
                updateConversationPreview(convId, conv.last_message, conv.last_sender === 'customer', conv.last_time);
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
                const res = await fetch("{{ route('user.messages.send') }}", {
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
