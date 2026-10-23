<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\CancelAppointmentController;
use App\Http\Controllers\Api\ProfessionalController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::get('professionals', [ProfessionalController::class, 'index']);
    Route::get('professionals/{professional}/availability', AvailabilityController::class);

    Route::post('appointments', [AppointmentController::class, 'store'])->middleware('throttle:10,1');
    Route::get('appointments/{appointment}', [AppointmentController::class, 'show']);
    Route::post('appointments/{appointment}/cancel', CancelAppointmentController::class);
});
