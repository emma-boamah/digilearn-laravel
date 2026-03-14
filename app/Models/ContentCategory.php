<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    /**
     * Get all of the videos that are assigned this category.
     */
    public function videos()
    {
        return $this->morphedByMany(Video::class, 'categorizable');
    }

    /**
     * Get all of the documents that are assigned this category.
     */
    public function documents()
    {
        return $this->morphedByMany(Document::class, 'categorizable');
    }

    /**
     * Get all of the quizzes that are assigned this category.
     */
    public function quizzes()
    {
        return $this->morphedByMany(Quiz::class, 'categorizable');
    }
}
