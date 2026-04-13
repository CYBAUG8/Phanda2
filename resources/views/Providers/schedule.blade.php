@extends('Providers.layout')

@section('content')
<<<<<<< HEAD

{{-- Add CSRF meta tag if not already in layout --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

@push('styles')
<style>
/* Sidebar card */
.sidebar-card {
  background-color: white;
  border-right: 1px solid #f3f4f6;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

/* Calendar card */
.calendar-card {
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  transition: box-shadow 0.3s ease;
}
.calendar-card:hover {
  box-shadow: 0 4px 8px rgba(0,0,0,0.08);
}

/* Modal animation */
.modal-transition {
  transition: opacity 0.2s ease, visibility 0.2s ease;
}

.fc-theme-standard .fc-toolbar .fc-button {
  background-color: #f97316 !important;
  border-color: #f97316 !important;
  color: white !important;
  border-radius: 0.5rem !important;
}

.fc-theme-standard .fc-toolbar .fc-button:hover {
  background-color: #ea580c !important;
}

/* Disabled button styling */
button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
=======
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
>>>>>>> feature2
}
</style>
@endpush

<<<<<<< HEAD
<div class="flex min-h-screen">
  <div class="flex-1 w-full">
    <main class="p-4 sm:p-6 md:p-10 space-y-6 md:space-y-8">
      <section class="calendar-card p-4 sm:p-6">
        {{-- Heading --}}
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4">Schedule Calendar</h1>

        {{-- Color key / legend --}}
        <div class="flex flex-wrap items-center gap-4 mb-4 text-sm">
          <span class="font-medium text-gray-600">Status:</span>
          <div class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-gray-400"></span> Pending</div>
          <div class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-orange-500"></span> Confirmed</div>
          <div class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-green-500"></span> Completed</div>
          <div class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-red-500"></span> Cancelled</div>
        </div>
        <div id="calendar" class="h-[700px] md:h-[800px]"></div>
      </section>
    </main>
  </div>
</div>

<!-- MODAL: EVENT DETAILS -->
<div id="eventModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 invisible opacity-0 modal-transition" style="background-color: rgba(0,0,0,0.4);">
  <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[90vh] overflow-y-auto">
    <div class="flex justify-between items-center p-5 border-b border-gray-100">
      <h3 class="text-lg font-semibold text-orange-500">Booking details</h3>
      <button id="closeEventModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
    </div>
    <div class="p-5 space-y-4">
      <div class="flex items-center justify-between">
        <span id="modalService" class="text-lg font-bold text-gray-800">Deep Cleaning</span>
        <span id="modalStatusBadge" class="px-3 py-1 text-xs font-semibold rounded-full">Confirmed</span>
      </div>
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div><span class="text-gray-400 block">Customer</span><span id="modalCustomer" class="font-medium">John Doe</span></div>
        <div><span class="text-gray-400 block">Phone</span><span id="modalPhone" class="font-medium">0723456789</span></div>
      </div>
      <div><span class="text-gray-400 text-sm">Address</span><p id="modalAddress" class="font-medium bg-gray-50 p-2 rounded-lg">12 Main Road, Johannesburg</p></div>
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div><span class="text-gray-400 block">Date</span><span id="modalDate" class="font-medium">17 Feb 2026</span></div>
        <div><span class="text-gray-400 block">Time</span><span id="modalTime" class="font-medium">10:00 – 12:00</span></div>
      </div>
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div><span class="text-gray-400 block">Duration</span><span id="modalDuration" class="font-medium">2 hours</span></div>
        <div><span class="text-gray-400 block">Total price</span><span id="modalPrice" class="font-bold text-orange-600">R 450</span></div>
      </div>
      <div><span class="text-gray-400 text-sm">Notes</span><p id="modalNotes" class="text-gray-700 bg-gray-50 p-3 rounded-lg text-sm">Please bring eco-friendly products.</p></div>
    </div>
    <div class="p-5 border-t border-gray-100 flex flex-wrap gap-3 justify-end">
      <button id="confirmBtn" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition" onclick="updateStatus('confirmed')">Confirm</button>
      <button id="completeBtn" class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm" onclick="updateStatus('completed')">✓ Completed</button>
      <button id="messageCustomerBtn"
              class="px-4 py-2 text-sm border border-blue-300 text-blue-600 rounded-lg hover:bg-blue-50 transition"
              data-customer-id=""
              data-booking-id="">
              💬 Message
      </button>
      <button id="cancelBtn" class="px-4 py-2 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600 transition" onclick="updateStatus('cancelled')">Cancel</button>
      <button id="closeEventModal2" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Close</button>
    </div>
  </div>
</div>

<!-- MODAL: BLOCK TIME / MANUAL -->
<div id="blockModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 invisible opacity-0 modal-transition" style="background-color: rgba(0,0,0,0.4);">
  <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-md">
    <div class="flex justify-between items-center p-5 border-b border-gray-100">
      <h3 class="text-lg font-semibold text-orange-500">Block time / manual</h3>
      <button id="closeBlockModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
    </div>
    <div class="p-5 space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
        <input type="text" id="blockDate" readonly value="2026-02-17" class="w-full border border-gray-300 rounded-lg p-3 bg-gray-50 text-gray-700">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Start time</label>
        <input type="text" id="blockStartTime" readonly value="14:00" class="w-full border border-gray-300 rounded-lg p-3 bg-gray-50 text-gray-700">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
        <select class="w-full border border-gray-300 rounded-lg p-3 text-gray-700 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
          <option>30 min</option>
          <option>1 hour</option>
          <option>2 hours</option>
          <option>3 hours</option>
          <option>4 hours</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Reason (optional)</label>
        <textarea rows="2" class="w-full border border-gray-300 rounded-lg p-3 text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="e.g. lunch break, offsite"></textarea>
      </div>
    </div>
    <div class="p-5 border-t border-gray-100 flex gap-3 justify-end">
      <button class="px-5 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition shadow-sm">Save block</button>
      <button id="closeBlockModal2" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Cancel</button>
      
    </div>
  </div>
</div>

<script>
(function() {
=======
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
>>>>>>> feature2

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

<<<<<<< HEAD
    function closeBlockModal() {
        document.getElementById('blockModal').classList.add('invisible', 'opacity-0');
    }

    // Attach close events for event modal
    document.getElementById('closeEventModal').addEventListener('click', closeEventModal);
    document.getElementById('closeEventModal2').addEventListener('click', closeEventModal);
    // Close when clicking outside modal content
    document.getElementById('eventModal').addEventListener('click', function(e) {
        if (e.target === this) closeEventModal();
    });

    // Attach close events for block modal
    document.getElementById('closeBlockModal').addEventListener('click', closeBlockModal);
    document.getElementById('closeBlockModal2').addEventListener('click', closeBlockModal);
    document.getElementById('blockModal').addEventListener('click', function(e) {
        if (e.target === this) closeBlockModal();
    });

    // ---------- CALENDAR INITIALIZATION ----------
    let currentEventId = null; // track the clicked event

    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        slotMinTime: '08:00:00',
        slotMaxTime: '18:00:00',
        allDaySlot: false,
        height: 'auto',
        timeZone: 'local',
        events: {
            url: '/provider/calendar/events',
            method: 'GET',
            failure: function() {
                alert('There was an error loading bookings.');
            }
        },
        eventDataTransform: function(event) {
            // Apply color based on status
            const status = event.extendedProps?.status;
            let color;
            switch (status) {
                case 'pending': color = '#9ca3af'; break;   // gray-400
                case 'confirmed': color = '#f97316'; break; // orange-500
                case 'completed': color = '#10b981'; break; // green-500
                case 'cancelled': color = '#ef4444'; break; // red-500
                default: color = '#9ca3af'; // default gray
            }
            event.backgroundColor = color;
            event.borderColor = color;
            event.textColor = '#ffffff';
            return event;
        },
        eventClick: function(info) {
            const e = info.event;
            const ext = e.extendedProps;
            const currentStatus = ext.status || 'confirmed';

            currentEventId = e.id;
            // Set message button data
            const msgBtn = document.getElementById('messageCustomerBtn');
            if (ext.customer_id) {
                msgBtn.setAttribute('data-customer-id', ext.customer_id);
                msgBtn.setAttribute('data-booking-id', e.id);
                msgBtn.disabled = false;
               } else {
                       msgBtn.disabled = true;
                  }
            // Populate modal fields
            document.getElementById('modalService').innerText = e.title;

            const badge = document.getElementById('modalStatusBadge');
            badge.innerText = currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1);
            badge.className = 'px-3 py-1 text-xs font-semibold rounded-full';
            if (currentStatus === 'confirmed') badge.classList.add('bg-orange-100', 'text-orange-700');
            else if (currentStatus === 'pending') badge.classList.add('bg-gray-200', 'text-gray-700');
            else if (currentStatus === 'completed') badge.classList.add('bg-green-100', 'text-green-700');
            else if (currentStatus === 'cancelled') badge.classList.add('bg-red-100', 'text-red-700');

            document.getElementById('modalCustomer').innerText = ext.customer_name || '—';
            document.getElementById('modalPhone').innerText = ext.phone || '—';
            document.getElementById('modalAddress').innerText = ext.address || '—';
            document.getElementById('modalNotes').innerText = ext.notes || '—';
            document.getElementById('modalPrice').innerText = ext.price ? 'R ' + ext.price : '—';

            const start = e.start;
            const end = e.end;

            if (start) {
                const dateStr = start.toLocaleDateString('en-ZA', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
                document.getElementById('modalDate').innerText = dateStr;

                const timeStr = start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) +
                    ' – ' +
                    (end ? end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '');
                document.getElementById('modalTime').innerText = timeStr;
            }

            if (start && end) {
                const diffMs = end - start;
                const diffHrs = diffMs / (1000 * 60 * 60);
                const hrs = Math.floor(diffHrs);
                const mins = Math.round((diffHrs - hrs) * 60);
                let durText = '';
                if (hrs > 0) durText += hrs + 'h ';
                if (mins > 0) durText += mins + 'm';
                document.getElementById('modalDuration').innerText = durText || '—';
            } else {
                document.getElementById('modalDuration').innerText = '—';
            }

            // Enable/disable action buttons based on current status
            const confirmBtn = document.getElementById('confirmBtn');
            const completeBtn = document.getElementById('completeBtn');
            const cancelBtn = document.getElementById('cancelBtn');

            // Reset all to enabled first
            confirmBtn.disabled = false;
            completeBtn.disabled = false;
            cancelBtn.disabled = false;

            // Apply logic:
            // - Confirm button only enabled when status is 'pending'
            // - Complete button only enabled when status is 'confirmed'
            // - Cancel button always enabled (optional: disable if already cancelled/completed)
            if (currentStatus !== 'pending') {
                confirmBtn.disabled = true;
            }
            if (currentStatus !== 'confirmed') {
                completeBtn.disabled = true;
            }
            if (currentStatus === 'cancelled' || currentStatus === 'completed') {
                // Optionally disable cancel if already cancelled/completed
                cancelBtn.disabled = true;
            }

            // Show the modal
            document.getElementById('eventModal').classList.remove('invisible', 'opacity-0');
        },
        dateClick: function(info) {
            const clickedDate = info.date;
            const year = clickedDate.getFullYear();
            const month = String(clickedDate.getMonth() + 1).padStart(2, '0');
            const day = String(clickedDate.getDate()).padStart(2, '0');
            document.getElementById('blockDate').value = `${year}-${month}-${day}`;

            const hours = String(clickedDate.getHours()).padStart(2, '0');
            const mins = String(clickedDate.getMinutes()).padStart(2, '0');
            document.getElementById('blockStartTime').value = `${hours}:${mins}`;

            document.getElementById('blockModal').classList.remove('invisible', 'opacity-0');
        }
    });

    calendar.render();

    document.getElementById('messageCustomerBtn').addEventListener('click', function() {
    const customerId = this.getAttribute('data-customer-id');
    if (customerId) {
        
        window.location.href = `/providers/messages/start/${customerId}`;
=======
    function setModalStatusBadge(status, label) {
        const normalized = status === 'cancelled' && currentEvent?.extendedProps?.cancellation_reason === 'expired'
            ? 'expired'
            : status;
        modalFields.statusBadge.className = `provider-status-badge ${statusClassMap[normalized] || 'provider-status-paused'}`;
        modalFields.statusBadge.textContent = label;
>>>>>>> feature2
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

<<<<<<< HEAD
        // Confirm action? Optional: add a confirmation dialog
        // if (!confirm(`Are you sure you want to mark this booking as ${status}?`)) return;

        fetch(`/provider/calendar/${currentEventId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Optionally update badge without waiting for refetch
                const badge = document.getElementById('modalStatusBadge');
                badge.innerText = status.charAt(0).toUpperCase() + status.slice(1);
                badge.className = 'px-3 py-1 text-xs font-semibold rounded-full';
                if (status === 'confirmed') badge.classList.add('bg-orange-100', 'text-orange-700');
                else if (status === 'pending') badge.classList.add('bg-gray-200', 'text-gray-700');
                else if (status === 'completed') badge.classList.add('bg-green-100', 'text-green-700');
                else if (status === 'cancelled') badge.classList.add('bg-red-100', 'text-red-700');

                // Refresh calendar events
                calendar.refetchEvents();

                // Close modal after successful update
                closeEventModal();
            } else {
                alert('Failed to update status.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error updating status.');
        });
    };
})();
</script>

<!-- additional responsive tweaks -->
<style>
.modal-transition {
    transition: opacity 0.2s ease, visibility 0.2s ease;
}
.invisible {
    visibility: hidden;
}
.opacity-0 {
    opacity: 0;
}
</style>

=======
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
>>>>>>> feature2
@endsection
