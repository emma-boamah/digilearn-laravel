<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteLockSetting extends Model
{
    protected $fillable = ['is_locked'];
    protected $casts = ['is_locked' => 'boolean'];
}
