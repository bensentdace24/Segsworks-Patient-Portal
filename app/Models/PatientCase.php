<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientCase extends Model
{
    protected $table = 'cases';

    protected $fillable = [
        'patient_id',
        'appointment_id',
        'opened_by',
        'completed_by',
        'status',
        'notes',
        'diagnosis',
        'consultation_notes',
        'prescription',
        'treatment_plan',
        'follow_up_instructions',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (PatientCase $case) {
            $case->case_number = self::generateCaseNumber();
        });
    }

    public static function generateCaseNumber(): string
    {
        $year = now()->year;
        $count = self::whereYear('created_at', $year)->count() + 1;

        return sprintf('CASE-%d-%06d', $year, $count);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }


    #this is for appointmentt
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
