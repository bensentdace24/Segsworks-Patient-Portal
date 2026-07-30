<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorSchedule extends Model
{
    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Whether a given Carbon datetime falls inside this schedule's
     * day-of-week + time window.
     */
    public function coversDateTime(\Illuminate\Support\Carbon $dateTime): bool
    {
        if (! $this->is_active || (int) $dateTime->dayOfWeek !== (int) $this->day_of_week) {
            return false;
        }

        $time = $dateTime->format('H:i:s');

        return $time >= $this->starts_at && $time <= $this->ends_at;
    }
}
