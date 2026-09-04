@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
    <div class="earnings-header-bar">
        <div class="earnings-header-left">
            <h2 class="earnings-header-title">
                <i class="fa-solid fa-chart-line" style="color: var(--secondary-blue);"></i>
                <span>Insights & Performance</span>
            </h2>
            <p class="earnings-header-sub">
                Real-time financial analytics, escrow security, and revenue trends
            </p>
        </div>

        <div class="earnings-header-right">
            <!-- Period Filter Dropdown / Pill -->
            <form action="{{ route('tutors.earnings.index') }}" method="GET" id="periodForm" class="period-filter-form">
                <div class="period-pill-wrapper">
                    <i class="fa-regular fa-calendar-days" style="color: #64748b; font-size: 0.85rem;"></i>
                    <span class="period-pill-label">Period:</span>
                    <select name="period" onchange="document.getElementById('periodForm').submit()" class="period-select">
                        <option value="7_days" @selected($period === '7_days')>Last 7 days</option>
                        <option value="30_days" @selected($period === '30_days')>Last 30 days</option>
                        <option value="90_days" @selected($period === '90_days')>Last 90 days</option>
                        <option value="this_year" @selected($period === 'this_year')>This Year</option>
                        <option value="all_time" @selected($period === 'all_time')>All Time</option>
                    </select>
                </div>
            </form>

            <div class="currency-badge">
                <span class="currency-code">GHS</span>
            </div>

            <!-- Action: Withdraw Earnings Modal Button -->
            <button type="button" class="header-action-btn btn-primary" onclick="openWithdrawModal()">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Withdraw</span>
            </button>

            <!-- Action: View Settlements & Transactions Ledger -->
            <a href="{{ route('tutors.earnings.transactions') }}" class="header-action-btn btn-secondary">
                <i class="fa-solid fa-receipt"></i>
                <span>Settlements & Ledger</span>
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="earnings-dashboard-container">
        @if(session('success'))
            <div class="alert-banner alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert-banner alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- 4 Top Metric KPI Cards -->
        <div class="kpi-grid">
            <!-- Card 1: Revenue -->
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-label">Revenue</span>
                    <div class="kpi-icon-wrap" style="background: rgba(38, 119, 184, 0.1); color: var(--secondary-blue);">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>
                <h3 class="kpi-value">GHS {{ number_format($periodRevenue, 2) }}</h3>
                <div class="kpi-subtext">
                    <span class="kpi-badge-info">{{ $completedCount }} completed {{ Str::plural('lesson', $completedCount) }}</span>
                </div>
            </div>

            <!-- Card 2: Available Balance -->
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-label">Available for Payout</span>
                    <div class="kpi-icon-wrap" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </div>
                </div>
                <h3 class="kpi-value" style="color: #10b981;">GHS {{ number_format($availableBalance, 2) }}</h3>
                <div class="kpi-subtext">
                    <button type="button" class="quick-withdraw-link" onclick="openWithdrawModal()">
                        Request withdrawal <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Card 3: Pending Escrow -->
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-label">Pending Settlements</span>
                    <div class="kpi-icon-wrap" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                </div>
                <h3 class="kpi-value" style="color: #f59e0b;">GHS {{ number_format($pendingEscrow, 2) }}</h3>
                <div class="kpi-subtext">
                    <span style="color: #64748b; font-size: 0.76rem;">Released upon lesson completion</span>
                </div>
            </div>

            <!-- Card 4: Avg Transaction Value -->
            <div class="kpi-card">
                <div class="kpi-header">
                    <span class="kpi-label">Avg. Transaction Value</span>
                    <div class="kpi-icon-wrap" style="background: rgba(225, 30, 45, 0.08); color: var(--primary-red);">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                </div>
                <h3 class="kpi-value">GHS {{ number_format($avgTransactionValue, 2) }}</h3>
                <div class="kpi-subtext">
                    <span style="color: #64748b; font-size: 0.76rem;">Average net rate per session</span>
                </div>
            </div>
        </div>

        <!-- Full-Width Expansive Revenue Breakdown Trend Chart -->
        <div class="insight-card chart-full-card">
            <div class="insight-card-header">
                <div>
                    <h3 class="insight-card-title">Revenue breakdown</h3>
                    <span class="insight-card-subtitle">Net earnings trend over the selected period</span>
                </div>
                <div class="chart-summary-tag">
                    <span>Total in Period:</span>
                    <strong>GHS {{ number_format($periodRevenue, 2) }}</strong>
                </div>
            </div>
            
            <div class="chart-container-wrap">
                <canvas id="revenueBreakdownChart"></canvas>
            </div>
        </div>

        <!-- Bottom 3 Performance & Risk Widgets -->
        <div class="performance-widgets-grid">
            <!-- Widget 1: Success Rate -->
            <div class="perf-widget-card">
                <div class="perf-widget-header">
                    <span class="perf-widget-title">Success rate</span>
                </div>
                
                <div class="perf-ring-container">
                    <div class="perf-circle-gauge" style="--gauge-pct: {{ $successRate }}; --gauge-color: #10b981;">
                        <span class="gauge-center-text">{{ $successRate }}%</span>
                    </div>
                </div>

                <div class="perf-legend-list">
                    <div class="perf-legend-item">
                        <span class="legend-name"><span class="legend-dot" style="background: #10b981;"></span> Successful</span>
                        <strong class="legend-val">{{ $completedCount }}</strong>
                    </div>
                    <div class="perf-legend-item">
                        <span class="legend-name"><span class="legend-dot" style="background: var(--primary-red);"></span> Processing errors</span>
                        <strong class="legend-val">0</strong>
                    </div>
                </div>
            </div>

            <!-- Widget 2: Payment Issues & Escrow Health -->
            <div class="perf-widget-card">
                <div class="perf-widget-header">
                    <span class="perf-widget-title">Payment issues</span>
                </div>
                
                <div class="health-widget-content">
                    @if($openDisputesCount > 0)
                        <div class="health-alert-box health-alert-warning">
                            <i class="fa-solid fa-triangle-exclamation" style="color: #f59e0b;"></i>
                            <div>
                                <strong>{{ $openDisputesCount }} Open Dispute(s)</strong>
                                <p>Under review by support team.</p>
                            </div>
                        </div>
                    @else
                        <div class="health-status-wrap">
                            <div class="health-icon-badge">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <strong class="health-title">Escrow Protected</strong>
                            <p class="health-desc">
                                All student payments and escrow settlements are in good standing.
                            </p>
                        </div>
                    @endif
                </div>

                <div class="perf-legend-list" style="margin-top: auto;">
                    <div class="perf-legend-item">
                        <span class="legend-name"><span class="legend-dot" style="background: var(--secondary-blue);"></span> Open disputes</span>
                        <strong class="legend-val">{{ $openDisputesCount }}</strong>
                    </div>
                </div>
            </div>

            <!-- Widget 3: Abandonment / Cancellation Rate -->
            <div class="perf-widget-card">
                <div class="perf-widget-header">
                    <span class="perf-widget-title">Abandonment rate</span>
                </div>
                
                <div class="perf-ring-container">
                    <div class="perf-circle-gauge" style="--gauge-pct: {{ max(4, $cancellationRate) }}; --gauge-color: #f97316;">
                        <span class="gauge-center-text" style="color: #ea580c;">{{ $cancellationRate }}%</span>
                    </div>
                </div>

                <div class="perf-legend-list">
                    <div class="perf-legend-item">
                        <span class="legend-name"><span class="legend-dot" style="background: #f97316;"></span> Processed</span>
                        <strong class="legend-val">{{ $totalProcessed }}</strong>
                    </div>
                    <div class="perf-legend-item">
                        <span class="legend-name"><span class="legend-dot" style="background: var(--primary-red);"></span> Abandoned / Missed</span>
                        <strong class="legend-val">{{ $cancelledCount }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Clean Withdrawal Modal -->
    <div id="withdrawModal" class="modal-backdrop" style="display: none;" onclick="closeWithdrawModal(event)">
        <div class="modal-card" onclick="event.stopPropagation()">
            <div class="modal-header">
                <div class="modal-header-left">
                    <div class="modal-icon-badge">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                    </div>
                    <div>
                        <h3 class="modal-title">Withdraw Earnings</h3>
                        <p class="modal-subtitle">Transfer available balance directly to your account</p>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" onclick="hideWithdrawModal()" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('tutors.earnings.payout') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Balance Callout -->
                    <div class="modal-balance-callout">
                        <span class="balance-label">Available Balance:</span>
                        <strong class="balance-amount">GHS {{ number_format($availableBalance, 2) }}</strong>
                    </div>

                    <!-- Amount Input -->
                    <div style="margin-bottom: 1.15rem;">
                        <label class="form-field-label">Withdrawal Amount (GHS)</label>
                        <div class="input-currency-wrapper">
                            <span class="input-currency-prefix">GHS</span>
                            <input type="number" step="0.01" min="{{ $minPayoutAmount }}" max="{{ $availableBalance }}" name="amount" value="{{ old('amount', min($availableBalance, $minPayoutAmount)) }}" required class="form-currency-input" placeholder="0.00">
                        </div>
                        <span class="field-helper-text">Minimum withdrawal: <strong>GHS {{ number_format($minPayoutAmount, 2) }}</strong></span>
                    </div>

                    <!-- Receiving Account Preview -->
                    <div class="receiving-account-box">
                        <div class="receiving-acc-header">
                            <span class="acc-badge"><i class="fa-solid fa-building-columns"></i> Receiving Account</span>
                            <a href="{{ route('tutors.profile.settings') }}" class="acc-change-link">Change</a>
                        </div>
                        <div class="acc-details-text">
                            @if($tutorProfile->payout_method === 'momo')
                                <strong>Mobile Money ({{ $tutorProfile->payout_momo_network }})</strong>
                                <span>{{ $tutorProfile->payout_momo_number }}</span>
                            @elseif($tutorProfile->payout_method === 'bank')
                                <strong>{{ $tutorProfile->payout_bank_name }}</strong>
                                <span>{{ $tutorProfile->payout_bank_account_number }}</span>
                            @else
                                <span style="color: var(--primary-red); font-weight: 600;">No payout method configured yet</span>
                            @endif
                        </div>
                    </div>

                    <p style="font-size: 0.75rem; color: #64748b; line-height: 1.4; margin: 0;">
                        <i class="fa-solid fa-shield-halved" style="color: var(--secondary-blue);"></i> Payouts are dispatched securely via Paystack Transfers API to your verified account based on standard settlement cycles.
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="modal-btn-cancel" onclick="hideWithdrawModal()">Cancel</button>
                    <button type="submit" @if($availableBalance < $minPayoutAmount || !$tutorProfile->payout_method) disabled @endif class="modal-btn-submit">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Confirm Withdrawal</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scoped Modern Light Mode Styles (Anti-Glare & Neurodivergent-Friendly) -->
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        .earnings-dashboard-container {
            padding: 1.5rem 2rem 3rem;
            max-width: 1400px;
            margin: 0 auto;
            color: #0f172a;
        }

        /* Top Header Bar */
        .earnings-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            width: 100%;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .earnings-header-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .earnings-header-sub {
            margin: 0.15rem 0 0 0;
            font-size: 0.8rem;
            color: #64748b;
        }

        .earnings-header-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .period-pill-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.45rem 0.75rem;
            font-size: 0.82rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        .period-pill-label {
            font-weight: 600;
            color: #64748b;
        }

        .period-select {
            background: transparent;
            border: none;
            color: #0f172a;
            font-weight: 700;
            font-size: 0.82rem;
            cursor: pointer;
            outline: none;
            padding-right: 0.25rem;
        }

        .currency-badge {
            display: inline-flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.45rem 0.75rem;
            font-size: 0.82rem;
            font-weight: 800;
            color: var(--secondary-blue);
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        .header-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.48rem 1rem;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary {
            background: var(--secondary-blue);
            color: #ffffff;
            border: 1px solid transparent;
            box-shadow: 0 2px 4px rgba(38, 119, 184, 0.2);
        }

        .btn-primary:hover {
            background: var(--secondary-blue-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(38, 119, 184, 0.3);
        }

        .btn-secondary {
            background: #f8fafc;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .btn-secondary:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        /* Alerts */
        .alert-banner {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.85rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* 4 Top KPI Metric Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.15rem;
            margin-bottom: 1.5rem;
        }

        .kpi-card {
            background: #fafbfc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.25rem 1.35rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .kpi-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        }

        .kpi-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.4rem;
        }

        .kpi-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
            letter-spacing: 0.02em;
        }

        .kpi-icon-wrap {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
        }

        .kpi-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0.2rem 0 0.45rem 0;
            letter-spacing: -0.02em;
        }

        .kpi-subtext {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .kpi-badge-info {
            background: rgba(38, 119, 184, 0.08);
            color: var(--secondary-blue);
            padding: 0.15rem 0.55rem;
            border-radius: 6px;
            font-size: 0.74rem;
            font-weight: 700;
        }

        .quick-withdraw-link {
            background: none;
            border: none;
            color: #10b981;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            padding: 0;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: color 0.15s ease;
        }

        .quick-withdraw-link:hover {
            color: #059669;
            text-decoration: underline;
        }

        /* Generic Insight Cards */
        .insight-card {
            background: #fafbfc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.4rem 1.6rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            margin-bottom: 1.5rem;
        }

        .insight-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .insight-card-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        .insight-card-subtitle {
            display: block;
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.15rem;
        }

        .chart-summary-tag {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.4rem 0.85rem;
            font-size: 0.82rem;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        .chart-summary-tag strong {
            color: var(--secondary-blue);
            font-size: 0.9rem;
        }

        .chart-container-wrap {
            position: relative;
            width: 100%;
            height: 330px;
        }

        /* Performance 3 Widgets Grid */
        .performance-widgets-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.15rem;
        }

        .perf-widget-card {
            background: #fafbfc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.25rem 1.35rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 220px;
        }

        .perf-widget-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0f172a;
        }

        /* Circular SVG Gauge */
        .perf-ring-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 0;
        }

        .perf-circle-gauge {
            width: 95px;
            height: 95px;
            border-radius: 50%;
            background: radial-gradient(closest-side, #fafbfc 74%, transparent 75% 100%),
                        conic-gradient(var(--gauge-color) calc(var(--gauge-pct) * 1%), #e2e8f0 0);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }

        .gauge-center-text {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
        }

        .perf-legend-list {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            font-size: 0.78rem;
            border-top: 1px solid #f1f5f9;
            padding-top: 0.75rem;
        }

        .perf-legend-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #64748b;
        }

        .perf-legend-item .legend-name {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .perf-legend-item .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .perf-legend-item .legend-val {
            font-weight: 700;
            color: #0f172a;
        }

        /* Health Status Widget */
        .health-widget-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0.6rem 0;
        }

        .health-status-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .health-icon-badge {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(38, 119, 184, 0.1);
            color: var(--secondary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-bottom: 0.5rem;
        }

        .health-title {
            font-size: 0.95rem;
            color: #0f172a;
            font-weight: 700;
        }

        .health-desc {
            font-size: 0.75rem;
            color: #64748b;
            margin: 0.25rem 0 0 0;
            line-height: 1.4;
        }

        /* Modern Withdrawal Modal */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            animation: fadeIn 0.2s ease-out;
        }

        .modal-card {
            background: #fafbfc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
        }

        .modal-header-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-icon-badge {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(38, 119, 184, 0.1);
            color: var(--secondary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .modal-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
        }

        .modal-subtitle {
            margin: 0.1rem 0 0 0;
            font-size: 0.76rem;
            color: #64748b;
        }

        .modal-close-btn {
            background: none;
            border: none;
            color: #64748b;
            font-size: 1.15rem;
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }

        .modal-close-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-balance-callout {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }

        .balance-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #065f46;
        }

        .balance-amount {
            font-size: 1.15rem;
            font-weight: 800;
            color: #047857;
        }

        .form-field-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.35rem;
        }

        .input-currency-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-currency-prefix {
            position: absolute;
            left: 0.85rem;
            font-weight: 700;
            color: #64748b;
            font-size: 0.85rem;
            pointer-events: none;
        }

        .form-currency-input {
            width: 100%;
            padding: 0.65rem 0.85rem 0.65rem 3.4rem;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
            font-weight: 700;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.15s ease;
        }

        .form-currency-input:focus {
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.15);
        }

        .field-helper-text {
            font-size: 0.74rem;
            color: #64748b;
            display: block;
            margin-top: 0.25rem;
        }

        .receiving-account-box {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.15rem;
        }

        .receiving-acc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.3rem;
        }

        .acc-badge {
            font-size: 0.72rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .acc-change-link {
            font-size: 0.74rem;
            font-weight: 700;
            color: var(--secondary-blue);
            text-decoration: none;
        }

        .acc-change-link:hover {
            text-decoration: underline;
        }

        .acc-details-text {
            font-size: 0.82rem;
            color: #0f172a;
            display: flex;
            flex-direction: column;
        }

        .modal-footer {
            padding: 1.1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .modal-btn-cancel {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #475569;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.55rem 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .modal-btn-cancel:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .modal-btn-submit {
            background: var(--secondary-blue);
            border: 1px solid transparent;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 0.55rem 1.25rem;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            box-shadow: 0 2px 4px rgba(38, 119, 184, 0.2);
            transition: all 0.15s ease;
        }

        .modal-btn-submit:hover:not(:disabled) {
            background: var(--secondary-blue-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(38, 119, 184, 0.3);
        }

        .modal-btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(12px) scale(0.98); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }

        /* Responsive Breakpoints */
        @media (max-width: 1100px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .performance-widgets-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .earnings-dashboard-container {
                padding: 1rem 0.75rem 2rem;
            }
            .kpi-grid {
                grid-template-columns: 1fr;
            }
            .chart-container-wrap {
                height: 250px;
            }
        }
    </style>

    <!-- Chart.js for Revenue Breakdown Area Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('revenueBreakdownChart').getContext('2d');
            
            // Gradient fill for area chart
            const gradient = ctx.createLinearGradient(0, 0, 0, 330);
            gradient.addColorStop(0, 'rgba(38, 119, 184, 0.28)');
            gradient.addColorStop(1, 'rgba(38, 119, 184, 0.0)');

            const labels = {!! json_encode($chartLabels) !!};
            const data = {!! json_encode($chartData) !!};

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Net Revenue (GHS)',
                        data: data,
                        borderColor: '#2677B8',
                        backgroundColor: gradient,
                        borderWidth: 2.8,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#2677B8',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4.5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#94a3b8',
                            bodyColor: '#ffffff',
                            padding: 12,
                            borderRadius: 8,
                            titleFont: { size: 12, weight: '600' },
                            bodyFont: { size: 14, weight: '700' },
                            callbacks: {
                                label: function(context) {
                                    return 'Net Volume: GHS ' + context.parsed.y.toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11, weight: '600' },
                                callback: function(value) {
                                    return 'GHS ' + value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11, weight: '600' }
                            }
                        }
                    }
                }
            });
        });

        function openWithdrawModal() {
            const modal = document.getElementById('withdrawModal');
            if (modal) {
                modal.style.display = 'flex';
            }
        }

        function hideWithdrawModal() {
            const modal = document.getElementById('withdrawModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        function closeWithdrawModal(e) {
            if (e.target && e.target.id === 'withdrawModal') {
                hideWithdrawModal();
            }
        }
    </script>
@endsection

