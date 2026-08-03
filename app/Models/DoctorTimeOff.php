<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorTimeOff extends Model
{
    /** The database uses a singular table name for doctor leave periods. */
    protected $table = 'doctor_time_off';

    protected $fillable = [
        'doctor_id',
        'starts_at',
        'ends_at',
        'reason',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /** Doctor account that owns this leave period. */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /** Check whether a proposed appointment occurs during this time off. */
    public function overlaps(\Illuminate\Support\Carbon $dateTime): bool
    {
        return $dateTime->betweenIncluded($this->starts_at, $this->ends_at);
    }
}
