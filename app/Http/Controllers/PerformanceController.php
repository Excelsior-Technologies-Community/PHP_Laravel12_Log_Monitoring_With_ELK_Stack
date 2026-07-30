<?php

namespace App\Http\Controllers;


use App\Models\PerformanceLog;
use Illuminate\Http\Request;


class PerformanceController extends Controller
{


    public function index()
    {


        $totalRequests =
            PerformanceLog::count();



        $averageResponse =
            PerformanceLog::avg('response_time');



        $slowRequests =
            PerformanceLog::where(
                'is_slow',
                true
            )->count();



        $totalMemory =
            PerformanceLog::avg(
                'memory_usage'
            );



        $recentLogs =
            PerformanceLog::latest()
            ->take(10)
            ->get();



        return view(
            'performance.dashboard',
            compact(
                'totalRequests',
                'averageResponse',
                'slowRequests',
                'totalMemory',
                'recentLogs'
            )
        );

    }


}