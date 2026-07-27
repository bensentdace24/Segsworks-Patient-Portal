<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Appointment extends Model
{
    protected $fillable =
    [
        'patient_id',
        'doctor_name',
        'department',
        'scheduled_at',
        'status',
        'notes'
    ];


    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
