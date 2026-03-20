<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'creator_id',
        'assignee_id',
        'status',
    ];

    /**
     * Get the admin who is performing the task.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Get the admin who assigned the task.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
