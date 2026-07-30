<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_logs', function (Blueprint $table) {

            $table->id();

            $table->string('method');
            
            $table->string('url');

            $table->integer('status_code');

            $table->decimal('response_time',10,2)
                  ->comment('Response time in milliseconds');

            $table->integer('memory_usage')
                  ->comment('Memory usage in bytes');

            $table->string('ip_address')
                  ->nullable();

            $table->unsignedBigInteger('user_id')
                  ->nullable();

            $table->boolean('is_slow')
                  ->default(false);

            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('performance_logs');
    }
};