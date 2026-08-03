<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class MedicalRecord extends Model
{
    /** Clinical record fields allowed for mass assignment. */
    protected $fillable = [
        'patient_id',
        'record_type',
        'title',
        'summary',
        'recorded_at'
    ];

    protected $casts = [
        'recorded_at' => 'date'
    ];

    /** Patient who owns this medical record. */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /** Optional source appointment when the schema provides appointment_id. */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
