<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Models\Client;
use App\Models\Workspace;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Workspace $workspace)
    {
        $clients = Client::where('workspace_id', $workspace->id)
                         ->withCount('projects')
                         ->with('primaryContact')
                         ->when(request('search'), fn($q, $s) =>
                             $q->where('name', 'like', "%$s%")
                               ->orWhere('email', 'like', "%$s%")
                               ->orWhere('company', 'like', "%$s%")
                         )
                         ->when(request('status'), fn($q, $s) => $q->where('status', $s))
                         ->latest()
                         ->paginate(15);

        return view('clients.index', compact('workspace', 'clients'));
    }

    public function create(Workspace $workspace)
    {
        return view('clients.create', compact('workspace'));
    }

    public function store(StoreClientRequest $request, Workspace $workspace)
    {
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('clients', 'public');
        }

        Client::create([...$data, 'workspace_id' => $workspace->id]);

        return redirect()->route('clients.index', $workspace->slug)
                         ->with('success', 'Khách hàng đã được thêm!');
    }

    public function show(Workspace $workspace, Client $client)
    {
        $client->load(['contacts', 'projects.activeSprint', 'invoices']);

        $stats = [
            'total_projects'  => $client->projects->count(),
            'active_projects' => $client->projects->where('status', 'active')->count(),
            'total_billed'    => $client->invoices->where('status', 'paid')->sum('total'),
            'outstanding'     => $client->invoices->whereIn('status', ['sent', 'partial'])->sum('amount_due'),
        ];

        return view('clients.show', compact('workspace', 'client', 'stats'));
    }

    public function edit(Workspace $workspace, Client $client)
    {
        return view('clients.edit', compact('workspace', 'client'));
    }

    public function update(StoreClientRequest $request, Workspace $workspace, Client $client)
    {
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('clients', 'public');
        }

        $client->update($data);

        return back()->with('success', 'Thông tin khách hàng đã được cập nhật.');
    }

    public function destroy(Workspace $workspace, Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index', $workspace->slug)
                         ->with('success', 'Khách hàng đã được xoá.');
    }
}
