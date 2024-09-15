<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;

// Patient-specific routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/appointments', [AppointmentController::class, 'create']); // Create Appointment
    Route::put('/appointments/{id}', [AppointmentController::class, 'updateStatus']); // Update Appointment Status
    Route::get('/appointments', [AppointmentController::class, 'index']); // List of Appointments
});

// Doctor-specific routes
Route::middleware(['auth:sanctum', 'role:doctor'])->group(function () {
    Route::get('/doctor/appointments', [AppointmentController::class, 'viewForDoctor']); // View Appointments for Doctors
    Route::put('/doctor/appointments/{id}', [AppointmentController::class, 'updateStatusForDoctor']); // Doctor Update Status
});


Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
