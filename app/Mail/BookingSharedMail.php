<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use App\Models\EmergencyContact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingSharedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceRequest  $booking,
        public EmergencyContact $contact,
    ) {}

    public function build(): self
    {
        return $this->subject('Safety Alert: ' . $this->booking->user->full_name . ' has booked a service')
                    ->view('emails.booking_shared');
    }
}