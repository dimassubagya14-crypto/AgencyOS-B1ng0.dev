<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use App\Models\TeamMember;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamAssignmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceRequest $request,
        public TeamMember $member,
        public string $messageContent,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pembagian Tugas ' . $this->request->number . ' — ' . $this->request->project_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.team-assignment',
            with: ['messageContent' => $this->messageContent],
        );
    }
}
