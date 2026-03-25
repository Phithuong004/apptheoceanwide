<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    public function index(Workspace $workspace): JsonResponse
    {
        return response()->json(['data' => $workspace->webhooks]);
    }

    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        $request->validate([
            'name'   => 'required|string|max:100',
            'url'    => 'required|url',
            'events' => 'required|array|min:1',
            'events.*' => 'in:' . implode(',', config('project_management.webhook_events')),
        ]);

        $webhook = $workspace->webhooks()->create([
            ...$request->validated(),
            'secret' => Str::random(32),
        ]);

        return response()->json(['data' => $webhook, 'secret' => $webhook->secret], 201);
    }

    public function update(Request $request, Workspace $workspace, Webhook $webhook): JsonResponse
    {
        $webhook->update($request->only(['name','url','events','is_active']));
        return response()->json(['data' => $webhook->fresh()]);
    }

    public function destroy(Workspace $workspace, Webhook $webhook): JsonResponse
    {
        $webhook->delete();
        return response()->json(null, 204);
    }
}
