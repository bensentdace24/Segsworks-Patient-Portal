<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['doctor', 'nurse', 'receptionist', 'admin']);
    }

    public function view(User $user, Patient $patient): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['nurse', 'receptionist', 'admin']);
    }

    public function update(User $user, Patient $patient): bool
    {
        return in_array($user->role, ['nurse', 'receptionist', 'admin']);
    }
}
