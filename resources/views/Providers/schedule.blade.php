@extends('providers.layout')

@section('content')
<<<<<<< HEAD

<meta name="csrf-token" content="{{ csrf_token() }}">

=======
>>>>>>> services-bookings-feature
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<style>
<<<<<<< HEAD

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
=======
.sidebar-card {
  background-color: white;
  border-right: 1px solid #f3f4f6;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

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

.modal-transition {
  transition: opacity 0.2s ease, visibility 0.2s ease;
>>>>>>> services-bookings-feature
}

.modal-transition{
    transition:opacity .2s ease,visibility .2s ease;
}

button:disabled{
    opacity:.5;
    cursor:not-allowed;
}

<<<<<<< HEAD
=======
button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.invisible {
  visibility: hidden;
}
.opacity-0 {
  opacity: 0;
}
>>>>>>> services-bookings-feature
</style>
@endpush

<div class="space-y-8">

<<<<<<< HEAD
    {{-- PAGE HEADER --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Bookings Calendar</h1>
            <p class="text-sm text-gray-500">View your upcoming jobs and availability</p>
        </div>
=======
<div id="eventModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 invisible opacity-0 modal-transition" style="background-color: rgba(0,0,0,0.4);">
  <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[90vh] overflow-y-auto">
    <div class="flex justify-between items-center p-5 border-b border-gray-100">
      <h3 class="text-lg font-semibold text-orange-500">Booking details</h3>
      <button id="closeEventModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
>>>>>>> services-bookings-feature
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
<<<<<<< HEAD

    <div class="flex justify-between">
    <span id="modalService" class="font-bold text-lg"></span>
    <span id="modalStatusBadge" class="px-3 py-1 text-xs rounded-full"></span>
    </div>

    <div class="grid grid-cols-2 gap-4 text-sm">

    <div>
    <span class="text-gray-400">Customer</span>
    <p id="modalCustomer"></p>
=======
      <div class="flex items-center justify-between">
        <span id="modalService" class="text-lg font-bold text-gray-800">Service</span>
        <span id="modalStatusBadge" class="px-3 py-1 text-xs font-semibold rounded-full">Pending</span>
      </div>
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div><span class="text-gray-400 block">Customer</span><span id="modalCustomer" class="font-medium">-</span></div>
        <div><span class="text-gray-400 block">Phone</span><span id="modalPhone" class="font-medium">-</span></div>
      </div>
      <div><span class="text-gray-400 text-sm">Address</span><p id="modalAddress" class="font-medium bg-gray-50 p-2 rounded-lg">-</p></div>
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div><span class="text-gray-400 block">Date</span><span id="modalDate" class="font-medium">-</span></div>
        <div><span class="text-gray-400 block">Time</span><span id="modalTime" class="font-medium">-</span></div>
      </div>
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div><span class="text-gray-400 block">Duration</span><span id="modalDuration" class="font-medium">-</span></div>
        <div><span class="text-gray-400 block">Total price</span><span id="modalPrice" class="font-bold text-orange-600">-</span></div>
      </div>
      <div><span class="text-gray-400 text-sm">Notes</span><p id="modalNotes" class="text-gray-700 bg-gray-50 p-3 rounded-lg text-sm">-</p></div>
    </div>
    <div class="p-5 border-t border-gray-100 flex flex-wrap gap-3 justify-end">
      <button id="confirmBtn" class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition" onclick="updateStatus('confirmed')">Confirm</button>
      <button id="startBtn" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-sm" onclick="updateStatus('in_progress')">Start</button>
      <button id="completeBtn" class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-sm" onclick="updateStatus('completed')">Completed</button>
      <button id="cancelBtn" class="px-4 py-2 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600 transition" onclick="updateStatus('cancelled')">Cancel</button>
      <button id="closeEventModal2" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">Close</button>
>>>>>>> services-bookings-feature
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

    <div>
    <span class="text-gray-400">Notes</span>
    <p id="modalNotes"></p>
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

    <div class="p-5 border-t flex justify-end gap-3">

      <button class="px-4 py-2 bg-orange-500 text-white rounded">
      Save Block
      </button>

    </div>

</div>

<<<<<<< HEAD
</div>



{{-- CALENDAR SCRIPT --}}
<script>

let currentEventId=null;

document.addEventListener('DOMContentLoaded',function(){

const calendarEl=document.getElementById('calendar');

const calendar=new FullCalendar.Calendar(calendarEl,{

initialView:'timeGridWeek',

headerToolbar:{
left:'prev,next today',
center:'title',
right:'dayGridMonth,timeGridWeek,timeGridDay'
},

slotMinTime:'08:00:00',
slotMaxTime:'18:00:00',

events:'/provider/calendar/events',

eventClick:function(info){

const e=info.event;
const ext=e.extendedProps;

currentEventId=e.id;

document.getElementById('modalService').innerText=e.title;
document.getElementById('modalCustomer').innerText=ext.customer_name;
document.getElementById('modalPhone').innerText=ext.phone;
document.getElementById('modalAddress').innerText=ext.address;
document.getElementById('modalNotes').innerText=ext.notes;
document.getElementById('modalPrice').innerText="R "+ext.price;

document.getElementById('eventModal').classList.remove('invisible','opacity-0');

},

dateClick:function(info){

document.getElementById('blockDate').value=info.dateStr;

document.getElementById('blockModal')
.classList.remove('invisible','opacity-0');

}

});
=======
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
(function() {
    const eventsUrl = "{{ route('provider.calendar.events') }}";
    const statusBaseUrl = "{{ url('/provider/calendar') }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function closeEventModal() {
        document.getElementById('eventModal').classList.add('invisible', 'opacity-0');
    }

    function openEventModal() {
        document.getElementById('eventModal').classList.remove('invisible', 'opacity-0');
    }

    function setBadge(status) {
        const badge = document.getElementById('modalStatusBadge');
        badge.innerText = status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
        badge.className = 'px-3 py-1 text-xs font-semibold rounded-full';

        if (status === 'confirmed') badge.classList.add('bg-orange-100', 'text-orange-700');
        else if (status === 'pending') badge.classList.add('bg-gray-200', 'text-gray-700');
        else if (status === 'in_progress') badge.classList.add('bg-indigo-100', 'text-indigo-700');
        else if (status === 'completed') badge.classList.add('bg-green-100', 'text-green-700');
        else if (status === 'cancelled') badge.classList.add('bg-red-100', 'text-red-700');
    }

    function setButtons(status) {
        const confirmBtn = document.getElementById('confirmBtn');
        const startBtn = document.getElementById('startBtn');
        const completeBtn = document.getElementById('completeBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        confirmBtn.disabled = status !== 'pending';
        startBtn.disabled = status !== 'confirmed';
        completeBtn.disabled = status !== 'in_progress';
        cancelBtn.disabled = !(status === 'pending' || status === 'confirmed');
    }

    document.getElementById('closeEventModal').addEventListener('click', closeEventModal);
    document.getElementById('closeEventModal2').addEventListener('click', closeEventModal);
    document.getElementById('eventModal').addEventListener('click', function(e) {
        if (e.target === this) closeEventModal();
    });

    let currentEventId = null;

    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        allDaySlot: false,
        height: 'auto',
        timeZone: 'local',
        events: {
            url: eventsUrl,
            method: 'GET',
            failure: function() {
                alert('There was an error loading bookings.');
            }
        },
        eventClick: function(info) {
            const event = info.event;
            const ext = event.extendedProps || {};
            const currentStatus = ext.status || 'pending';

            currentEventId = event.id;

            document.getElementById('modalService').innerText = event.title || '-';
            document.getElementById('modalCustomer').innerText = ext.customer_name || '-';
            document.getElementById('modalPhone').innerText = ext.phone || '-';
            document.getElementById('modalAddress').innerText = ext.address || '-';
            document.getElementById('modalNotes').innerText = ext.notes || '-';
            document.getElementById('modalPrice').innerText = ext.price ? 'R ' + ext.price : '-';

            const start = event.start;
            const end = event.end;

            if (start) {
                const dateStr = start.toLocaleDateString('en-ZA', { day: 'numeric', month: 'short', year: 'numeric' });
                document.getElementById('modalDate').innerText = dateStr;

                const timeStr = start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) +
                    ' - ' +
                    (end ? end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '');
                document.getElementById('modalTime').innerText = timeStr;
            }

            if (start && end) {
                const totalMinutes = Math.max(Math.round((end - start) / (1000 * 60)), 0);
                const hrs = Math.floor(totalMinutes / 60);
                const mins = totalMinutes % 60;
                const durText = `${hrs > 0 ? hrs + 'h ' : ''}${mins > 0 ? mins + 'm' : ''}`.trim();
                document.getElementById('modalDuration').innerText = durText || '-';
            } else {
                document.getElementById('modalDuration').innerText = '-';
            }

            setBadge(currentStatus);
            setButtons(currentStatus);
            openEventModal();
        }
    });
>>>>>>> services-bookings-feature

calendar.render();

<<<<<<< HEAD
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
=======
    window.updateStatus = function(status) {
        if (!currentEventId) {
            alert('No event selected.');
            return;
        }

        fetch(`${statusBaseUrl}/${currentEventId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status })
        })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Failed to update status.');
            }
            return data;
        })
        .then(() => {
            calendar.refetchEvents();
            closeEventModal();
        })
        .catch((err) => {
            alert(err.message || 'Error updating status.');
        });
    };
})();
</script>
@endpush
@endsection
>>>>>>> services-bookings-feature
