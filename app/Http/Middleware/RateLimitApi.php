<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitApi
{
    public function __construct(private RateLimiter $limiter) {}

    public function handle(Request $request, Closure $next, int $maxAttempts = 60): Response
    {
        $key = 'api:' . ($request->user()?->id ?? $request->ip());

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message'     => 'Too many requests.',
                'retry_after' => $this->limiter->availableIn($key),
            ], 429);
        }

        $this->limiter->hit($key, 60);

        $response = $next($request);
        $response->headers->set('X-RateLimit-Limit',     $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $this->limiter->remaining($key, $maxAttempts));

        return $response;
    }
}
