<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MedicalRecord;

class DashboardController extends Controller
{
    /** Return dashboard data shaped specifically for the authenticated user's role. */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            // Admins receive system-wide totals plus operational notifications.
            return response()->json([
                'staff' => $user,

                'total_users' => User::count(),
                'total_patients' => Patient::count(),
                'total_appointments' => Appointment::count(),
                'total_records' => MedicalRecord::count(),

                'notifications' => $this->getNotifications($user),
            ]);
        }

        if ($user->role === 'doctor') {
            // Doctors receive only upcoming appointments assigned to their name.
            $doctorName = strtolower(trim($user->name));

            return response()->json([
                'staff' => $user,

                'my_upcoming_appointments' => Appointment::with('patient')
                    ->whereRaw('LOWER(TRIM(doctor_name)) = ?', [$doctorName])
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->orderBy('scheduled_at')
                    ->get(),

                'notifications' => $this->getNotifications($user),
            ]);
        }
        // Nurses and receptionists receive patient totals and today's schedule.
        return response()->json([
            'staff' => $user,
            'total_patients' => Patient::count(),
            'todays_appointments' => Appointment::with(['patient', 'doctor'])
                ->whereDate('scheduled_at', today())
                ->get(),
            'notifications' => $this->getNotifications($user),
        ]);
    }

    /** Build live reminders from appointment dates and statuses instead of stored messages. */
    private function getNotifications($user): array
    {
        $notifications = [];

        // Find confirmed appointments beginning within the next hour.
        $upcomingQuery = Appointment::with('patient')
            ->where('status', 'confirmed')
            ->whereBetween('scheduled_at', [now(), now()->addHour()]);

        if ($user->role === 'doctor') {
            $upcomingQuery->whereRaw('LOWER(TRIM(doctor_name)) = ?', [
                strtolower(trim($user->name)),
            ]);
        }
        foreach ($upcomingQuery->get() as $appt) {
            $notifications[] = [
                'type' => 'reminder',
                'message' => "{$appt->patient->full_name}'s appointment is coming up at " . $appt->scheduled_at->format('g:i A'),
            ];
        }

        // Count today's appointments that staff still need to confirm.
        $pendingTodayQuery = Appointment::whereDate('scheduled_at', today())
            ->where('status', 'pending');

        if ($user->role === 'doctor') {
            $pendingTodayQuery->whereRaw('LOWER(TRIM(doctor_name)) = ?', [
                strtolower(trim($user->name)),
            ]);
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
