@extends('providers.layout')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

@push('styles')
<style>

.calendar-card{
    background:white;
    border:1px solid #e5e7eb;
    border-radius:10px;
    box-shadow:0 1px 3px rgba(0,0,0,0.05);
}

.fc-theme-standard .fc-toolbar .fc-button{
    background:#f97316 !important;
    border-color:#f97316 !important;
}

.fc-theme-standard .fc-toolbar .fc-button:hover{
    background:#ea580c !important;
}

.modal-transition{
    transition:opacity .2s ease,visibility .2s ease;
}

button:disabled{
    opacity:.5;
    cursor:not-allowed;
}

</style>
@endpush

<div class="space-y-8">

    {{-- PAGE HEADER --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Bookings Calendar</h1>
            <p class="text-sm text-gray-500">View your upcoming jobs and availability</p>
        </div>
    </div>

    {{-- CALENDAR --}}
    <section class="calendar-card p-6">

      <div id="calendar" class="h-[750px]"></div>


      {{-- LEGEND --}}
      <div class="mt-6 border-t pt-4 flex flex-wrap gap-6 text-sm">

        <div class="flex items-center gap-2">
        <span class="w-4 h-4 bg-gray-400 rounded-full"></span>
        <span>Pending</span>
        </div>

        <div class="flex items-center gap-2">
        <span class="w-4 h-4 bg-orange-500 rounded-full"></span>
        <span>Confirmed</span>
        </div>

        <div class="flex items-center gap-2">
        <span class="w-4 h-4 bg-green-500 rounded-full"></span>
        <span>Completed</span>
        </div>

        <div class="flex items-center gap-2">
        <span class="w-4 h-4 bg-red-500 rounded-full"></span>
        <span>Cancelled</span>
        </div>

      </div>

    </section>

    </div>


    {{-- EVENT DETAILS MODAL --}}
    <div id="eventModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 invisible opacity-0 modal-transition" style="background:rgba(0,0,0,.4)">

    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg">

    <div class="flex justify-between items-center p-5 border-b">
    <h3 class="text-lg font-semibold text-orange-500">Booking details</h3>
    <button id="closeEventModal">&times;</button>
    </div>

    <div class="p-5 space-y-4">

    <div class="flex justify-between">
    <span id="modalService" class="font-bold text-lg"></span>
    <span id="modalStatusBadge" class="px-3 py-1 text-xs rounded-full"></span>
    </div>

    <div class="grid grid-cols-2 gap-4 text-sm">

    <div>
    <span class="text-gray-400">Customer</span>
    <p id="modalCustomer"></p>
    </div>

    <div>
    <span class="text-gray-400">Phone</span>
    <p id="modalPhone"></p>
    </div>

    </div>

    <div>
    <span class="text-gray-400 text-sm">Address</span>
    <p id="modalAddress"></p>
    </div>

    <div class="grid grid-cols-2 gap-4 text-sm">

    <div>
    <span class="text-gray-400">Date</span>
    <p id="modalDate"></p>
    </div>

    <div>
    <span class="text-gray-400">Time</span>
    <p id="modalTime"></p>
    </div>

    </div>

    <div class="grid grid-cols-2 gap-4 text-sm">

    <div>
    <span class="text-gray-400">Duration</span>
    <p id="modalDuration"></p>
    </div>

    <div>
    <span class="text-gray-400">Price</span>
    <p id="modalPrice"></p>
    </div>

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


    <div class="p-5 border-t flex justify-end gap-3">

    <button id="confirmBtn"
    class="px-4 py-2 border rounded"
    onclick="updateStatus('confirmed')">Confirm</button>

    <button id="completeBtn"
    class="px-4 py-2 bg-green-600 text-white rounded"
    onclick="updateStatus('completed')">Completed</button>

    <button id="cancelBtn"
    class="px-4 py-2 bg-red-500 text-white rounded"
    onclick="updateStatus('cancelled')">Cancel</button>

    </div>

    </div>

    </div>


    {{-- BLOCK TIME MODAL --}}
    <div id="blockModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 invisible opacity-0 modal-transition" style="background:rgba(0,0,0,.4)">

    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">

      <div class="p-5 border-b flex justify-between">
      <h3 class="font-semibold text-orange-500">Block Time</h3>
      <button id="closeBlockModal">&times;</button>
      </div>

      <div class="p-5 space-y-4">

      <input id="blockDate" readonly class="w-full border p-2 rounded">

      <input id="blockStartTime" readonly class="w-full border p-2 rounded">

      <select class="w-full border p-2 rounded">
      <option>30 min</option>
      <option>1 hour</option>
      <option>2 hours</option>
      <option>3 hours</option>
      </select>

      <textarea class="w-full border p-2 rounded" placeholder="Reason"></textarea>

    </div>
    <div class="p-5 border-t border-gray-100 flex gap-3 justify-end">
      <button class="px-5 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition shadow-sm">Save block</button>
      <button id="closeBlockModal2" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Cancel</button>
      
    </div>

</div>

</div>



{{-- CALENDAR SCRIPT --}}
<script>
(function() {




    // ---------- MODAL CLOSE FUNCTIONS ----------
    function closeEventModal() {
        document.getElementById('eventModal').classList.add('invisible', 'opacity-0');
    }

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

document.getElementById('eventModal').classList.remove('invisible','opacity-0');

},

dateClick:function(info){

document.getElementById('blockDate').value=info.dateStr;

document.getElementById('blockModal')
.classList.remove('invisible','opacity-0');

}

});

    calendar.render();

    document.getElementById('messageCustomerBtn').addEventListener('click', function() {
    const customerId = this.getAttribute('data-customer-id');
    if (customerId) {
        
        window.location.href = `/providers/messages/start/${customerId}`;
    }
});

});


function updateStatus(status){

fetch(`/provider/calendar/${currentEventId}/status`,{

method:'POST',

headers:{
'Content-Type':'application/json',
'X-CSRF-TOKEN':
document.querySelector('meta[name="csrf-token"]').content
},

body:JSON.stringify({status:status})

})
.then(res=>res.json())
.then(data=>{

if(data.success){

location.reload();

}

});

}


document.getElementById('closeEventModal')
.onclick=()=>{

document.getElementById('eventModal')
.classList.add('invisible','opacity-0');

};

document.getElementById('closeBlockModal')
.onclick=()=>{

document.getElementById('blockModal')
.classList.add('invisible','opacity-0');

};

</script>

@endsection