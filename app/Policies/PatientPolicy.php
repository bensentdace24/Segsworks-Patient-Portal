<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /** All authenticated hospital roles may browse patient records. */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['doctor', 'nurse', 'receptionist', 'admin']);
    }

    /** General view permission; doctor assignment is enforced in the controller. */
    public function view(User $user, Patient $patient): bool
    {
        return true;
    }

    /** Only operational staff and admins may register patients. */
    public function create(User $user): bool
    {
        return in_array($user->role, ['nurse', 'receptionist', 'admin']);
    }

    /** Doctors have read-only patient demographics. */
    public function update(User $user, Patient $patient): bool
    {
        return in_array($user->role, ['nurse', 'receptionist', 'admin']);
    }
}
