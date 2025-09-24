<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class AccountSetupMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $setupUrl;

    public function __construct(User $user, string $setupUrl)
    {
        $this->user = $user;
        $this->setupUrl = $setupUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Set Up Your ' . config('app.name') . ' Account',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.users.account-setup',
            with: [
                'user' => $this->user,
                'setupUrl' => $this->setupUrl,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}