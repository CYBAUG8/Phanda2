@extends('Providers.layout')

@section('content')
@php
    $hasSelectedConversation = isset($selectedConversation) && $selectedConversation;
    $activeConversationId = $hasSelectedConversation ? $selectedConversation->conversation_id : null;
@endphp

<div class="provider-page-shell space-y-6">
    <section class="provider-page-header">
        <div>
            <h1>Messages</h1>
            <p class="provider-page-subtitle">Respond quickly to customers and keep booking communication in one place.</p>
        </div>
    </section>

    @include('partials.ui.flash')

    <section class="provider-two-pane">
        <aside id="conversationSidebar" class="provider-pane-sidebar {{ $hasSelectedConversation ? 'hidden md:flex' : 'flex' }} flex-col">
            <div class="provider-pane-header space-y-3">
                <h2 class="provider-section-title">Conversations</h2>
                <div>
                    <label for="conversationSearch" class="sr-only">Search conversations</label>
                    <input
                        type="text"
                        id="conversationSearch"
                        placeholder="Search by customer name"
                        class="provider-input"
                    >
                </div>
            </div>

            <div id="conversationSearchEmpty" class="hidden px-4 py-3 text-xs text-slate-500">
                No conversations match your search.
            </div>

            <div id="conversationsList" class="provider-pane-body">
                @forelse($conversations as $conversation)
                    @php
                        $unreadCount = $conversation->messages()
                            ->where('sender_type', '!=', 'provider')
                            ->where('is_read', false)
                            ->count();
                        $lastMsg = $conversation->messages->sortByDesc('created_at')->first();
                        $lastTime = $lastMsg?->created_at ?? $conversation->last_message_time ?? $conversation->created_at;
                    @endphp
                    <a
                        href="{{ route('provider.messages.show', $conversation->conversation_id) }}"
                        data-conversation-id="{{ $conversation->conversation_id }}"
                        data-last-time="{{ optional($lastTime)->toIso8601String() }}"
                        class="conversation-item flex items-center gap-3 border-b border-slate-100 px-4 py-3 no-underline transition hover:bg-slate-100 {{ $activeConversationId === $conversation->conversation_id ? 'bg-orange-50' : '' }}"
                    >
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-orange-500 text-sm font-semibold text-white">
                            {{ strtoupper(substr($conversation->user->full_name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $conversation->user->full_name ?? 'Unknown Customer' }}</p>
                                <span
                                    class="unread-badge {{ $unreadCount > 0 ? 'inline-flex' : 'hidden' }} min-h-5 min-w-5 items-center justify-center rounded-full bg-orange-500 px-1 text-[11px] font-bold text-white"
                                >
                                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                </span>
                            </div>
                            <p class="conversation-preview truncate text-xs {{ $unreadCount > 0 ? 'font-semibold text-slate-700' : 'text-slate-500' }}">
                                @if($lastMsg)
                                    @if($lastMsg->sender_type === 'provider')
                                        You: {{ \Illuminate\Support\Str::limit($lastMsg->message, 34) }}
                                    @else
                                        {{ \Illuminate\Support\Str::limit($lastMsg->message, 34) }}
                                    @endif
                                @else
                                    No messages yet
                                @endif
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="p-4">
                        <div class="provider-empty-inline">
                            No conversations yet. Messages from customers will appear here.
                        </div>
                    </div>
                @endforelse
            </div>
        </aside>

        <section id="threadPane" class="provider-pane-main {{ $hasSelectedConversation ? 'flex' : 'hidden md:flex' }} flex-col">
            @if($hasSelectedConversation)
                @php
                    $lastMsgTs = $selectedConversation->messages->last()?->created_at?->toIso8601String() ?? '';
                    $groupedMessages = $selectedConversation->messages->groupBy(fn($message) => $message->created_at->toDateString());
                @endphp
                <header class="provider-pane-header">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <a href="{{ route('provider.messages') }}" class="ui-btn-secondary px-3 py-2 text-xs md:hidden">
                                <i class="fa-solid fa-arrow-left"></i>
                                <span>Back</span>
                            </a>
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-orange-500 text-sm font-semibold text-white">
                                {{ strtoupper(substr($selectedConversation->user->full_name ?? 'U', 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-semibold text-slate-900">{{ $selectedConversation->user->full_name ?? 'Unknown Customer' }}</h3>
                                <p id="pollStatus" class="text-xs text-slate-500">Syncing messages</p>
                            </div>
                        </div>
                    </div>
                </header>

                <div id="messagesArea" data-last-ts="{{ $lastMsgTs }}" class="provider-pane-body space-y-3 bg-slate-50 px-4 py-4">
                    <div id="threadLoading" class="hidden rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-500">
                        Loading latest messages...
                    </div>

                    @if($selectedConversation->messages->isEmpty())
                        <div class="provider-empty-inline" id="threadEmptyState">
                            No messages yet. Start the conversation below.
                        </div>
                    @else
                        <div id="threadEmptyState" class="hidden provider-empty-inline">No messages yet. Start the conversation below.</div>
                        @foreach($groupedMessages as $date => $messages)
                            <div class="date-separator my-2 flex items-center gap-3" data-date="{{ $date }}">
                                <div class="h-px flex-1 bg-slate-200"></div>
                                <span class="whitespace-nowrap px-2 text-xs font-medium text-slate-500">
                                    @php
                                        $day = \Carbon\Carbon::parse($date);
                                        if ($day->isToday()) {
                                            echo 'Today';
                                        } elseif ($day->isYesterday()) {
                                            echo 'Yesterday';
                                        } else {
                                            echo $day->format('F j, Y');
                                        }
                                    @endphp
                                </span>
                                <div class="h-px flex-1 bg-slate-200"></div>
                            </div>

                            @foreach($messages as $message)
                                @php $isProvider = $message->sender_type === 'provider'; @endphp
                                <div
                                    data-id="{{ $message->message_id }}"
                                    data-sender="{{ $message->sender_type }}"
                                    data-read="{{ $message->is_read ? 'true' : 'false' }}"
                                    class="message-row flex {{ $isProvider ? 'justify-end' : 'justify-start' }}"
                                >
                                    <div class="provider-chat-bubble {{ $isProvider ? 'provider-chat-bubble-out' : 'provider-chat-bubble-in' }}">
                                        <p>{{ $message->message }}</p>
                                        <div class="mt-1 flex items-center gap-1 text-[11px] {{ $isProvider ? 'text-orange-100' : 'text-slate-500' }}">
                                            <span title="{{ $message->created_at }}">{{ $message->created_at->format('H:i') }}</span>
                                            @if($isProvider)
                                                <span class="read-receipt">{!! $message->is_read ? '&#10003;&#10003;' : '&#10003;' !!}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    @endif
                </div>

                <footer class="provider-pane-footer">
                    <form id="messageForm" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="conversation_id" value="{{ $selectedConversation->conversation_id }}">
                        <label for="messageInput" class="sr-only">Type your message</label>
                        <input
                            id="messageInput"
                            type="text"
                            name="message"
                            class="provider-input"
                            placeholder="Type your message..."
                            autocomplete="off"
                            required
                        >
                        <button type="submit" id="sendButton" class="ui-btn-primary min-h-11 justify-center px-4 py-2.5">
                            <i id="sendSpinner" class="fa-solid fa-spinner hidden animate-spin"></i>
                            <span>Send</span>
                        </button>
                    </form>
                </footer>
            @else
                <div class="flex h-full items-center justify-center p-6">
                    <div class="provider-empty-inline w-full max-w-md text-center">
                        Select a conversation to start messaging your customer.
                    </div>
                </div>
            @endif
        </section>
    </section>
</div>
@endsection

@push('styles')
<style>
.message-row.is-new {
    animation: providerMessageIn 180ms ease-out;
}

@keyframes providerMessageIn {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('conversationSearch');
    const conversationsList = document.getElementById('conversationsList');
    const searchEmptyState = document.getElementById('conversationSearchEmpty');

    const messagesArea = document.getElementById('messagesArea');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const sendSpinner = document.getElementById('sendSpinner');
    const threadLoading = document.getElementById('threadLoading');
    const threadEmptyState = document.getElementById('threadEmptyState');
    const pollStatus = document.getElementById('pollStatus');

    const seenMessageIds = new Set();
    messagesArea?.querySelectorAll('[data-id]').forEach((el) => {
        seenMessageIds.add(String(el.getAttribute('data-id')));
    });

    const activeConversationId = messageForm ? String(messageForm.conversation_id.value) : '';
    let lastCreatedAt = messagesArea ? (messagesArea.dataset.lastTs || '') : '';
    let failedPollCount = 0;

    function applySearch() {
        if (!searchInput || !conversationsList) {
            return;
        }

        const term = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        conversationsList.querySelectorAll('a.conversation-item').forEach((item) => {
            const name = (item.querySelector('p')?.textContent || '').toLowerCase();
            const shouldShow = term === '' || name.includes(term);
            item.classList.toggle('hidden', !shouldShow);
            if (shouldShow) {
                visibleCount += 1;
            }
        });

        if (searchEmptyState) {
            searchEmptyState.classList.toggle('hidden', !(term !== '' && visibleCount === 0));
        }
    }

    searchInput?.addEventListener('input', applySearch);

    function escapeHTML(str) {
        return String(str).replace(/[&<>"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[char]));
    }

    function isNearBottom() {
        if (!messagesArea) {
            return true;
        }
        const threshold = 56;
        return messagesArea.scrollHeight - messagesArea.scrollTop - messagesArea.clientHeight < threshold;
    }

    function scrollToBottom(force = false) {
        if (!messagesArea) {
            return;
        }
        if (force || isNearBottom()) {
            messagesArea.scrollTo({ top: messagesArea.scrollHeight, behavior: force ? 'auto' : 'smooth' });
        }
    }

    function formatDateLabel(dateStr) {
        const target = new Date(dateStr);
        const today = new Date();
        const yesterday = new Date();
        yesterday.setDate(today.getDate() - 1);

        const ymd = (date) => date.toISOString().slice(0, 10);
        if (ymd(target) === ymd(today)) {
            return 'Today';
        }
        if (ymd(target) === ymd(yesterday)) {
            return 'Yesterday';
        }

        return target.toLocaleDateString('en-ZA', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    function ensureDateSeparator(dateStr) {
        if (!messagesArea) {
            return;
        }

        let separator = messagesArea.querySelector(`.date-separator[data-date="${dateStr}"]`);
        if (separator) {
            return separator;
        }

        separator = document.createElement('div');
        separator.className = 'date-separator my-2 flex items-center gap-3';
        separator.setAttribute('data-date', dateStr);
        separator.innerHTML = `
            <div class="h-px flex-1 bg-slate-200"></div>
            <span class="whitespace-nowrap px-2 text-xs font-medium text-slate-500">${formatDateLabel(dateStr)}</span>
            <div class="h-px flex-1 bg-slate-200"></div>
        `;
        messagesArea.appendChild(separator);
        return separator;
    }

    function appendMessage(message, isMine) {
        if (!messagesArea) {
            return;
        }

        const messageId = String(message.id);
        if (seenMessageIds.has(messageId)) {
            return;
        }

        const dateStr = (message.created_at || '').slice(0, 10) || new Date().toISOString().slice(0, 10);
        ensureDateSeparator(dateStr);

        const row = document.createElement('div');
        row.className = `message-row is-new flex ${isMine ? 'justify-end' : 'justify-start'}`;
        row.setAttribute('data-id', messageId);
        row.setAttribute('data-sender', message.sender_type || (isMine ? 'provider' : 'customer'));
        row.setAttribute('data-read', message.is_read ? 'true' : 'false');

        const readReceipt = isMine ? `<span class="read-receipt">${message.is_read ? '&#10003;&#10003;' : '&#10003;'}</span>` : '';

        row.innerHTML = `
            <div class="provider-chat-bubble ${isMine ? 'provider-chat-bubble-out' : 'provider-chat-bubble-in'}">
                <p>${escapeHTML(message.message || '')}</p>
                <div class="mt-1 flex items-center gap-1 text-[11px] ${isMine ? 'text-orange-100' : 'text-slate-500'}">
                    <span>${escapeHTML(message.time || '')}</span>
                    ${readReceipt}
                </div>
            </div>
        `;

        messagesArea.appendChild(row);
        seenMessageIds.add(messageId);
        threadEmptyState?.classList.add('hidden');

        if (message.created_at && message.created_at > lastCreatedAt) {
            lastCreatedAt = message.created_at;
        }
    }

    function updateReadReceipts(readMessageIds) {
        if (!messagesArea || !Array.isArray(readMessageIds)) {
            return;
        }

        readMessageIds.forEach((id) => {
            const item = messagesArea.querySelector(`[data-id="${id}"]`);
            if (!item || item.getAttribute('data-read') === 'true') {
                return;
            }
            item.setAttribute('data-read', 'true');
            const badge = item.querySelector('.read-receipt');
            if (badge) {
                badge.innerHTML = '&#10003;&#10003;';
            }
        });
    }

    function setConversationPreview(conversationId, text, isMine, lastTime) {
        if (!conversationsList) {
            return;
        }

        const item = conversationsList.querySelector(`a[data-conversation-id="${conversationId}"]`);
        if (!item) {
            return;
        }

        const preview = item.querySelector('.conversation-preview');
        if (preview) {
            const snippet = String(text || '').slice(0, 34);
            preview.textContent = isMine ? `You: ${snippet}` : snippet || 'No messages yet';
            preview.classList.add('text-slate-700');
        }

        if (lastTime) {
            item.setAttribute('data-last-time', lastTime);
        }
    }

    function setUnreadBadge(conversationId, count) {
        if (!conversationsList) {
            return;
        }

        const item = conversationsList.querySelector(`a[data-conversation-id="${conversationId}"]`);
        if (!item) {
            return;
        }

        const badge = item.querySelector('.unread-badge');
        if (!badge) {
            return;
        }

        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.classList.remove('hidden');
            badge.classList.add('inline-flex');
        } else {
            badge.classList.add('hidden');
            badge.classList.remove('inline-flex');
        }
    }

    function reorderConversations() {
        if (!conversationsList) {
            return;
        }

        const items = Array.from(conversationsList.querySelectorAll('a.conversation-item'));
        items.sort((a, b) => {
            const aTime = a.getAttribute('data-last-time') || '';
            const bTime = b.getAttribute('data-last-time') || '';
            return bTime.localeCompare(aTime);
        });
        items.forEach((item) => conversationsList.appendChild(item));
    }

    async function markConversationRead() {
        if (!activeConversationId) {
            return;
        }

        try {
            await fetch(`/providers/messages/${activeConversationId}/read`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });
            setUnreadBadge(activeConversationId, 0);
        } catch (error) {
            console.error('Failed to mark conversation as read:', error);
        }
    }

    async function fetchMessages() {
        if (!activeConversationId) {
            return;
        }

        threadLoading?.classList.remove('hidden');
        pollStatus && (pollStatus.textContent = 'Syncing messages');
        pollStatus && pollStatus.classList.remove('text-rose-600');
        pollStatus && pollStatus.classList.add('text-slate-500');

        try {
            const query = lastCreatedAt ? `?after=${encodeURIComponent(lastCreatedAt)}` : '';
            const response = await fetch(`/providers/messages/${activeConversationId}/latest${query}`, {
                headers: { 'Accept': 'application/json' },
            });

            if (!response.ok) {
                throw new Error('Unable to load latest messages.');
            }

            const payload = await response.json();
            const messages = Array.isArray(payload.messages) ? payload.messages : [];

            messages.forEach((message) => {
                const isMine = String(message.sender_type) === 'provider';
                appendMessage(message, isMine);
                setConversationPreview(activeConversationId, message.message, isMine, message.created_at);
            });

            updateReadReceipts(payload.read_message_ids || []);
            if (messages.length > 0) {
                reorderConversations();
                scrollToBottom();
                await markConversationRead();
            }

            failedPollCount = 0;
            pollStatus && (pollStatus.textContent = 'Messages are up to date');
        } catch (error) {
            failedPollCount += 1;
            console.error('Message polling error:', error);
            pollStatus && (pollStatus.textContent = 'Connection issue. Retrying...');
            pollStatus && pollStatus.classList.remove('text-slate-500');
            pollStatus && pollStatus.classList.add('text-rose-600');
            if (failedPollCount === 1) {
                window.uiToast('Unable to sync messages right now. Retrying automatically.', 'warning');
            }
        } finally {
            threadLoading?.classList.add('hidden');
        }
    }

    async function fetchConversationList() {
        if (!conversationsList) {
            return;
        }

        try {
            const response = await fetch('/providers/messages/list', {
                headers: { 'Accept': 'application/json' },
            });
            if (!response.ok) {
                throw new Error('Unable to refresh conversation list.');
            }

            const payload = await response.json();
            const conversations = Array.isArray(payload.conversations) ? payload.conversations : [];

            conversations.forEach((conversation) => {
                const id = String(conversation.id);
                if (id !== activeConversationId) {
                    setUnreadBadge(id, Number(conversation.unread_count || 0));
                }
                setConversationPreview(id, conversation.last_message || '', conversation.last_sender === 'provider', conversation.last_time || '');
            });

            reorderConversations();
            applySearch();
        } catch (error) {
            console.error('Conversation polling error:', error);
        }
    }

    if (activeConversationId) {
        markConversationRead();
        scrollToBottom(true);
    }

    const messagePollTimer = activeConversationId ? setInterval(fetchMessages, 4000) : null;
    const conversationPollTimer = setInterval(fetchConversationList, 7000);

    window.addEventListener('beforeunload', () => {
        if (messagePollTimer) {
            clearInterval(messagePollTimer);
        }
        clearInterval(conversationPollTimer);
    });

    messageForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const text = String(messageInput.value || '').trim();
        if (!text) {
            return;
        }

        sendButton.disabled = true;
        messageInput.disabled = true;
        sendSpinner?.classList.remove('hidden');

        try {
            const response = await fetch('{{ route('provider.messages.send') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    conversation_id: activeConversationId,
                    message: text,
                }),
            });

            if (!response.ok) {
                throw new Error('Failed to send message.');
            }

            const payload = await response.json();
            appendMessage(payload.message, true);
            setConversationPreview(activeConversationId, payload.message?.message || text, true, payload.message?.created_at || '');
            reorderConversations();
            scrollToBottom(true);
            messageInput.value = '';
            messageInput.focus();
        } catch (error) {
            console.error('Send message error:', error);
            window.uiToast('Message could not be sent. Please try again.', 'error');
        } finally {
            sendSpinner?.classList.add('hidden');
            sendButton.disabled = false;
            messageInput.disabled = false;
        }
    });
});
</script>
@endpush
