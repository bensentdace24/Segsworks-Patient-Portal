<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientCase extends Model
{
    /** Laravel uses the existing `cases` table for this clinical model. */
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

        // Assign a human-readable case number before insertion.
        static::creating(function (PatientCase $case) {
            $case->case_number = self::generateCaseNumber();
        });
    }

    /** Generate the next yearly case number without reusing deleted sequences. */
    public static function generateCaseNumber(): string
    {
        $year = now()->year;
        $prefix = "CASE-{$year}-";

        // Count-based numbering can reuse an existing number after a deletion.
        // Lock the latest row while the surrounding transaction chooses the next
        // yearly sequence so two requests cannot select the same value.
        $latestCaseNumber = self::query()
            ->where('case_number', 'like', "{$prefix}%")
            ->orderByDesc('case_number')
            ->lockForUpdate()
            ->value('case_number');

        $nextSequence = $latestCaseNumber
            ? ((int) substr($latestCaseNumber, strlen($prefix))) + 1
            : 1;

        return sprintf('%s%06d', $prefix, $nextSequence);
    }

    /** Patient whose clinical episode this case represents. */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }


    /** Appointment that opened this case. */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /** Doctor who completed and closed the case. */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
