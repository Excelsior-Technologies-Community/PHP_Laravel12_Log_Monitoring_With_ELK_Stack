<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateTestLogs extends Command
{
    protected $signature = 'logs:generate {count=10}';
    protected $description = 'Generate test logs for ELK monitoring';

    public function handle()
    {
        $count = $this->argument('count');
        $levels = ['INFO', 'WARNING', 'ERROR', 'DEBUG'];
        
        $this->info("Generating {$count} test logs...");
        
        $bar = $this->output->createProgressBar($count);
        
        for ($i = 1; $i <= $count; $i++) {
            $level = $levels[array_rand($levels)];
            $message = "Test log {$i} - " . $this->getRandomMessage();
            
            $context = [
                'iteration' => $i,
                'random_data' => [
                    'id' => uniqid(),
                    'timestamp' => now()->toISOString(),
                    'value' => rand(1, 1000)
                ]
            ];
            
            switch ($level) {
                case 'INFO':
                    Log::info($message, $context);
                    break;
                case 'WARNING':
                    Log::warning($message, $context);
                    break;
                case 'ERROR':
                    Log::error($message, $context);
                    break;
                case 'DEBUG':
                    Log::debug($message, $context);
                    break;
            }
            
            $bar->advance();
            usleep(100000); // 0.1 second delay
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Generated {$count} test logs successfully!");
        
        return 0;
    }
    
    private function getRandomMessage()
    {
        $messages = [
            "User logged in successfully",
            "Database query executed",
            "API request received",
            "File uploaded",
            "Cache cleared",
            "Email sent",
            "Payment processed",
            "Report generated",
            "Backup completed",
            "System maintenance"
        ];
        
        return $messages[array_rand($messages)];
    }
}