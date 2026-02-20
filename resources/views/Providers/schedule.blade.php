@extends('providers.layout')

@section('content')

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

</style>
@endpush


  <div class="flex min-h-screen">
    
    <div class="flex-1 w-full">
      <main class="p-4 sm:p-6 md:p-10 space-y-6 md:space-y-8">
   

        <section class="calendar-card p-4 sm:p-6">
          <div id="calendar" class="h-[700px] md:h-[800px]"></div>
        </section>
      </main>
    </div>
  </div>

  <!-- MODAL: EVENT DETAILS  -->
  <div id="eventModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 invisible opacity-0 modal-transition" style="background-color: rgba(0,0,0,0.4); transition: opacity 0.2s ease, visibility 0.2s ease;">
    <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[90vh] overflow-y-auto">
      
      <div class="flex justify-between items-center p-5 border-b border-gray-100">
        <h3 class="text-lg font-semibold text-orange-500">Booking details</h3>
        <button id="closeEventModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
      </div>
      <!-- body -->
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
      <!-- action buttons (like profile modal buttons) -->
      <div class="p-5 border-t border-gray-100 flex flex-wrap gap-3 justify-end">
        <button class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition" onClick="updateStatus('confirmed')">Confirm</button>
        <button class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm" onClick="updateStatus('completed')">✓ Completed</button>
        <button class="px-4 py-2 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600 transition" onClick="updateStatus('cancelled')">Cancel</button>
        <button id="closeEventModal2" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Close</button>
      </div>
    </div>
  </div>

  <!-- MODAL: BLOCK TIME / MANUAL (styled like profile) -->
  <div id="blockModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 invisible opacity-0 modal-transition" style="background-color: rgba(0,0,0,0.4); transition: opacity 0.2s ease, visibility 0.2s ease;">
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
(function () {

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

        // ✅ THIS replaces the dummy array
        events: {
            url: '/provider/calendar/events',
            method: 'GET',
            failure: function () {
                alert('There was an error loading bookings.');
            }
        },

        eventClick: function (info) {

            const e = info.event;
            const ext = e.extendedProps;

            document.getElementById('modalService').innerText = e.title;

            const status = ext.status || 'confirmed';
            const badge = document.getElementById('modalStatusBadge');

            badge.innerText =
                status.charAt(0).toUpperCase() + status.slice(1);

            badge.className =
                'px-3 py-1 text-xs font-semibold rounded-full';

            if (status === 'confirmed')
                badge.classList.add('bg-orange-100', 'text-orange-700');
            else if (status === 'pending')
                badge.classList.add('bg-gray-200', 'text-gray-700');
            else if (status === 'completed')
                badge.classList.add('bg-green-100', 'text-green-700');
            else if (status === 'cancelled')
                badge.classList.add('bg-red-100', 'text-red-700');

            document.getElementById('modalCustomer').innerText = ext.customer || '—';
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

                const timeStr =
                    start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) +
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

            document.getElementById('eventModal')
                .classList.remove('invisible', 'opacity-0');
        },

        dateClick: function (info) {

            const clickedDate = info.date;

            const year = clickedDate.getFullYear();
            const month = String(clickedDate.getMonth() + 1).padStart(2, '0');
            const day = String(clickedDate.getDate()).padStart(2, '0');

            document.getElementById('blockDate').value =
                `${year}-${month}-${day}`;

            const hours = String(clickedDate.getHours()).padStart(2, '0');
            const mins = String(clickedDate.getMinutes()).padStart(2, '0');

            document.getElementById('blockStartTime').value =
                `${hours}:${mins}`;

            document.getElementById('blockModal')
                .classList.remove('invisible', 'opacity-0');
        }
    });

    calendar.render();

})();
</script>


  <!-- additional responsive tweaks -->
  <style>
    /* ensure modals are hidden by default */
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
@endsection