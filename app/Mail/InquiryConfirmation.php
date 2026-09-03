<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $inquiry;

    public function __construct(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thank You for Your Inquiry - Afro-Vertex Tours',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.inquiries.confirmation-to-visitor',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}