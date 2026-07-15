<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $guarded = [];

    protected $casts = [
        'subscription_starts_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'price_per_seat' => 'decimal:2',
    ];

    // ---------- Relationships ----------

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

    public function pricingPlan()
    {
        return $this->belongsTo(PricingPlan::class);
    }

    // ---------- Seat Tracking ----------

    /**
     * Count the number of active student seats currently used.
     */
    public function usedSeats(): int
    {
        return $this->users()->role('student')->count();
    }

    /**
     * Number of remaining available seats.
     */
    public function remainingSeats(): int
    {
        return max(0, $this->max_seats - $this->usedSeats());
    }

    /**
     * Whether the school has exceeded its seat limit.
     */
    public function isOverLimit(): bool
    {
        return $this->usedSeats() > $this->max_seats;
    }

    /**
     * Seat utilization percentage (capped at 100).
     */
    public function seatUtilization(): float
    {
        if ($this->max_seats === 0) {
            return 0;
        }
        return min(100, round(($this->usedSeats() / $this->max_seats) * 100, 1));
    }

    // ---------- Subscription Helpers ----------

    /**
     * Whether the subscription is currently active.
     */
    public function hasActiveSubscription(): bool
    {
        if (!$this->subscription_expires_at) {
            return $this->status === 'active'; // Legacy schools without expiry tracking
        }
        return $this->subscription_expires_at->isFuture();
    }

    /**
     * Whether the school is in its grace period (expired but not yet locked).
     */
    public function isInGracePeriod(): bool
    {
        if (!$this->subscription_expires_at || !$this->grace_period_ends_at) {
            return false;
        }
        return $this->subscription_expires_at->isPast() && $this->grace_period_ends_at->isFuture();
    }

    /**
     * Whether the subscription is fully expired (past grace period).
     */
    public function isExpired(): bool
    {
        if (!$this->subscription_expires_at) {
            return false;
        }

        $cutoff = $this->grace_period_ends_at ?? $this->subscription_expires_at;
        return $cutoff->isPast();
    }

    /**
     * Days until subscription expires (negative if already expired).
     */
    public function daysUntilExpiry(): ?int
    {
        if (!$this->subscription_expires_at) {
            return null;
        }
        return (int) now()->diffInDays($this->subscription_expires_at, false);
    }

    // ---------- Tier Helpers ----------

    /**
     * Configuration for each plan tier.
     */
    public static function tierConfig(): array
    {
        return [
            'basic' => [
                'label' => 'Basic',
                'max_seats' => 100,
                'subdomain' => false,
                'content_studio' => false,
                'analytics' => 'basic',
                'price_per_seat' => 20.00,
            ],
            'pro' => [
                'label' => 'School Pro',
                'max_seats' => 500,
                'subdomain' => true,
                'content_studio' => true,
                'analytics' => 'advanced',
                'price_per_seat' => 25.00,
            ],
            'enterprise' => [
                'label' => 'Enterprise',
                'max_seats' => 99999, // Effectively unlimited
                'subdomain' => true,
                'content_studio' => true,
                'analytics' => 'full',
                'price_per_seat' => 0, // Custom pricing
            ],
        ];
    }

    /**
     * Whether this school's tier includes the Content Studio.
     */
    public function hasContentStudio(): bool
    {
        $config = self::tierConfig()[$this->plan_tier] ?? null;
        return $config['content_studio'] ?? false;
    }
}
