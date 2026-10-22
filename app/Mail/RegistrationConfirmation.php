<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $doctor) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Registration confirmed | DEPTH');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.registration-confirmation');
    }

    public function attachments(): array
    {
        return [];
    }
}
