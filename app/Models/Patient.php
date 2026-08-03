<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Patient extends Model
{
    /** Fields allowed when creating or updating a patient through Eloquent. */
    protected $fillable = [
        'created_by',
        'full_name',
        'date_of_birth',
        'gender',
        'phone',
        'address',
        'blood_type',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        // Generate the PHN automatically before the first database insert.
        static::creating(function (Patient $patient) {
            $patient->phn = self::generatePhn();
        });
    }

    /** Generate the next yearly PHN from the highest existing sequence. */
    public static function generatePhn(): string
    {
        $year = now()->year;
        $prefix = "PHN-{$year}-";

        // Lock the latest row to reduce duplicate numbers during concurrent requests.
        $latestPhn = self::query()
            ->where('phn', 'like', "{$prefix}%")
            ->orderByDesc('phn')
            ->lockForUpdate()
            ->value('phn');

        $nextSequence = $latestPhn
            ? ((int) substr($latestPhn, strlen($prefix))) + 1
            : 1;

        return sprintf('%s%06d', $prefix, $nextSequence);
    }

    /** Staff account that registered this patient. */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** All clinical cases opened for this patient. */
    public function cases(): HasMany
    {
        return $this->hasMany(PatientCase::class);
    }

    /** All past and future appointments belonging to this patient. */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** Permanent diagnoses, lab results, and consultation summaries. */
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function patientCase(): HasOne
    {
        return $this->hasOne(PatientCase::class, 'appointment_id');
    }


    public function createdPatients(): HasMany
    {
        return $this->hasMany(Patient::class, 'created_by');
    }
}
