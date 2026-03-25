<?php
namespace App\Services;

use App\Jobs\SendWebhookJob;
use App\Models\Webhook;

class WebhookService
{
    public function dispatch(string $event, array $payload, int $workspaceId): void
    {
        $webhooks = Webhook::where('workspace_id', $workspaceId)
                           ->where('is_active', true)
                           ->whereJsonContains('events', $event)
                           ->get();

        foreach ($webhooks as $webhook) {
            SendWebhookJob::dispatch($webhook, $event, $payload);
        }
    }

    public function send(Webhook $webhook, string $event, array $payload): bool
    {
        $body      = json_encode(['event' => $event, 'payload' => $payload, 'timestamp' => now()->toIso8601String()]);
        $signature = hash_hmac('sha256', $body, $webhook->secret ?? '');

        try {
            $response = \Http::withHeaders([
                'Content-Type'       => 'application/json',
                'X-Webhook-Event'    => $event,
                'X-Webhook-Signature'=> "sha256={$signature}",
            ])->timeout(10)->post($webhook->url, json_decode($body, true));

            $webhook->update([
                'last_triggered_at' => now(),
                'failure_count'     => $response->successful() ? 0 : $webhook->failure_count + 1,
            ]);

            if ($webhook->fresh()->failure_count >= 5) {
                $webhook->update(['is_active' => false]);
            }

            return $response->successful();
        } catch (\Exception $e) {
            $webhook->increment('failure_count');
            return false;
        }
    }
}
