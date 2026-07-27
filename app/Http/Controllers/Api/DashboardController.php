<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $patient = $request->user()->patient;

        return response()->json([
            'patient' => $patient,
            'upcoming_appointments' => $patient->appointments()
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')
                ->take(3)
                ->get(),
            'recent_records' => $patient->medicalRecords()
                ->latest('recorded_at')
                ->take(3)
                ->get(),
        ]);
    }
}
