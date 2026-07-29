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
        $user = $request->user();

        if ($user->role === 'doctor') {
            return response()->json([
                'staff' => $user,
                'my_upcoming_appointments' => $user->doctorAppointments()
                    ->with('patient')
                    ->where('scheduled_at', '>=', now())
                    ->orderBy('scheduled_at')
                    ->get(),
            ]);
        }

        return response()->json([
            'staff' => $user,
            'total_patients' => Patient::count(),
            'todays_appointments' => Appointment::with(['patient', 'doctor'])
                ->whereDate('scheduled_at', today())
                ->get(),
        ]);
    }
}
