<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage)
    {
        $this->contactMessage->loadMissing('property');
    }

    public function envelope(): Envelope
    {
        $sources = ['home_cta' => 'Ana sayfa', 'contact_page' => 'İletişim sayfası', 'property' => 'İlan bilgi talebi'];

        return new Envelope(
            replyTo: [new Address($this->contactMessage->email, $this->contactMessage->name)],
            subject: 'Yeni web formu: '.($this->contactMessage->subject ?: $sources[$this->contactMessage->source] ?? 'İletişim'),
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.contact-message-received');
    }
}
