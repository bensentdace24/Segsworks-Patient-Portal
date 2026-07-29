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
            // ...existing doctor branch stays the same
        }

        return response()->json([
            'staff' => $user,
            'total_patients' => Patient::count(),
            'todays_appointments' => Appointment::with('patient')
                ->whereDate('scheduled_at', today())
                ->get(),
            'notifications' => $this->getNotifications(),
        ]);
    }

    private function getNotifications(): array
    {
        $notifications = [];

        // Reminder: appointments starting within the next hour
        $upcoming = Appointment::with('patient')
            ->where('status', 'confirmed')
            ->whereBetween('scheduled_at', [now(), now()->addHour()])
            ->get();

        foreach ($upcoming as $appt) {
            $notifications[] = [
                'type' => 'reminder',
                'message' => "{$appt->patient->full_name}'s appointment is coming up at " . $appt->scheduled_at->format('g:i A'),
            ];
        }

        // Alert: pending appointments still not confirmed for today
        $pendingToday = Appointment::whereDate('scheduled_at', today())
            ->where('status', 'pending')
            ->count();

        if ($pendingToday > 0) {
            $notifications[] = [
                'type' => 'alert',
                'message' => "{$pendingToday} appointment(s) today still awaiting confirmation",
            ];
        }

        return $notifications;
    }
}
