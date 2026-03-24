@extends('Providers.layout')

@section('content')
@push('styles')
<style>
.provider-calendar-shell .fc .fc-toolbar-title {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
}

.provider-calendar-shell .fc-theme-standard td,
.provider-calendar-shell .fc-theme-standard th,
.provider-calendar-shell .fc-scrollgrid {
    border-color: #e2e8f0;
}

.provider-calendar-shell .fc .fc-button {
    border-radius: 0.65rem;
    border: 1px solid #ea580c;
    background: #f97316;
    color: #fff;
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.4rem 0.65rem;
}

.provider-calendar-shell .fc .fc-button:hover {
    background: #ea580c;
    border-color: #c2410c;
}

.provider-calendar-shell .fc .fc-button:disabled {
    opacity: 0.5;
}

.provider-calendar-shell .fc .fc-event {
    border-width: 0;
    border-radius: 0.55rem;
    padding: 2px 4px;
    font-size: 0.75rem;
}
</style>
@endpush

<div class="provider-page-shell space-y-6">
    <section class="provider-page-header">
        <div>
            <h1>Schedule</h1>
            <p class="provider-page-subtitle">Manage confirmed and in-progress bookings in your calendar timeline.</p>
        </div>
    </section>

    @include('partials.ui.flash')

    <section class="ui-card p-5">
        <div class="mb-4 flex flex-wrap items-center gap-2 text-xs text-slate-500">
            <span class="provider-status-badge provider-status-confirmed">Confirmed</span>
            <span class="provider-status-badge provider-status-in-progress">In Progress</span>
            <span class="provider-status-badge provider-status-completed">Completed</span>
            <span class="provider-status-badge provider-status-cancelled">Cancelled</span>
            <span class="provider-status-badge provider-status-paused">Expired</span>
        </div>

        <div class="provider-calendar-shell relative">
            <div id="calendarLoadingState" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                Loading schedule...
            </div>

            <div id="calendarErrorState" class="hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                Unable to load your schedule right now.
                <button type="button" id="retryCalendarBtn" class="ml-2 ui-btn-secondary px-3 py-1.5 text-xs">Retry</button>
            </div>

            <div id="calendarEmptyState" class="hidden rounded-xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm text-slate-600">
                No bookings are scheduled yet.
            </div>

            <div id="calendarWrap" class="hidden">
                <div id="calendar" class="min-h-[640px]"></div>
            </div>
        </div>
    </section>
</div>

<div
    id="eventModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="bookingDetailsTitle"
>
    <div class="flex min-h-full items-center justify-center">
        <div class="fixed inset-0 bg-slate-950/50" data-close-event-modal></div>
        <div class="provider-modal-panel relative z-10 w-full max-w-2xl">
            <div class="provider-modal-header">
                <div>
                    <h3 id="bookingDetailsTitle" class="text-lg font-semibold text-slate-900">Booking Details</h3>
                    <p class="mt-1 text-xs text-slate-500">Review details and update status when allowed.</p>
                </div>
                <button type="button" class="text-slate-500 hover:text-slate-700" data-close-event-modal aria-label="Close booking details">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="provider-modal-body">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h4 id="modalService" class="text-base font-semibold text-slate-900">Service</h4>
                    <span id="modalStatusBadge" class="provider-status-badge provider-status-paused">Status</span>
                </div>

                <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="provider-label normal-case tracking-normal text-slate-500">Customer</dt>
                        <dd id="modalCustomer" class="font-semibold text-slate-900">-</dd>
                    </div>
                    <div>
                        <dt class="provider-label normal-case tracking-normal text-slate-500">Phone</dt>
                        <dd id="modalPhone" class="font-semibold text-slate-900">-</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="provider-label normal-case tracking-normal text-slate-500">Address</dt>
                        <dd id="modalAddress" class="font-semibold text-slate-900">-</dd>
                    </div>
                    <div>
                        <dt class="provider-label normal-case tracking-normal text-slate-500">Date</dt>
                        <dd id="modalDate" class="font-semibold text-slate-900">-</dd>
                    </div>
                    <div>
                        <dt class="provider-label normal-case tracking-normal text-slate-500">Time</dt>
                        <dd id="modalTime" class="font-semibold text-slate-900">-</dd>
                    </div>
                    <div>
                        <dt class="provider-label normal-case tracking-normal text-slate-500">Duration</dt>
                        <dd id="modalDuration" class="font-semibold text-slate-900">-</dd>
                    </div>
                    <div>
                        <dt class="provider-label normal-case tracking-normal text-slate-500">Total Price</dt>
                        <dd id="modalPrice" class="font-semibold text-slate-900">-</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="provider-label normal-case tracking-normal text-slate-500">Notes</dt>
                        <dd id="modalNotes" class="font-semibold text-slate-900">No notes provided</dd>
                    </div>
                </dl>

                <p id="actionReason" class="mt-4 hidden rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600"></p>
            </div>

            <div class="provider-modal-footer">
                <a id="messageCustomerBtn" href="#" class="ui-btn-secondary hidden">
                    <i class="fa-solid fa-comment"></i>
                    <span>Message Customer</span>
                </a>
                <button id="confirmBtn" type="button" class="ui-btn-secondary px-4 py-2" data-status-action="confirmed">Confirm</button>
                <button id="startBtn" type="button" class="ui-btn-primary px-4 py-2" data-status-action="in_progress">Start</button>
                <button id="completeBtn" type="button" class="ui-btn-primary px-4 py-2" data-status-action="completed">Mark Completed</button>
                <button id="cancelBtn" type="button" class="ui-btn-danger px-4 py-2" data-status-action="cancelled">Cancel Booking</button>
                <button type="button" class="ui-btn-secondary px-4 py-2" data-close-event-modal>Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const loadingState = document.getElementById('calendarLoadingState');
    const errorState = document.getElementById('calendarErrorState');
    const emptyState = document.getElementById('calendarEmptyState');
    const calendarWrap = document.getElementById('calendarWrap');
    const retryCalendarBtn = document.getElementById('retryCalendarBtn');
    const eventModal = document.getElementById('eventModal');
    const actionReason = document.getElementById('actionReason');

    const modalFields = {
        service: document.getElementById('modalService'),
        statusBadge: document.getElementById('modalStatusBadge'),
        customer: document.getElementById('modalCustomer'),
        phone: document.getElementById('modalPhone'),
        address: document.getElementById('modalAddress'),
        date: document.getElementById('modalDate'),
        time: document.getElementById('modalTime'),
        duration: document.getElementById('modalDuration'),
        price: document.getElementById('modalPrice'),
        notes: document.getElementById('modalNotes'),
        messageBtn: document.getElementById('messageCustomerBtn'),
    };

    const actionButtons = {
        confirmed: document.getElementById('confirmBtn'),
        in_progress: document.getElementById('startBtn'),
        completed: document.getElementById('completeBtn'),
        cancelled: document.getElementById('cancelBtn'),
    };

    const statusClassMap = {
        pending: 'provider-status-pending',
        confirmed: 'provider-status-confirmed',
        in_progress: 'provider-status-in-progress',
        completed: 'provider-status-completed',
        cancelled: 'provider-status-cancelled',
        expired: 'provider-status-paused',
    };

    let currentEvent = null;
    let isActionLoading = false;

    function showLoading() {
        loadingState?.classList.remove('hidden');
        errorState?.classList.add('hidden');
        emptyState?.classList.add('hidden');
        calendarWrap?.classList.add('hidden');
    }

    function showError() {
        loadingState?.classList.add('hidden');
        errorState?.classList.remove('hidden');
        emptyState?.classList.add('hidden');
        calendarWrap?.classList.add('hidden');
    }

    function showCalendar(hasEvents) {
        loadingState?.classList.add('hidden');
        errorState?.classList.add('hidden');
        emptyState?.classList.toggle('hidden', hasEvents);
        calendarWrap?.classList.remove('hidden');
    }

    function openEventModal() {
        eventModal?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeEventModal() {
        eventModal?.classList.add('hidden');
        document.body.style.overflow = '';
        currentEvent = null;
    }

    function setModalStatusBadge(status, label) {
        const normalized = status === 'cancelled' && currentEvent?.extendedProps?.cancellation_reason === 'expired'
            ? 'expired'
            : status;
        modalFields.statusBadge.className = `provider-status-badge ${statusClassMap[normalized] || 'provider-status-paused'}`;
        modalFields.statusBadge.textContent = label;
    }

    function computeDuration(start, end) {
        if (!start || !end) {
            return '-';
        }
        const diffMinutes = Math.round((end.getTime() - start.getTime()) / 60000);
        if (diffMinutes <= 0) {
            return '-';
        }
        const hours = Math.floor(diffMinutes / 60);
        const minutes = diffMinutes % 60;
        if (hours > 0 && minutes > 0) {
            return `${hours}h ${minutes}m`;
        }
        if (hours > 0) {
            return `${hours}h`;
        }
        return `${minutes}m`;
    }

    function setActionState(status, paymentStatus, isExpired) {
        const stateMap = {
            confirmed: status === 'pending' && !isExpired,
            in_progress: status === 'confirmed' && paymentStatus === 'paid' && !isExpired,
            completed: status === 'in_progress' && !isExpired,
            cancelled: ['pending', 'confirmed'].includes(status) && !isExpired,
        };

        Object.entries(actionButtons).forEach(([key, button]) => {
            const enabled = Boolean(stateMap[key]);
            button.disabled = !enabled || isActionLoading;
            button.classList.toggle('opacity-60', !enabled || isActionLoading);
        });

        let reason = '';
        if (isExpired) {
            reason = 'This booking has expired and can no longer be updated.';
        } else if (status === 'confirmed' && paymentStatus !== 'paid') {
            reason = 'Start is disabled until user payment is marked as paid.';
        }

        actionReason.textContent = reason;
        actionReason.classList.toggle('hidden', reason === '');
    }

    function hydrateModal(event) {
        const props = event.extendedProps || {};
        const status = props.status || 'pending';
        const paymentStatus = props.payment_status || 'unpaid';
        const isExpired = status === 'cancelled' && props.cancellation_reason === 'expired';

        modalFields.service.textContent = event.title || 'Service';
        setModalStatusBadge(status, props.status_label || status);
        modalFields.customer.textContent = props.customer_name || '-';
        modalFields.phone.textContent = props.phone || '-';
        modalFields.address.textContent = props.address || '-';
        modalFields.notes.textContent = props.notes || 'No notes provided';
        modalFields.price.textContent = props.price ? `R ${props.price}` : '-';

        if (event.start) {
            modalFields.date.textContent = event.start.toLocaleDateString('en-ZA', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
            });
            const endText = event.end ? event.end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '--:--';
            modalFields.time.textContent = `${event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} - ${endText}`;
        } else {
            modalFields.date.textContent = '-';
            modalFields.time.textContent = '-';
        }

        modalFields.duration.textContent = computeDuration(event.start, event.end);

        if (props.customer_id) {
            modalFields.messageBtn.href = `/providers/messages/start/${props.customer_id}`;
            modalFields.messageBtn.classList.remove('hidden');
        } else {
            modalFields.messageBtn.classList.add('hidden');
        }

        setActionState(status, paymentStatus, isExpired);
    }

    async function updateStatus(nextStatus) {
        if (!currentEvent || isActionLoading) {
            return;
        }

        if (nextStatus === 'cancelled') {
            const approved = await window.uiConfirm({
                title: 'Cancel booking',
                message: 'Are you sure you want to cancel this booking?',
                confirmText: 'Cancel booking',
                variant: 'danger',
            });
            if (!approved) {
                return;
            }
        }

        isActionLoading = true;
        setActionState(
            currentEvent.extendedProps?.status || '',
            currentEvent.extendedProps?.payment_status || '',
            currentEvent.extendedProps?.cancellation_reason === 'expired'
        );

        try {
            const response = await fetch(`/provider/calendar/${currentEvent.id}/status`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ status: nextStatus }),
            });

            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Failed to update booking status.');
            }

            window.uiToast(payload.message || 'Booking updated.', 'success');
            calendar.refetchEvents();
            closeEventModal();
        } catch (error) {
            console.error(error);
            window.uiToast(error.message || 'Error updating booking.', 'error');
        } finally {
            isActionLoading = false;
        }
    }

    document.querySelectorAll('[data-close-event-modal]').forEach((button) => {
        button.addEventListener('click', closeEventModal);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !eventModal.classList.contains('hidden')) {
            closeEventModal();
        }
    });

    Object.entries(actionButtons).forEach(([status, button]) => {
        button.addEventListener('click', () => updateStatus(status));
    });

    const calendarEl = document.getElementById('calendar');
    let eventCount = 0;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        slotMinTime: '08:00:00',
        slotMaxTime: '18:00:00',
        allDaySlot: false,
        height: 'auto',
        timeZone: 'local',
        events(fetchInfo, successCallback, failureCallback) {
            showLoading();
            fetch('/provider/calendar/events', {
                headers: { 'Accept': 'application/json' },
            })
                .then(async (response) => {
                    if (!response.ok) {
                        throw new Error('Unable to fetch events.');
                    }
                    const payload = await response.json();
                    const events = Array.isArray(payload) ? payload : [];
                    eventCount = events.length;
                    showCalendar(eventCount > 0);
                    successCallback(events);
                })
                .catch((error) => {
                    console.error(error);
                    showError();
                    failureCallback(error);
                });
        },
        eventClick(info) {
            currentEvent = info.event;
            hydrateModal(info.event);
            openEventModal();
        },
        dateClick() {
            window.uiToast('Manual block-time controls are coming soon.', 'info');
        },
    });

    retryCalendarBtn?.addEventListener('click', () => {
        showLoading();
        calendar.refetchEvents();
    });

    calendar.render();
})();
</script>
@endpush
@endsection
