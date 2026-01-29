<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class SimpleLogController extends Controller
{
    /**
     * Generate test logs
     */
    public function generateLogs(Request $request)
    {
        $type = $request->get('type', 'info');
        
        $logData = [
            'timestamp' => now()->toISOString(),
            'level' => strtoupper($type),
            'message' => "Test {$type} log generated via API",
            'context' => [
                'user_id' => auth()->id() ?? 'guest',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'data' => ['test' => true]
            ]
        ];
        
        // Log to JSON file
        $jsonLog = json_encode($logData) . PHP_EOL;
        File::append(storage_path('logs/json/laravel-json.log'), $jsonLog);
        
        // Also log normally
        Log::$type("Test {$type} log generated");
        
        return response()->json([
            'message' => ucfirst($type) . ' log generated successfully',
            'type' => $type,
            'data' => $logData
        ]);
    }
    
    /**
     * Search logs
     */
    public function searchLogs(Request $request)
    {
        $logFile = storage_path('logs/json/laravel-json.log');
        
        if (!File::exists($logFile)) {
            return response()->json([
                'total' => 0,
                'logs' => [],
                'message' => 'No logs found. Generate some logs first!'
            ]);
        }
        
        $logs = [];
        $lines = File::lines($logFile);
        
        foreach ($lines as $line) {
            if (trim($line)) {
                $log = json_decode($line, true);
                if ($log) {
                    $logs[] = $log;
                }
            }
        }
        
        // Reverse to show latest first
        $logs = array_reverse($logs);
        
        // Apply filters
        $query = strtolower($request->get('q', ''));
        $level = strtoupper($request->get('level', ''));
        
        if ($query || $level) {
            $logs = array_filter($logs, function($log) use ($query, $level) {
                $matches = true;
                
                if ($query) {
                    $message = strtolower($log['message'] ?? '');
                    $matches = $matches && str_contains($message, $query);
                }
                
                if ($level) {
                    $logLevel = strtoupper($log['level'] ?? '');
                    $matches = $matches && ($logLevel === $level);
                }
                
                return $matches;
            });
        }
        
        // Limit results
        $logs = array_slice($logs, 0, 50);
        
        return response()->json([
            'total' => count($logs),
            'logs' => array_values($logs),
            'source' => 'local_json_file'
        ]);
    }
    
    /**
     * Get log statistics
     */
    public function getStatistics(Request $request)
    {
        $logFile = storage_path('logs/json/laravel-json.log');
        
        if (!File::exists($logFile)) {
            return response()->json([
                'total_logs' => 0,
                'logs_per_level' => [],
                'logs_per_hour' => [],
                'message' => 'No logs found'
            ]);
        }
        
        $logs = [];
        $lines = File::lines($logFile);
        
        foreach ($lines as $line) {
            if (trim($line)) {
                $log = json_decode($line, true);
                if ($log) {
                    $logs[] = $log;
                }
            }
        }
        
        // Count logs by level
        $levelCounts = [
            'INFO' => 0,
            'WARNING' => 0,
            'ERROR' => 0,
            'DEBUG' => 0
        ];
        
        foreach ($logs as $log) {
            $level = strtoupper($log['level'] ?? 'INFO');
            if (isset($levelCounts[$level])) {
                $levelCounts[$level]++;
            } else {
                $levelCounts[$level] = 1;
            }
        }
        
        // Convert to format expected by frontend
        $logsPerLevel = [];
        foreach ($levelCounts as $level => $count) {
            if ($count > 0) {
                $logsPerLevel[] = [
                    'key' => $level,
                    'doc_count' => $count
                ];
            }
        }
        
        // Simple hourly distribution (last 24 hours)
        $logsPerHour = [];
        $now = now();
        for ($i = 23; $i >= 0; $i--) {
            $hour = $now->copy()->subHours($i);
            $hourStart = $hour->copy()->startOfHour();
            $hourEnd = $hour->copy()->endOfHour();
            
            $count = 0;
            foreach ($logs as $log) {
                $logTime = $log['timestamp'] ?? '';
                if ($logTime && $logTime >= $hourStart->toISOString() && $logTime <= $hourEnd->toISOString()) {
                    $count++;
                }
            }
            
            $logsPerHour[] = [
                'key_as_string' => $hourStart->toISOString(),
                'key' => $hourStart->timestamp * 1000,
                'doc_count' => $count
            ];
        }
        
        return response()->json([
            'total_logs' => count($logs),
            'logs_per_level' => $logsPerLevel,
            'logs_per_hour' => $logsPerHour,
            'source' => 'local_json_file'
        ]);
    }
}