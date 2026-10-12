<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeeInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The dynamic invitation model instance bound to the tenant workspace.
     */
    public Invitation $invitation;

    /**
     * Create a new message instance.
     */
    public function __construct(Invitation $invitation)
    {
        // Bind the contextual encryption token and receiver details
        $this->invitation = $invitation;
    }

    /**
     * Get the message envelope defining the international business subject line.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation to Join Corporate Workspace - ' . tenant('name'),
        );
    }

    /**
     * Get the message content definition targeting our dedicated pure English layout view.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
            with: [
                'actionUrl' => 'http://' . tenant('id') . '.localhost/register/accept?token=' . $this->invitation->token,
                'workspaceName' => tenant('name'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
