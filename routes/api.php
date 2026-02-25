<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PatientController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // ─── Legacy endpoints (AppointmentController) ───────────────────────────
    Route::post('/patients', [PatientController::class, 'store']);
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);

    // ─── Booking endpoints (Hexagonal Architecture + Command/Query Separation) ─
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::patch('/bookings/{id}/reschedule', [BookingController::class, 'reschedule']);
    Route::post('/bookings/{id}/confirm', [BookingController::class, 'confirm']);
    Route::delete('/bookings/{id}', [BookingController::class, 'cancel']);

    // ─── Availability endpoints (Query only) ─────────────────────────────────
    Route::get('/resources/{resourceId}/slots', [AvailabilityController::class, 'availableSlots']);
    Route::get('/resources/{resourceId}/schedule', [AvailabilityController::class, 'resourceSchedule']);
});
