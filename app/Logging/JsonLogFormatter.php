<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter as BaseJsonFormatter;

class JsonLogFormatter extends BaseJsonFormatter
{
    public function format(array $record): string
    {
        // Add extra fields
        $record['extra']['host'] = gethostname();
        $record['extra']['ip'] = request()->ip();
        $record['extra']['user_id'] = auth()->id() ?? null;
        $record['extra']['url'] = request()->fullUrl();
        $record['extra']['method'] = request()->method();
        
        // Format timestamp
        $record['datetime'] = $record['datetime']->format('c');
        
        return parent::format($record);
    }
}