<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $guarded = [];

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function schoolClasses()
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
