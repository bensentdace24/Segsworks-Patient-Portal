<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorTimeOff extends Model
{
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

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function overlaps(\Illuminate\Support\Carbon $dateTime): bool
    {
        return $dateTime->betweenIncluded($this->starts_at, $this->ends_at);
    }
}
