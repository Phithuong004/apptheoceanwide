<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TrackLastActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $key = 'user_last_active_' . auth()->id();
            if (!Cache::has($key)) {
                auth()->user()->update(['last_active_at' => now()]);
                Cache::put($key, true, 60); // Update tối đa 1 lần/phút
            }
        }
        return $next($request);
    }
}
