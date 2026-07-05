<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date_administered' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function scores()
    {
        return $this->hasMany(AssessmentScore::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Check if this assessment is a CBT (linked to a quiz).
     */
    public function isCbt(): bool
    {
        return $this->type === 'cbt' && $this->quiz_id !== null;
    }
}
