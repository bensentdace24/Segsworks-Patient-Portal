<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\ConsultationController;


// Public endpoint: validates credentials and returns a Sanctum token.
Route::post('/login', [AuthController::class, 'login']);

// Every route in this group requires a valid Bearer token.
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Shared read endpoints; controllers apply doctor-specific record filtering.
    Route::middleware('role:receptionist,doctor,nurse,admin')->group(function () {
        Route::get('/patients', [PatientController::class, 'index']);
        Route::get('/patients/{patient}', [PatientController::class, 'show']);

        Route::get('/appointments', [AppointmentController::class, 'index']);
        Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);

        Route::get('/doctors', [DoctorController::class, 'index']);
    });

    // Patient and appointment CRUD for operational staff and admins.
    Route::middleware('role:receptionist,nurse,admin')->group(function () {
        Route::post('/patients', [PatientController::class, 'store']);
        Route::put('/patients/{patient}', [PatientController::class, 'update']);
        Route::delete('/patients/{patient}', [PatientController::class, 'destroy']); #kani sya para ni sya sa patient delete

        Route::post('/appointments', [AppointmentController::class, 'store']);
        Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);
    });

    // Doctor-only endpoint that completes a case and creates a medical record.
    Route::middleware('role:doctor')->group(function () {
        Route::post(
            '/appointments/{appointment}/consult',
            [ConsultationController::class, 'store']
        );
    });

    // All authenticated hospital roles can read permitted medical records.
    Route::middleware('role:receptionist,nurse,doctor,admin')->group(function () {
        Route::get('/medical-records', [MedicalRecordController::class, 'index']);
        Route::get('/medical-records/{medicalRecord}', [MedicalRecordController::class, 'show']);
    });

    // Clinical staff and admins can add diagnoses, lab results, and other records.
    Route::middleware('role:nurse,doctor,admin')->group(function () {
        Route::post('/medical-records', [MedicalRecordController::class, 'store']);
    });
});
