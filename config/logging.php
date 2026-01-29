<?php

return [
    'default' => env('LOG_CHANNEL', 'stack'),
    
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'elasticsearch'],
            'ignore_exceptions' => false,
        ],
        
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],
        
        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],
        
        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        
        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        
        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],
        
        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
        
        // JSON Log channel for ELK Stack
        'json' => [
            'driver' => 'single',
            'path' => storage_path('logs/json/laravel-json.log'),
            'level' => 'debug',
            'formatter' => Monolog\Formatter\JsonFormatter::class,
            'formatter_with' => [
                'includeStacktraces' => true,
            ],
        ],
        
        // Elasticsearch channel
        'elasticsearch' => [
            'driver' => 'custom',
            'via' => App\Logging\ElasticsearchLogger::class,
            'level' => 'debug',
            'hosts' => [env('ELASTICSEARCH_HOST', 'http://localhost:9200')],
            'index' => env('ELASTICSEARCH_INDEX', 'laravel-logs'),
        ],
    ],
];