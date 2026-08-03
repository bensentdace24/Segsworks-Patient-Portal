<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Appointments linked to this user through doctor_id. */
    public function doctorAppointments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }


    public function patient()
    {
        return $this->hasOne(Patient::class);
    }


    /** Recurring availability windows configured for a doctor. */
    public function schedules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id');
    }

    /** Leave or unavailable periods configured for a doctor. */
    public function timeOff(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DoctorTimeOff::class, 'doctor_id');
    }

    /** Patients registered by this staff account. */
    public function createdPatients()
    {
        return $this->hasMany(Patient::class, 'created_by');
    }
}
