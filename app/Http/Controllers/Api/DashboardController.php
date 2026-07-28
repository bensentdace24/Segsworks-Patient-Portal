<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'staff' => $request->user(),
            'total_patients' => Patient::count(),
            'todays_appointments' => Appointment::whereDate('scheduled_at', today())->get(),
        ]);
    }
}
