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
                'notifications' => $this->getNotifications($user),
            ]);
        }

        return response()->json([
            'staff' => $user,
            'total_patients' => Patient::count(),
            'todays_appointments' => Appointment::with(['patient', 'doctor'])
                ->whereDate('scheduled_at', today())
                ->get(),
            'notifications' => $this->getNotifications($user),
        ]);
    }

    private function getNotifications($user): array
    {
        $notifications = [];

        $upcomingQuery = Appointment::with('patient')
            ->where('status', 'confirmed')
            ->whereBetween('scheduled_at', [now(), now()->addHour()]);

        if ($user->role === 'doctor') {
            $upcomingQuery->where('doctor_id', $user->id);
        }

        foreach ($upcomingQuery->get() as $appt) {
            $notifications[] = [
                'type' => 'reminder',
                'message' => "{$appt->patient->full_name}'s appointment is coming up at " . $appt->scheduled_at->format('g:i A'),
            ];
        }

        $pendingTodayQuery = Appointment::whereDate('scheduled_at', today())
            ->where('status', 'pending');

        if ($user->role === 'doctor') {
            $pendingTodayQuery->where('doctor_id', $user->id);
        }

        $pendingToday = $pendingTodayQuery->count();

        if ($pendingToday > 0) {
            $notifications[] = [
                'type' => 'alert',
                'message' => "{$pendingToday} appointment(s) today still awaiting confirmation",
            ];
        }

        return $notifications;
    }
}
