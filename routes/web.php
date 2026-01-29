<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimpleLogController;

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