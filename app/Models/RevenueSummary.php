<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_type',
        'period_date',
        'revenue',
        'payments_count',
        'subscriptions_count',
        'metadata',
    ];

    protected $casts = [
        'period_date' => 'date',
        'revenue' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function scopeDaily($query)
    {
        return $query->where('period_type', 'daily');
    }

    public function scopeWeekly($query)
    {
        return $query->where('period_type', 'weekly');
    }

    public function scopeMonthly($query)
    {
        return $query->where('period_type', 'monthly');
    }

    public function scopeAnnual($query)
    {
        return $query->where('period_type', 'annual');
    }
}
