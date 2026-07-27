<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;



class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $patient = $request->user()->patient;

        return view('dashboard', [
            'patient' => $patient,
            'appointments' => $patient->appointments()->where('scheduled_at', '>=', now())->orderBy('scheduled_at')->take(3)->get(),
            'recentRecords' => $patient->medicalRecords()->latest('recorded_at')->take(3)->get(),
        ]);
    }
}
