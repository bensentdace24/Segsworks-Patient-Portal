<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'created_by',
        'department',
        'scheduled_at',
        'status',
        'notes',

    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    // Appointment.php — add
    public function patientCase()
    {
        return $this->hasOne(PatientCase::class, 'appointment_id');
    }

    #Doctors relationship
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    #to see kinsa ang nag buhat sa appointment
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
