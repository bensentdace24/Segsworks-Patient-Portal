<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['doctor', 'nurse', 'receptionist', 'admin']);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->role !== 'doctor' || $appointment->doctor_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['receptionist', 'nurse', 'admin']);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->role === 'admin'
            || $user->role === 'receptionist'
            || $user->role === 'nurse'
            || ($user->role === 'doctor' && $appointment->doctor_id === $user->id);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, ['receptionist', 'nurse', 'admin']);
    }
}
