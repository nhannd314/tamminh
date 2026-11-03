<?php

namespace App\Mail;

use App\Filament\Resources\PalmReadingBookings\PalmReadingBookingResource;
use App\Models\PalmReadingBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Báo cho admin có khách vừa đặt lịch xem chỉ tay.
 */
class PalmReadingBooked extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public PalmReadingBooking $booking) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lịch xem chỉ tay mới: '.$this->booking->name.' - '.$this->booking->phone,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.palm-reading-booked',
            with: [
                'adminUrl' => PalmReadingBookingResource::getUrl(panel: 'admin'),
            ],
        );
    }
}
