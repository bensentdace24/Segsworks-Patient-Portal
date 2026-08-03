<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /** All hospital roles may access an appropriate appointment list. */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['doctor', 'nurse', 'receptionist', 'admin']);
    }

    /** Doctors may view only appointments linked to their local doctor ID. */
    public function view(User $user, Appointment $appointment): bool
    {
        return $user->role !== 'doctor' || $appointment->doctor_id === $user->id;
    }

    /** Receptionists, nurses, and admins may book appointments. */
    public function create(User $user): bool
    {
        return in_array($user->role, ['receptionist', 'nurse', 'admin']);
    }

    /** Operational staff may update; doctors are limited to assigned appointments. */
    public function update(User $user, Appointment $appointment): bool
    {
        return $user->role === 'admin'
            || $user->role === 'receptionist'
            || $user->role === 'nurse'
            || ($user->role === 'doctor' && $appointment->doctor_id === $user->id);
    }

    /** Only operational staff and admins may delete appointments. */
    public function delete(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, ['receptionist', 'nurse', 'admin']);
    }
}
