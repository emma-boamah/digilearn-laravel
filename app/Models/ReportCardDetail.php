<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCardDetail extends Model
{
    protected $guarded = [];

    public function reportCard()
    {
        return $this->belongsTo(ReportCard::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
