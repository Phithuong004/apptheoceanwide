<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Workspace;
use Illuminate\Http\Request;

class ClientPortalController extends Controller
{
    public function show(Workspace $workspace, Client $client)
    {
        // Check if auth user is a portal user for this client
        $isPortalUser = $client->portalUsers()->where('user_id', auth()->id())->exists();
        if (!$isPortalUser && !auth()->user()->isWorkspaceAdmin($workspace)) {
            abort(403, 'Bạn không có quyền truy cập client portal này.');
        }

        $projects = $client->projects()
                           ->with(['tasks' => fn($q) => $q->whereNull('parent_id')])
                           ->get()
                           ->map(fn($p) => [
                               'name'       => $p->name,
                               'status'     => $p->status,
                               'progress'   => $p->progress,
                               'start_date' => $p->start_date?->format('d/m/Y'),
                               'end_date'   => $p->end_date?->format('d/m/Y'),
                               'tasks'      => [
                                   'total'     => $p->tasks->count(),
                                   'done'      => $p->tasks->where('status','done')->count(),
                                   'in_progress'=> $p->tasks->where('status','in_progress')->count(),
                               ],
                           ]);

        $invoices = $client->invoices()
                           ->whereIn('status', ['sent','viewed','partial','paid'])
                           ->latest()
                           ->get();

        return view('clients.portal', compact('workspace','client','projects','invoices'));
    }
}
