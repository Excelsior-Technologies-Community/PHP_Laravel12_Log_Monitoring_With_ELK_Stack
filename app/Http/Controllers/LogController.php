<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

class LogController extends Controller
{
    protected $elasticsearch;
    protected $elasticsearchEnabled;
    
    public function __construct()
    {
        $this->elasticsearchEnabled = env('ELASTICSEARCH_ENABLED', false);
        
        if ($this->elasticsearchEnabled) {
            try {
                $this->elasticsearch = ClientBuilder::create()
                    ->setHosts([env('ELASTICSEARCH_HOST', 'http://localhost:9200')])
                    ->build();
            } catch (\Exception $e) {
                $this->elasticsearchEnabled = false;
                Log::warning('Elasticsearch connection failed, using file logs only', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * Generate test logs
     */
    public function generateLogs(Request $request)
    {
        $type = $request->get('type', 'info');
        
        switch ($type) {
            case 'info':
                Log::channel('json')->info('User accessed generate logs endpoint', [
                    'user_id' => auth()->id(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                break;
                
            case 'warning':
                Log::channel('json')->warning('This is a warning message', [
                    'context' => 'Test warning',
                    'data' => ['foo' => 'bar']
                ]);
                break;
                
            case 'error':
                Log::channel('json')->error('This is an error message', [
                    'exception' => 'TestException',
                    'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
                ]);
                break;
                
            case 'debug':
                Log::channel('json')->debug('Debug information', [
                    'memory_usage' => memory_get_usage(),
                    'loaded_extensions' => get_loaded_extensions()
                ]);
                break;
        }
        
        // Also log to default channel for backup
        Log::$type('Test log generated via API', ['type' => $type]);
        
        return response()->json([
            'message' => ucfirst($type) . ' log generated successfully',
            'type' => $type,
            'elasticsearch_enabled' => $this->elasticsearchEnabled
        ]);
    }
    
    /**
     * Search logs in Elasticsearch
     */
    public function searchLogs(Request $request)
    {
        // If Elasticsearch is not enabled, return local file logs
        if (!$this->elasticsearchEnabled || !$this->elasticsearch) {
            return $this->getLocalLogs($request);
        }
        
        $query = $request->get('q', '');
        $level = $request->get('level', '');
        $from = $request->get('from', 'now-1d');
        $size = $request->get('size', 50);
        
        $params = [
            'index' => 'laravel-logs-*',
            'body' => [
                'size' => $size,
                'sort' => [
                    [
                        '@timestamp' => [
                            'order' => 'desc'
                        ]
                    ]
                ],
                'query' => [
                    'bool' => [
                        'must' => []
                    ]
                ]
            ]
        ];
        
        // Add search query
        if (!empty($query)) {
            $params['body']['query']['bool']['must'][] = [
                'multi_match' => [
                    'query' => $query,
                    'fields' => ['message', 'context.*', 'extra.*']
                ]
            ];
        }
        
        // Add level filter
        if (!empty($level)) {
            $params['body']['query']['bool']['must'][] = [
                'match' => [
                    'level' => strtoupper($level)
                ]
            ];
        }
        
        // Add time range
        $params['body']['query']['bool']['filter'][] = [
            'range' => [
                '@timestamp' => [
                    'gte' => $from,
                    'lte' => 'now'
                ]
            ]
        ];
        
        try {
            $response = $this->elasticsearch->search($params);
            $hits = $response['hits']['hits'];
            
            $logs = array_map(function($hit) {
                return array_merge($hit['_source'], [
                    '_id' => $hit['_id'],
                    '_score' => $hit['_score']
                ]);
            }, $hits);
            
            return response()->json([
                'total' => $response['hits']['total']['value'],
                'logs' => $logs,
                'source' => 'elasticsearch'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Elasticsearch search failed', [
                'error' => $e->getMessage()
            ]);
            
            // Fallback to local logs
            return $this->getLocalLogs($request);
        }
    }
    
    /**
     * Get log statistics
     */
    public function getStatistics(Request $request)
    {
        // If Elasticsearch is not enabled, return basic statistics
        if (!$this->elasticsearchEnabled || !$this->elasticsearch) {
            return $this->getLocalStatistics();
        }
        
        $from = $request->get('from', 'now-7d');
        
        $params = [
            'index' => 'laravel-logs-*',
            'body' => [
                'size' => 0,
                'query' => [
                    'range' => [
                        '@timestamp' => [
                            'gte' => $from,
                            'lte' => 'now'
                        ]
                    ]
                ],
                'aggs' => [
                    'logs_per_level' => [
                        'terms' => [
                            'field' => 'level.keyword',
                            'size' => 10
                        ]
                    ],
                    'logs_per_hour' => [
                        'date_histogram' => [
                            'field' => '@timestamp',
                            'calendar_interval' => 'hour'
                        ]
                    ],
                    'top_errors' => [
                        'filter' => [
                            'term' => [
                                'level.keyword' => 'ERROR'
                            ]
                        ],
                        'aggs' => [
                            'error_messages' => [
                                'terms' => [
                                    'field' => 'message.keyword',
                                    'size' => 10
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        try {
            $response = $this->elasticsearch->search($params);
            
            return response()->json([
                'total_logs' => $response['hits']['total']['value'],
                'logs_per_level' => $response['aggregations']['logs_per_level']['buckets'] ?? [],
                'logs_per_hour' => $response['aggregations']['logs_per_hour']['buckets'] ?? [],
                'top_errors' => $response['aggregations']['top_errors']['error_messages']['buckets'] ?? [],
                'source' => 'elasticsearch'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Elasticsearch statistics failed', [
                'error' => $e->getMessage()
            ]);
            
            // Fallback to local statistics
            return $this->getLocalStatistics();
        }
    }
    
    /**
     * Get logs from local JSON files (fallback)
     */
    private function getLocalLogs(Request $request)
    {
        $logFile = storage_path('logs/json/laravel-json.log');
        
        if (!file_exists($logFile)) {
            return response()->json([
                'total' => 0,
                'logs' => [],
                'source' => 'local_file',
                'message' => 'No log file found'
            ]);
        }
        
        $logs = [];
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        // Parse JSON lines
        foreach ($lines as $line) {
            $log = json_decode($line, true);
            if ($log) {
                $logs[] = $log;
            }
        }
        
        // Apply basic filtering
        $query = strtolower($request->get('q', ''));
        $level = strtoupper($request->get('level', ''));
        
        $filteredLogs = array_filter($logs, function($log) use ($query, $level) {
            $matches = true;
            
            if ($query) {
                $message = strtolower($log['message'] ?? '');
                $matches = $matches && str_contains($message, strtolower($query));
            }
            
            if ($level) {
                $logLevel = strtoupper($log['level'] ?? '');
                $matches = $matches && ($logLevel === $level);
            }
            
            return $matches;
        });
        
        // Reverse to show latest first
        $filteredLogs = array_reverse($filteredLogs);
        $filteredLogs = array_slice($filteredLogs, 0, 50);
        
        return response()->json([
            'total' => count($filteredLogs),
            'logs' => $filteredLogs,
            'source' => 'local_file'
        ]);
    }
    
    /**
     * Get basic statistics from local files
     */
    private function getLocalStatistics()
    {
        $logFile = storage_path('logs/json/laravel-json.log');
        
        if (!file_exists($logFile)) {
            return response()->json([
                'total_logs' => 0,
                'logs_per_level' => [],
                'logs_per_hour' => [],
                'top_errors' => [],
                'source' => 'local_file',
                'message' => 'No log file found'
            ]);
        }
        
        $logs = [];
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $log = json_decode($line, true);
            if ($log) {
                $logs[] = $log;
            }
        }
        
        // Count logs by level
        $levelCounts = [];
        foreach ($logs as $log) {
            $level = strtoupper($log['level'] ?? 'UNKNOWN');
            $levelCounts[$level] = ($levelCounts[$level] ?? 0) + 1;
        }
        
        // Convert to Elasticsearch-like format
        $logsPerLevel = [];
        foreach ($levelCounts as $level => $count) {
            $logsPerLevel[] = [
                'key' => $level,
                'doc_count' => $count
            ];
        }
        
        return response()->json([
            'total_logs' => count($logs),
            'logs_per_level' => $logsPerLevel,
            'logs_per_hour' => [],
            'top_errors' => [],
            'source' => 'local_file'
        ]);
    }
}