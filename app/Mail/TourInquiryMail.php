<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TourInquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Tour Inquiry - ' . ($this->data['tour_title'] ?? 'Tour') . ' - ' . ($this->data['first_name'] ?? '') . ' ' . ($this->data['last_name'] ?? ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tour-inquiry',
        );
    }
}
