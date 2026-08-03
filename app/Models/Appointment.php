<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    /** Appointment fields accepted from validated frontend requests. */
    protected $fillable = [
        'patient_id',
        'doctor_name',
        'department',
        'scheduled_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    /** Clinical case opened automatically when this appointment is booked. */
    public function patientCase()
    {
        return $this->hasOne(PatientCase::class, 'appointment_id');
    }

    /** Optional local user relationship when a doctor_id is available. */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /** Patient scheduled for this appointment. */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /** Staff account that originally booked the appointment. */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
