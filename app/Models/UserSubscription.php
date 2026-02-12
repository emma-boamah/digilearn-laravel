<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pricing_plan_id',
        'status',
        'started_at',
        'expires_at',
        'trial_ends_at',
        'amount_paid',
        'payment_method',
        'transaction_id',
        'metadata',
        'grace_period_ends_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'amount_paid' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the subscription
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the pricing plan
     */
    public function pricingPlan()
    {
        return $this->belongsTo(PricingPlan::class);
    }

    /**
     * Check if subscription is active
     */
    public function isActive()
    {
        return in_array(strtolower($this->status), ['active']) &&
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Check if subscription is in trial period
     */
    public function isInTrial()
    {
        return in_array(strtolower($this->status), ['trial']) &&
               $this->trial_ends_at &&
               $this->trial_ends_at->isFuture();
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired()
    {
        return $this->status === 'expired' ||
               ($this->expires_at && $this->expires_at->isPast() && !$this->isInGracePeriod());
    }

    /**
     * Check if subscription is in grace period
     */
    public function isInGracePeriod(): bool
    {
        return $this->status === 'grace_period'
            && $this->grace_period_ends_at
            && $this->grace_period_ends_at->isFuture();
    }

    /**
     * Get days remaining in subscription
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->expires_at) {
            return null;
        }

        return max(0, round(now()->diffInDays($this->expires_at, true)));
    }

    /**
     * Get trial days remaining
     */
    public function getTrialDaysRemainingAttribute()
    {
        if (!$this->trial_ends_at) {
            return null;
        }

        return max(0, round(now()->diffInDays($this->trial_ends_at, true)));
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($active) {
                $active->where('status', 'active')
                       ->where(function ($dates) {
                           $dates->whereNull('expires_at')
                                 ->orWhere('expires_at', '>', now());
                       });
            })->orWhere(function ($grace) {
                $grace->where('status', 'grace_period')
                      ->where('grace_period_ends_at', '>', now());
            });
        });
    }

    /**
     * Scope for trial subscriptions
     */
    public function scopeTrial($query)
    {
        return $query->where('status', 'trial')
                    ->where('trial_ends_at', '>', now());
    }
}
