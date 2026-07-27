<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class MedicalRecord extends Model
{
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

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
