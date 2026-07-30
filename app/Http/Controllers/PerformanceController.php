<?php

namespace App\Http\Controllers;

use App\Models\PerformanceLog;

class PerformanceController extends Controller
{
    public function index()
    {
        // Dashboard Statistics
        $totalRequests = PerformanceLog::count();

        $averageResponse = PerformanceLog::avg('response_time') ?? 0;

        $maxResponse = PerformanceLog::max('response_time') ?? 0;

        $minResponse = PerformanceLog::min('response_time') ?? 0;

        $slowRequests = PerformanceLog::where('is_slow', true)->count();

        $fastRequests = PerformanceLog::where('is_slow', false)->count();

        $failedRequests = PerformanceLog::where('status_code', '>=', 400)->count();

        $successfulRequests = $totalRequests - $failedRequests;

        $successRate = $totalRequests > 0
            ? round(($successfulRequests / $totalRequests) * 100, 2)
            : 0;

        $averageMemory = PerformanceLog::avg('memory_usage') ?? 0;

        // Latest Requests
        $recentLogs = PerformanceLog::latest()
            ->take(10)
            ->get();

        // Top Requested URLs
        $topUrls = PerformanceLog::select('url')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('url')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        // Response Time Chart Data
        $chartData = PerformanceLog::latest()
            ->take(20)
            ->get()
            ->reverse();

        return view('performance.dashboard', compact(
            'totalRequests',
            'averageResponse',
            'maxResponse',
            'minResponse',
            'slowRequests',
            'fastRequests',
            'failedRequests',
            'successfulRequests',
            'successRate',
            'averageMemory',
            'recentLogs',
            'topUrls',
            'chartData'
        ));
    }
}
