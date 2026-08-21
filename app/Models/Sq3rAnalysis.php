<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sq3rAnalysis extends Model
{
    use HasFactory;

    protected $table = 'sq3r_analyses';

    protected $fillable = [
        'document_id',
        'file_path',
        'status',
        'structural_map',
        'question_list',
        'content_notes',
        'simple_summary',
        'final_guide',
        'structured_payload',
        'error_message',
    ];

    protected $casts = [
        'structured_payload' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
