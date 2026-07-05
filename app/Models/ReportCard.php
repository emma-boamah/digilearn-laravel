<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCard extends Model
{
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function details()
    {
        return $this->hasMany(ReportCardDetail::class);
    }
}
