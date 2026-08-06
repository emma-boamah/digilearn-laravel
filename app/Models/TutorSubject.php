<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject_id',
        'hourly_rate',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
