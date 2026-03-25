<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(
        private string $mailable,
        private string $to,
        private array  $data = []
    ) {}

    public function handle(): void
    {
        $mail = new $this->mailable(...array_values($this->data));
        Mail::to($this->to)->send($mail);
    }

    public function failed(\Throwable $e): void
    {
        \Log::error("Email failed to {$this->to}: " . $e->getMessage());
    }
}
