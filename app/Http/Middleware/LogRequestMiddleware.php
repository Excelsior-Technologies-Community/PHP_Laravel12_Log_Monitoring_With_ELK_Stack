<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use App\Models\PerformanceLog;

class LogRequestMiddleware
{

    public function handle($request, Closure $next)
    {

        $startTime = microtime(true);

        $startMemory = memory_get_usage();


        $response = $next($request);


        $endTime = microtime(true);

        $endMemory = memory_get_usage();



        // Calculate Performance

        $duration =
            ($endTime - $startTime) * 1000;


        $memory =
            $endMemory - $startMemory;



        $status =
            $response->getStatusCode();



        $isSlow =
            $duration > 500;



        /*
        |--------------------------------------------------------------------------
        | Store Performance Data
        |--------------------------------------------------------------------------
        */

        PerformanceLog::create([

            'method' => $request->method(),

            'url' => $request->fullUrl(),

            'status_code' => $status,

            'response_time' => round($duration,2),

            'memory_usage' => $memory,

            'ip_address' => $request->ip(),

            'user_id' => auth()->id(),

            'is_slow' => $isSlow,

        ]);




        /*
        |--------------------------------------------------------------------------
        | Send To Elasticsearch Through Existing Logging
        |--------------------------------------------------------------------------
        */

        Log::info('Request processed', [

            'type' => 'performance',

            'method' => $request->method(),

            'url' => $request->fullUrl(),

            'duration_ms' => round($duration,2),

            'memory_mb' => round($memory / 1024 / 1024,2),

            'status' => $status,

            'slow_request' => $isSlow,

            'user_id' => auth()->id(),

            'ip' => $request->ip(),

            'user_agent' => $request->userAgent()

        ]);



        return $response;

    }

}