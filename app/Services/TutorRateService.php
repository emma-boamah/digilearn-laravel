<?php

namespace App\Services;

use App\Models\TutorProfile;
use App\Models\TutorSubject;
use App\Models\PlatformSetting;
use App\Models\User;

class TutorRateService
{
    /**
     * Get the hourly rate for a specific tutor and subject.
     */
    public function getHourlyRate(int $userId, int $subjectId): float
    {
        $tutorSubject = TutorSubject::where('user_id', $userId)
            ->where('subject_id', $subjectId)
            ->first();

        return $tutorSubject ? (float) $tutorSubject->hourly_rate : 0.00;
    }

    /**
     * Get formatted rate string or range for a tutor profile (e.g. "GHS 50.00/hr" or "GHS 40.00 - GHS 80.00/hr").
     */
    public function getFormattedRateRange(int $userId): string
    {
        $rates = TutorSubject::where('user_id', $userId)->pluck('hourly_rate');

        if ($rates->isEmpty()) {
            return 'GHS 0.00/hr';
        }

        $min = (float) $rates->min();
        $max = (float) $rates->max();

        if ($min === 0.0 && $max === 0.0) {
            return 'GHS 0.00/hr';
        }

        if ($min === $max) {
            return 'GHS ' . number_format($min, 2) . '/hr';
        }

        return 'GHS ' . number_format($min, 2) . ' - ' . number_format($max, 2) . '/hr';
    }

    /**
     * Calculate total credits, subtotal, and subscription discount for a booking.
     */
    public function calculateBookingCost(int $userId, int $subjectId, int $durationHours, bool $hasActiveSubscription = false): array
    {
        $hourlyRate = $this->getHourlyRate($userId, $subjectId);
        $subtotal = $hourlyRate * $durationHours;

        // Apply subscription discount if applicable (e.g. 10% discount for active subscribers)
        $discountPercentage = $hasActiveSubscription ? 0.10 : 0.00;
        $discountAmount = $subtotal * $discountPercentage;
        $totalCredits = max(0.00, $subtotal - $discountAmount);

        return [
            'hourly_rate' => $hourlyRate,
            'duration_hours' => $durationHours,
            'subtotal' => $subtotal,
            'discount_percentage' => $discountPercentage * 100,
            'discount_amount' => $discountAmount,
            'total_credits' => $totalCredits,
            'credits_formatted' => number_format($totalCredits, 2) . ' Credits',
        ];
    }

    /**
     * Calculate platform commission split and tutor net payout.
     */
    public function calculateCommissionSplit(float $totalAmount): array
    {
        $commissionRate = (float) PlatformSetting::getValue('tutor_commission_rate', 0.15);
        $platformFee = round($totalAmount * $commissionRate, 2);
        $tutorPayout = round($totalAmount - $platformFee, 2);

        return [
            'gross_total' => $totalAmount,
            'commission_rate' => $commissionRate,
            'commission_percentage' => $commissionRate * 100,
            'platform_fee' => $platformFee,
            'tutor_payout' => $tutorPayout,
        ];
    }
}
