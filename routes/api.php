<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PatientController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::apiResource('appointments', AppointmentController::class);
    Route::apiResource('medical-records', MedicalRecordController::class)->only(['index', 'show']);
    Route::get('/doctors', [DoctorController::class, 'index']);

    #patient route
    Route::apiResource('patients', PatientController::class)->only(['index', 'store', 'show', 'update']);



    #doctors-ppicker endpoint
    Route::get('/doctors-list', function () {
        return response()->json(
            \App\Models\User::where('role', 'doctor')->get(['id', 'name'])
        );
    });
});
