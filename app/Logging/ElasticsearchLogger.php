<?php

namespace App\Logging;

use Monolog\Logger;
use Elastic\Elasticsearch\ClientBuilder;
use Monolog\Handler\ElasticsearchHandler;
use Monolog\Formatter\ElasticsearchFormatter;

class ElasticsearchLogger
{
    public function __invoke(array $config)
    {
        $client = ClientBuilder::create()
            ->setHosts($config['hosts'])
            ->build();
        
        $handler = new ElasticsearchHandler(
            $client,
            [
                'index' => $config['index'],
                'type' => '_doc',
            ]
        );
        
        $formatter = new ElasticsearchFormatter(
            $config['index'],
            '_doc'
        );
        
        $handler->setFormatter($formatter);
        
        $logger = new Logger('elasticsearch');
        $logger->pushHandler($handler);
        
        return $logger;
    }
}