<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'service' => 'NextCart API',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});