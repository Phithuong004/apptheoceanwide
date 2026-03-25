<?php
namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DailyDigestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User       $user,
        public Collection $tasks,
        public Collection $overdue,
        public Collection $dueToday
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '📋 Daily Digest — ' . now()->format('d/m/Y'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.daily-digest');
    }
}
