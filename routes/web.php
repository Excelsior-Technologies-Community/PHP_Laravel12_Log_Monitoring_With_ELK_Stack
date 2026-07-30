<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\SimpleLogController;
use App\Http\Controllers\PerformanceController;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Log Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/logs/dashboard', function () {
    return view('logs.dashboard');
})->name('logs.dashboard');

/*
|--------------------------------------------------------------------------
| Performance Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/performance/dashboard', [
    PerformanceController::class,
    'index'
])->name('performance.dashboard');

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api')->group(function () {

    Route::get('/logs/generate', [
        SimpleLogController::class,
        'generateLogs'
    ]);

    Route::get('/logs/search', [
        SimpleLogController::class,
        'searchLogs'
    ]);

    Route::get('/logs/statistics', [
        SimpleLogController::class,
        'getStatistics'
    ]);

    Route::get('/logs/export', [
        SimpleLogController::class,
        'exportLogs'
    ]);

    Route::delete('/logs/clear', [
        SimpleLogController::class,
        'clearLogs'
    ]);
});

/*
|--------------------------------------------------------------------------
| Test Error Route
|--------------------------------------------------------------------------
*/

Route::get('/test-error', function () {

    Log::error(
        'Payment gateway failed.',
        [
            'order_id' => rand(1000, 9999),
            'payment' => 'Razorpay'
        ]
    );

    return response()->json([
        'message' => 'Error log generated successfully.'
    ]);
});

/*
|--------------------------------------------------------------------------
| Slow Request
|--------------------------------------------------------------------------
*/

Route::get('/slow-test', function () {

    sleep(2);

    return response()->json([
        'message' => 'Slow request completed.'
    ]);
});
