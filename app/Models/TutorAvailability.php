<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_recurring',
        'specific_date',
        'is_blocked',
        'slot_duration_minutes',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_recurring' => 'boolean',
        'specific_date' => 'date',
        'is_blocked' => 'boolean',
        'slot_duration_minutes' => 'integer',
    ];

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }
}
