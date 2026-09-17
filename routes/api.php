<?php

use App\Http\Controllers\Api\DriverApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for SIMVENTRA Driver App & Fleet Tracking
|--------------------------------------------------------------------------
*/

// Public: Driver Login
Route::post('/driver/login', [DriverApiController::class, 'login']);

// Public/Internal: Fleet Live GPS Locations for OpenStreetMap monitoring
Route::get('/fleet/live-locations', [DriverApiController::class, 'getFleetLiveLocations']);

// Protected: Driver App Operations (via Sanctum Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/driver/push-token', [DriverApiController::class, 'storePushToken']);
    Route::get('/driver/task', [DriverApiController::class, 'getTask']);
    Route::post('/driver/task/{id}/confirm', [DriverApiController::class, 'confirmTask']);
    Route::post('/driver/task/{id}/start', [DriverApiController::class, 'startTrip']);
    Route::post('/driver/task/{id}/location', [DriverApiController::class, 'sendLocation']);
    Route::post('/driver/task/{id}/complete', [DriverApiController::class, 'completeTrip']);
});
