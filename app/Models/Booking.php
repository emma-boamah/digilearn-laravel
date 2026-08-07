<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'tutor_id',
        'subject_id',
        'credits_paid',
        'commission_amount',
        'start_time',
        'end_time',
        'status',
        'meeting_link',
        'tutor_notes',
        'student_rating',
        'student_feedback',
        'decline_reason',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected $casts = [
        'credits_paid' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'student_rating' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending_scheduling');
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['scheduled', 'confirmed']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}
