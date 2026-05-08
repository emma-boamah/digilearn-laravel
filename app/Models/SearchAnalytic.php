<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchAnalytic extends Model
{
    protected $fillable = [
        'user_id',
        'query',
        'domain',
        'hits',
        'last_searched_at',
    ];

    protected $casts = [
        'last_searched_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
