<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'legal_name',
        'bio',
        'tagline',
        'qualifications',
        'intro_video_url',
        'scheduling_link',
        'scheduling_preference',
        'headshot_path',
        'id_type',
        'id_document_path',
        'id_document_back_path',
        'tax_document_path',
        'certificates_path',
        'test_video_path',
        'communication_handle',
        'payout_method',
        'payout_momo_network',
        'payout_momo_number',
        'payout_bank_name',
        'payout_bank_account_name',
        'payout_bank_account_number',
        'payout_bank_branch',
        'is_approved',
        'is_verified',
        'availability_status',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tutorSubjects()
    {
        return $this->hasMany(TutorSubject::class, 'user_id', 'user_id');
    }

    public function getMinRateAttribute()
    {
        return $this->tutorSubjects->min('hourly_rate') ?? 0;
    }

    public function getMaxRateAttribute()
    {
        return $this->tutorSubjects->max('hourly_rate') ?? 0;
    }

    public function getRateRangeAttribute()
    {
        $min = $this->min_rate;
        $max = $this->max_rate;

        if ($min == 0 && $max == 0) return 'GHS 0.00';
        if ($min == $max) return 'GHS ' . number_format($min, 2);
        return 'GHS ' . number_format($min, 2) . ' - ' . number_format($max, 2);
    }
}
