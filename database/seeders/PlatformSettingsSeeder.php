<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlatformSetting;

class PlatformSettingsSeeder extends Seeder
{
    public function run()
    {
        PlatformSetting::setValue('tutor_commission_rate', 0.15, 'decimal', 'Percentage commission taken by platform on tutor bookings (e.g. 0.15 = 15%)');
        PlatformSetting::setValue('min_payout_amount', 50.00, 'decimal', 'Minimum credit balance required for tutor payout request (GHS)');
        PlatformSetting::setValue('payout_processing_days', 3, 'integer', 'Estimated processing duration for payouts in business days');
    }
}
