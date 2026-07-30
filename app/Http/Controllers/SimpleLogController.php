<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
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
    /**
     * Search logs with pagination
     */
    public function searchLogs(Request $request)
    {
        $logFile = storage_path('logs/json/laravel-json.log');

        if (!File::exists($logFile)) {
            return response()->json([
                'current_page' => 1,
                'per_page' => 10,
                'total' => 0,
                'last_page' => 0,
                'logs' => [],
            ]);
        }

        $logs = [];

        foreach (File::lines($logFile) as $line) {

            if (!trim($line)) {
                continue;
            }

            $log = json_decode($line, true);

            if ($log) {
                $logs[] = $log;
            }
        }

        // Show latest logs first
        $logs = array_reverse($logs);

        // Filters
        $keyword = strtolower($request->get('q', ''));
        $level = strtoupper($request->get('level', ''));
        $date = $request->get('date', '');

        $logs = array_filter($logs, function ($log) use ($keyword, $level, $date) {

            if ($keyword) {

                $message = strtolower($log['message'] ?? '');

                if (!str_contains($message, $keyword)) {
                    return false;
                }
            }

            if ($level) {

                if (strtoupper($log['level'] ?? '') !== $level) {
                    return false;
                }
            }

            if ($date) {

                $logDate = Carbon::parse(
                    $log['timestamp']
                )->format('Y-m-d');

                if ($logDate !== $date) {
                    return false;
                }
            }

            return true;
        });

        // Re-index array
        $logs = array_values($logs);

        // Pagination
        $perPage = (int) $request->get('per_page', 5);

        $page = max((int) $request->get('page', 1), 1);

        $total = count($logs);

        $lastPage = max((int) ceil($total / $perPage), 1);

        $offset = ($page - 1) * $perPage;

        $paginatedLogs = array_slice(
            $logs,
            $offset,
            $perPage
        );

        return response()->json([

            'current_page' => $page,

            'per_page' => $perPage,

            'total' => $total,

            'last_page' => $lastPage,

            'from' => $total > 0 ? $offset + 1 : 0,

            'to' => min($offset + $perPage, $total),

            'logs' => $paginatedLogs,

            'source' => 'local_json_file',

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

    /**
     * Export logs to CSV
     */
    public function exportLogs()
    {
        $logFile = storage_path('logs/json/laravel-json.log');

        if (!File::exists($logFile)) {
            return response()->json([
                'message' => 'No logs available to export.'
            ], 404);
        }

        $response = new StreamedResponse(function () use ($logFile) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Timestamp',
                'Level',
                'Message',
                'IP Address',
                'URL'
            ]);

            foreach (File::lines($logFile) as $line) {

                if (!trim($line)) {
                    continue;
                }

                $log = json_decode($line, true);

                if (!$log) {
                    continue;
                }

                fputcsv($handle, [

                    $log['timestamp'] ?? '',

                    $log['level'] ?? '',

                    $log['message'] ?? '',

                    $log['context']['ip'] ?? '',

                    $log['context']['url'] ?? ''

                ]);
            }

            fclose($handle);
        });

        $response->headers->set(
            'Content-Type',
            'text/csv'
        );

        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="laravel_logs.csv"'
        );

        return $response;
    }

    /**
     * Clear all logs
     */
    public function clearLogs()
    {
        $logFile = storage_path('logs/json/laravel-json.log');

        if (File::exists($logFile)) {

            File::put($logFile, '');
        }

        return response()->json([

            'success' => true,

            'message' => 'Logs cleared successfully.'

        ]);
    }
}
