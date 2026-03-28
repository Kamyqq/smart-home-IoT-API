<?php

use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\MeasurementController;
use Illuminate\Support\Facades\Route;

Route::post('/measurements', [MeasurementController::class, 'store'])->middleware(['auth.device', 'throttle:device_api']);
Route::get('/room-actions', [RoomController::class, 'decisions'])->middleware(['auth.device', 'throttle:device_api']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/rooms/{room}/measurements', [MeasurementController::class, 'index'])->name('room.measurements');
});
