<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimpleLogController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\PerformanceController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/logs/dashboard', function () {
    return view('logs.dashboard');
});

// Simple log routes (no Elasticsearch dependency)
Route::prefix('api')->group(function () {
    Route::get('/logs/generate', [SimpleLogController::class, 'generateLogs']);
    Route::get('/logs/search', [SimpleLogController::class, 'searchLogs']);
    Route::get('/logs/statistics', [SimpleLogController::class, 'getStatistics']);
});

Route::get('/test-error', function () {

    Log::error('Payment gateway failed during transaction', [
        'order_id' => 12345,
        'payment' => 'Razorpay'
    ]);

    return "Error log created";

});

Route::get('/performance/dashboard', [PerformanceController::class, 'index'])
    ->name('performance.dashboard');

Route::get('/slow-test', function(){

    sleep(2);

    return "Slow request completed";

});
