<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\RevenueSummary;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AggregateRevenue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:aggregate-revenue {--period=all : The period to aggregate (daily, weekly, monthly, annual, all)} {--date= : The reference date (defaults to today, format Y-m-d)} {--back=30 : Number of days to look back for "all" or catch-up}';

    protected $description = 'Aggregate raw payment data into the revenue_summaries table for fast reporting.';

    public function handle()
    {
        $periodType = $this->option('period');
        $dateStr = $this->option('date') ?: now()->toDateString();
        $date = Carbon::parse($dateStr);
        $backDays = (int) $this->option('back');

        // Automatic Backfill: If table is empty and we're doing "all", 
        // increase backDays to 365 to catch historical data.
        if ($periodType === 'all' && RevenueSummary::count() === 0) {
            $this->warn('RevenueSummary table is empty. Triggering automatic 365-day backfill...');
            $backDays = 365;
        }

        $this->info("Starting revenue aggregation for period: {$periodType} on {$date->toDateString()} (Back: {$backDays} days)");

        if ($periodType === 'all') {
            $bar = $this->output->createProgressBar($backDays + 1);
            $bar->start();

            for ($i = 0; $i <= $backDays; $i++) {
                $d = $date->copy()->subDays($i);
                $this->aggregate($d, 'daily');
                
                // For "all", we always update the parent periods for every day to ensure completeness
                $this->aggregate($d, 'weekly');
                $this->aggregate($d, 'monthly');
                $this->aggregate($d, 'annual');
                
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
        } else {
            $this->aggregate($date, $periodType);
        }

        $this->info('Aggregation completed successfully.');
        return 0;
    }

    private function aggregate(Carbon $date, string $periodType)
    {
        $start = $date->copy();
        $end = $date->copy();

        switch ($periodType) {
            case 'daily':
                $start->startOfDay();
                $end->endOfDay();
                break;
            case 'weekly':
                $start->startOfWeek();
                $end->endOfWeek();
                break;
            case 'monthly':
                $start->startOfMonth();
                $end->endOfMonth();
                break;
            case 'annual':
                $start->startOfYear();
                $end->endOfYear();
                break;
            default:
                $this->error("Invalid period type: {$periodType}");
                return;
        }

        $revenue = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');

        $paymentsCount = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$start, $end])
            ->count();

        $subscriptionsCount = UserSubscription::whereBetween('created_at', [$start, $end])
            ->count();

        // Bonus: Store per-plan breakdown in metadata
        $planBreakdown = Payment::where('payments.status', 'success')
            ->whereBetween('payments.paid_at', [$start, $end])
            ->join('pricing_plans', 'payments.pricing_plan_id', '=', 'pricing_plans.id')
            ->selectRaw('pricing_plans.name, SUM(payments.amount) as total')
            ->groupBy('pricing_plans.name')
            ->pluck('total', 'name')
            ->toArray();

        RevenueSummary::updateOrCreate(
            ['period_type' => $periodType, 'period_date' => $start->toDateString()],
            [
                'revenue' => $revenue,
                'payments_count' => $paymentsCount,
                'subscriptions_count' => $subscriptionsCount,
                'metadata' => [
                    'plan_breakdown' => $planBreakdown,
                    'calculated_at' => now()->toIso8601String(),
                ]
            ]
        );
    }
}
