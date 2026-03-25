<?php
namespace App\Http\Middleware;

use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;

class CheckWorkspaceMember
{
    public function handle(Request $request, Closure $next)
    {
        $workspace = $request->route('workspace');

        if (!$workspace instanceof Workspace) {
            $workspace = Workspace::where('slug', $workspace)->firstOrFail();
        }

        if (!$workspace->hasMember(auth()->user())) {
            abort(403, 'Bạn không phải thành viên của workspace này.');
        }

        // Share workspace globally in request
        $request->merge(['current_workspace' => $workspace]);
        view()->share('currentWorkspace', $workspace);

        return $next($request);
    }
}
