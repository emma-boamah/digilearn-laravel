<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuidedLearningProgress extends Model
{
    use HasFactory;

    protected $table = 'guided_learning_progress';

    protected $fillable = [
        'user_id',
        'curriculum_indicator_id',
        'textbook_section_id',
        'status',
        'completed_at',
        'time_spent_seconds',
        'quiz_score',
        'quiz_attempts',
        'quiz_passed_at',
        'notes',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'quiz_passed_at' => 'datetime',
        'time_spent_seconds' => 'integer',
        'quiz_score' => 'integer',
        'quiz_attempts' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(CurriculumIndicator::class, 'curriculum_indicator_id');
    }

    public function textbookSection(): BelongsTo
    {
        return $this->belongsTo(TextbookSection::class, 'textbook_section_id');
    }
}
