<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'tutor_id',
        'amount',
        'status',
        'payout_method',
        'paystack_recipient_code',
        'paystack_transfer_code',
        'reference',
        'bank_name',
        'account_number',
        'processed_at',
        'failure_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }
}
