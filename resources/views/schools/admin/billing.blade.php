@extends('schools.admin.layout')

@section('title', 'Subscription & Billing')

@section('styles')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        .billing-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        @media (max-width: 900px) {
            .billing-grid {
                grid-template-columns: 1fr;
            }
        }

        .billing-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 28px;
        }

        .billing-card-title {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 16px;
        }

        /* Seat Gauge */
        .gauge-container {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .gauge-circle {
            width: 120px;
            height: 120px;
            position: relative;
            flex-shrink: 0;
        }

        .gauge-circle svg {
            transform: rotate(-90deg);
        }

        .gauge-bg {
            fill: none;
            stroke: #e2e8f0;
            stroke-width: 8;
        }

        .gauge-fill {
            fill: none;
            stroke-width: 8;
            stroke-linecap: round;
            transition: stroke-dashoffset 0.6s ease;
        }

        .gauge-text {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .gauge-value {
            font-size: 1.6rem;
            font-weight: 700;
        }

        .gauge-label {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .gauge-info {
            flex: 1;
        }

        .gauge-stat {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
        }

        .gauge-stat:last-child {
            border-bottom: none;
        }

        .gauge-stat-label {
            color: var(--text-muted);
        }

        .gauge-stat-value {
            font-weight: 600;
        }

        /* Plan Banner */
        .plan-banner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), #1e40af);
            color: white;
            margin-bottom: 32px;
        }

        .plan-banner-left h2 {
            font-size: 1.4rem;
            margin-bottom: 4px;
        }

        .plan-banner-left p {
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .plan-badge-tier {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Sub Status */
        .sub-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .sub-status.active {
            background: #dcfce7;
            color: #166534;
        }

        .sub-status.grace {
            background: #fef3c7;
            color: #92400e;
        }

        .sub-status.expired {
            background: #fef2f2;
            color: #991b1b;
        }

        /* Payment History */
        .payment-table {
            width: 100%;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .payment-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .payment-table th {
            text-align: left;
            padding: 12px 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border);
            background: var(--bg);
        }

        .payment-table td {
            padding: 14px 20px;
            font-size: 0.9rem;
            border-bottom: 1px solid var(--border);
        }

        .payment-table tr:last-child td {
            border-bottom: none;
        }

        .amount-badge {
            font-weight: 600;
            color: var(--success);
        }

        .status-pill {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-pill.success {
            background: #dcfce7;
            color: #166534;
        }

        .status-pill.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-pill.failed {
            background: #fef2f2;
            color: #991b1b;
        }

        /* Upgrade Card */
        .upgrade-card {
            background: var(--bg-card);
            border: 2px dashed var(--primary);
            border-radius: 12px;
            padding: 28px;
            text-align: center;
        }

        .upgrade-card h3 {
            margin-bottom: 8px;
        }

        .upgrade-card p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
    </style>
@endsection

@section('content')
    <!-- Plan Banner -->
    <div class="plan-banner">
        <div class="plan-banner-left">
            <h2>{{ ucfirst($school->plan_tier) }} Plan</h2>
            <p>{{ $school->billing_cycle === 'term' ? 'Billed per term' : 'Billed annually' }} ·
                GH₵{{ number_format($school->price_per_seat, 2) }}/seat</p>
        </div>
        <div class="plan-badge-tier">
            @if($school->hasActiveSubscription())
                <span class="sub-status active"><i class="fas fa-circle" style="font-size: 6px;"></i> Active</span>
            @elseif($school->isInGracePeriod())
                <span class="sub-status grace"><i class="fas fa-exclamation-triangle" style="font-size: 10px;"></i> Grace
                    Period</span>
            @else
                <span class="sub-status expired"><i class="fas fa-times-circle" style="font-size: 10px;"></i> Expired</span>
            @endif
        </div>
    </div>

    <div class="billing-grid">
        <!-- Seat Usage -->
        <div class="billing-card">
            <div class="billing-card-title">Seat Usage</div>

            @php
                $used = $school->usedSeats();
                $max = $school->max_seats;
                $pct = $school->seatUtilization();
                $circumference = 2 * 3.14159 * 50;
                $offset = $circumference - ($pct / 100) * $circumference;
                $gaugeColor = $pct > 90 ? '#dc2626' : ($pct > 70 ? '#d97706' : '#059669');
            @endphp

            <div class="gauge-container">
                <div class="gauge-circle">
                    <svg width="120" height="120" viewBox="0 0 120 120">
                        <circle class="gauge-bg" cx="60" cy="60" r="50" />
                        <circle class="gauge-fill" cx="60" cy="60" r="50" stroke="{{ $gaugeColor }}"
                            stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}" />
                    </svg>
                    <div class="gauge-text">
                        <div class="gauge-value" style="color: {{ $gaugeColor }}">{{ round($pct) }}%</div>
                        <div class="gauge-label">Utilized</div>
                    </div>
                </div>
                <div class="gauge-info">
                    <div class="gauge-stat">
                        <span class="gauge-stat-label">Students Enrolled</span>
                        <span class="gauge-stat-value">{{ $used }}</span>
                    </div>
                    <div class="gauge-stat">
                        <span class="gauge-stat-label">Max Seats</span>
                        <span class="gauge-stat-value">{{ $max >= 99999 ? 'Unlimited' : number_format($max) }}</span>
                    </div>
                    <div class="gauge-stat">
                        <span class="gauge-stat-label">Remaining</span>
                        <span class="gauge-stat-value"
                            style="color: {{ $school->remainingSeats() < 10 ? '#dc2626' : 'inherit' }}">
                            {{ $max >= 99999 ? '∞' : $school->remainingSeats() }}
                        </span>
                    </div>
                </div>
            </div>

            @if($school->isOverLimit())
                <div
                    style="margin-top: 16px; padding: 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; font-size: 0.85rem; color: #991b1b;">
                    <i class="fas fa-exclamation-triangle"></i> You have exceeded your seat limit. Please upgrade your plan or
                    remove inactive students.
                </div>
            @endif
        </div>

        <!-- Subscription Details -->
        <div class="billing-card">
            <div class="billing-card-title">Subscription</div>

            <div class="gauge-stat">
                <span class="gauge-stat-label">Started</span>
                <span
                    class="gauge-stat-value">{{ $school->subscription_starts_at ? $school->subscription_starts_at->format('M d, Y') : 'N/A' }}</span>
            </div>
            <div class="gauge-stat">
                <span class="gauge-stat-label">Expires</span>
                <span class="gauge-stat-value">
                    {{ $school->subscription_expires_at ? $school->subscription_expires_at->format('M d, Y') : 'N/A' }}
                    @if($school->daysUntilExpiry() !== null && $school->daysUntilExpiry() > 0 && $school->daysUntilExpiry() <= 30)
                        <span style="color: #d97706; font-size: 0.75rem;">({{ $school->daysUntilExpiry() }} days left)</span>
                    @endif
                </span>
            </div>
            <div class="gauge-stat">
                <span class="gauge-stat-label">Grace Period Until</span>
                <span
                    class="gauge-stat-value">{{ $school->grace_period_ends_at ? $school->grace_period_ends_at->format('M d, Y') : 'N/A' }}</span>
            </div>
            <div class="gauge-stat">
                <span class="gauge-stat-label">Current Invoice</span>
                <span
                    class="gauge-stat-value amount-badge">GH₵{{ number_format($used * $school->price_per_seat, 2) }}</span>
            </div>

            @if($school->subscription_expires_at && $school->daysUntilExpiry() !== null && $school->daysUntilExpiry() <= 30)
                <div style="margin-top: 16px;">
                    <a href="{{ route('school.admin.billing.renew') }}" class="sa-btn sa-btn-primary"
                        style="width: 100%; justify-content: center;">
                        <i class="fas fa-redo"></i> Renew Subscription
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Payment History -->
    <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-size: 1.1rem; font-weight: 600;">Payment History</h2>
    </div>
    <div class="payment-table">
        <table>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td style="font-family: monospace; font-size: 0.8rem;">{{ Str::limit($payment->reference, 20) }}</td>
                        <td class="amount-badge">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                        <td><span class="status-pill {{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                        <td style="color: var(--text-muted);">{{ $payment->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">No payment records
                            found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($school->plan_tier !== 'enterprise')
        <div class="upgrade-card" style="margin-top: 32px;">
            <h3><i class="fas fa-rocket"></i> Need More?</h3>
            <p>Upgrade to
                {{ $school->plan_tier === 'basic' ? 'School Pro for custom subdomains and the Content Studio' : 'Enterprise for unlimited seats and API integrations' }}.
            </p>
            <a href="{{ route('contact') }}" class="sa-btn sa-btn-primary">
                {{ $school->plan_tier === 'basic' ? 'Upgrade to Pro' : 'Contact Sales for Enterprise' }}
            </a>
        </div>
    @endif
@endsection