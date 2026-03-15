<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $recipientName;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, ?User $user = null)
    {
        $this->booking = $booking;
        $this->recipientName = $user ? $user->name : ($booking->guest_name ?? 'Valued Customer');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Booking Confirmed - Northern Province Sports Complex')
                    ->view('emails.booking_confirmed')
                    ->with([
                        'userName' => $this->recipientName,
                    ]);
    }
}
