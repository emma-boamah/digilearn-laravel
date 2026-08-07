<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'user_id',
        'total_items',
        'completed_items',
        'failed_items',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
