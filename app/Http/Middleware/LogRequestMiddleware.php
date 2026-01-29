<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogRequestMiddleware
{
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $duration = microtime(true) - $startTime;
        $memory = memory_get_usage();
        
        Log::info('Request processed', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'duration_ms' => round($duration * 1000, 2),
            'memory_mb' => round($memory / 1024 / 1024, 2),
            'status' => $response->getStatusCode(),
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        return $response;
    }
}