<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $guarded = [];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function students()
    {
        return $this->hasMany(User::class, 'school_class_id');
    }
}
