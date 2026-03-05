@extends('providers.layout')

@section('content')
<div class="container mt-4">
    <h2>Bookings</h2>
    <p>List of bookings will appear here.</p>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>User</th>
                <th>Service</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
            <tr id="booking-{{ $booking->id }}">
                <td>{{ $booking->id }}</td>
                <td>{{ $booking->user->name }}</td>
                <td>{{ $booking->service->name }}</td>
                <td>{{ $booking->booking_date->format('Y-m-d') }}</td>
                <td id="status-{{ $booking->id }}">{{ ucfirst($booking->status) }}</td>
                <td>
                    @if($booking->status === 'pending')
                        <button class="btn btn-success btn-sm confirm-booking" data-id="{{ $booking->id }}">Accept</button>
                        <button class="btn btn-danger btn-sm cancel-booking" data-id="{{ $booking->id }}">Decline</button>
                    @elseif($booking->status === 'in_progress')
                        <button class="btn btn-primary btn-sm complete-booking" data-id="{{ $booking->id }}">Complete</button>
                    @endif
                    <button class="btn btn-info btn-sm message-user" 
                            data-user="{{ $booking->user->user_id }}"
                            data-name="{{ $booking->user->name }}">Message</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Messaging Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Message: <span id="modalUserName"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="messagesContainer" style="max-height:400px; overflow-y:auto; border:1px solid #ddd; padding:10px; margin-bottom:10px;"></div>
        <form id="messageForm">
            <input type="hidden" id="modalUserId" name="user_id">
            <div class="input-group">
                <input type="text" id="messageInput" name="message" class="form-control" placeholder="Type your message..." required>
                <button type="submit" class="btn btn-primary">Send</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Update booking status
    function updateBookingStatus(id, action) {
        fetch(`/providers/bookings/${id}/${action}`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const statusTd = document.getElementById(`status-${id}`);
                statusTd.textContent = data.status.replace('_', ' ').toUpperCase();

                // Replace action buttons depending on status
                const row = document.getElementById(`booking-${id}`);
                const actionCell = row.querySelector('td:last-child');
                let buttons = '';

                if(data.status === 'pending') {
                    buttons = `
                        <button class="btn btn-success btn-sm confirm-booking" data-id="${id}">Accept</button>
                        <button class="btn btn-danger btn-sm cancel-booking" data-id="${id}">Decline</button>
                        <button class="btn btn-info btn-sm message-user" data-user="${data.user_id}" data-name="${data.user_name}">Message</button>
                    `;
                } else if(data.status === 'in_progress') {
                    buttons = `
                        <button class="btn btn-primary btn-sm complete-booking" data-id="${id}">Complete</button>
                        <button class="btn btn-info btn-sm message-user" data-user="${data.user_id}" data-name="${data.user_name}">Message</button>
                    `;
                } else if(data.status === 'completed' || data.status === 'cancelled') {
                    buttons = `
                        <button class="btn btn-info btn-sm message-user" data-user="${data.user_id}" data-name="${data.user_name}">Message</button>
                    `;
                }

                actionCell.innerHTML = buttons;
                attachActionEvents(); // reattach click listeners
            }
        });
    }

    // Attach events to buttons
    function attachActionEvents() {
        document.querySelectorAll('.confirm-booking').forEach(btn => {
            btn.onclick = () => updateBookingStatus(btn.dataset.id, 'confirm');
        });
        document.querySelectorAll('.cancel-booking').forEach(btn => {
            btn.onclick = () => updateBookingStatus(btn.dataset.id, 'cancel');
        });
        document.querySelectorAll('.complete-booking').forEach(btn => {
            btn.onclick = () => updateBookingStatus(btn.dataset.id, 'complete');
        });
    }

    attachActionEvents(); // initial attach
});
</script>