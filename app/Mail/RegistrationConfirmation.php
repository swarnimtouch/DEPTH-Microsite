<?php

namespace App\Mail;

use App\Models\User;
use App\Support\ParticipantTimeZone;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public array $session;

    public array $sessionTimeLines;

    public string $accessUrl;

    public function __construct(public User $doctor)
    {
        $doctor->loadMissing('registeredModule');
        $module = $doctor->registeredModule;
        $this->session = ParticipantTimeZone::eventDetails(
            $module?->getRawOriginal('start_at'),
            $module?->getRawOriginal('end_at'),
            $doctor->country,
        );
        $this->sessionTimeLines = ParticipantTimeZone::seaLines(
            $module?->getRawOriginal('start_at'),
            $module?->getRawOriginal('end_at'),
        );
        $this->accessUrl = route('home', ['auth' => 'login']).'#register';
    }

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
