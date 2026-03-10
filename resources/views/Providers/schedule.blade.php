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

calendar.render();

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