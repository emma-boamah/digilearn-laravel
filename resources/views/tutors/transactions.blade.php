@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
    <div class="transactions-header-bar">
        <div class="transactions-header-left">
            <h2 class="transactions-header-title">
                <i class="fa-solid fa-receipt" style="color: var(--secondary-blue);"></i>
                <span>Settlements & Transactions</span>
            </h2>
            <p class="transactions-header-sub">
                Track completed lesson earnings, platform fee deductions, and withdrawal payout logs
            </p>
        </div>

        <div class="transactions-header-right">
            <a href="{{ route('tutors.earnings.index') }}" class="header-action-btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Insights</span>
            </a>

            <button type="button" class="header-action-btn btn-primary" onclick="openWithdrawModal()">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Request Payout</span>
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="transactions-page-container">
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

        <!-- Summary & Action Row -->
        <div class="summary-action-grid">
            <!-- Balance & Fast Withdraw Card -->
            <div class="card-surface balance-summary-card">
                <div class="card-header-flex">
                    <div>
                        <span class="card-eyebrow">Available for Payout</span>
                        <h3 class="balance-hero-amount">GHS {{ number_format($availableBalance, 2) }}</h3>
                    </div>
                    <div class="balance-icon-circle">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>

                <div class="destination-account-box">
                    <div class="account-row-label">
                        <i class="fa-solid fa-building-columns"></i>
                        <span>Payout Destination:</span>
                    </div>
                    @if($tutorProfile->payout_method && $tutorProfile->payout_account_number)
                        <div class="account-details-val">
                            <span class="payout-type-tag">{{ strtoupper($tutorProfile->payout_method) }}</span>
                            <span class="payout-bank-name">{{ $tutorProfile->payout_bank_name ?? 'Bank Account' }}</span>
                            <span class="payout-acc-num">({{ substr($tutorProfile->payout_account_number, 0, 3) }}•••{{ substr($tutorProfile->payout_account_number, -4) }})</span>
                        </div>
                    @else
                        <div class="account-details-missing">
                            <span>No payout account configured.</span>
                            <a href="{{ route('tutors.profile') }}" class="setup-payout-link">Set up in Profile</a>
                        </div>
                    @endif
                </div>

                <div class="balance-actions-footer">
                    @if($availableBalance >= $minPayoutAmount && $tutorProfile->payout_method)
                        <button type="button" class="btn-full-withdraw" onclick="openWithdrawModal()">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Withdraw Funds</span>
                        </button>
                    @else
                        <button type="button" class="btn-full-withdraw btn-disabled" disabled>
                            <i class="fa-solid fa-lock"></i>
                            <span>Min. Payout GHS {{ number_format($minPayoutAmount, 2) }}</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Settlement & Fee Breakdown Card -->
            <div class="card-surface fee-policy-card">
                <div class="policy-header">
                    <div class="policy-icon-circle">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h4 class="policy-title">Settlement Policy & Protection</h4>
                        <p class="policy-subtitle">Transparent earnings release directly to your MoMo or Bank</p>
                    </div>
                </div>

                <div class="policy-points-list">
                    <div class="policy-point-item">
                        <div class="point-bullet bullet-blue">
                            <i class="fa-solid fa-percent"></i>
                        </div>
                        <div class="point-text">
                            <strong>10% Platform Fee:</strong>
                            <span>Deducted automatically upon completed lesson verification to cover escrow insurance and platform infrastructure.</span>
                        </div>
                    </div>

                    <div class="policy-point-item">
                        <div class="point-bullet bullet-green">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div class="point-text">
                            <strong>1-2 Business Days:</strong>
                            <span>Withdrawals processed via Paystack are typically settled into your mobile wallet or bank account within 24–48 hours.</span>
                        </div>
                    </div>

                    <div class="policy-point-item">
                        <div class="point-bullet bullet-red">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <div class="point-text">
                            <strong>Minimum Threshold:</strong>
                            <span>Minimum eligible balance to initiate a withdrawal request is <strong>GHS {{ number_format($minPayoutAmount, 2) }}</strong>.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 1: Withdrawal / Payout Requests -->
        <div class="ledger-section-card">
            <div class="ledger-header">
                <div>
                    <h3 class="ledger-title">
                        <i class="fa-solid fa-money-bill-transfer" style="color: var(--secondary-blue);"></i>
                        <span>Withdrawal & Payout History</span>
                    </h3>
                    <p class="ledger-subtitle">History of all funds requested and transferred to your account</p>
                </div>
            </div>

            <div class="table-responsive-wrapper">
                <table class="modern-ledger-table">
                    <thead>
                        <tr>
                            <th>Reference ID</th>
                            <th>Date & Time</th>
                            <th>Payout Destination</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($payouts) && count($payouts) > 0)
                            @foreach($payouts as $payout)
                                <tr>
                                    <td>
                                        <code class="ref-code">{{ $payout->reference ?? 'PO-'.str_pad($payout->id, 6, '0', STR_PAD_LEFT) }}</code>
                                    </td>
                                    <td>
                                        <div class="table-date-cell">
                                            <span class="date-main">{{ $payout->created_at->format('M d, Y') }}</span>
                                            <span class="date-sub">{{ $payout->created_at->format('h:i A') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dest-cell">
                                            <span class="payout-type-mini">{{ strtoupper($payout->payout_method ?? 'MOMO') }}</span>
                                            <span class="dest-name">{{ $payout->bank_name ?? 'Mobile Money' }}</span>
                                            <span class="dest-acc">({{ $payout->account_number ?? '••••' }})</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="payout-amount-val">GHS {{ number_format($payout->amount, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($payout->status === 'completed')
                                            <span class="badge-status badge-success">
                                                <i class="fa-solid fa-circle-check"></i> Completed
                                            </span>
                                        @elseif($payout->status === 'processing')
                                            <span class="badge-status badge-processing">
                                                <i class="fa-solid fa-arrows-rotate fa-spin"></i> Processing
                                            </span>
                                        @elseif($payout->status === 'failed')
                                            <span class="badge-status badge-failed">
                                                <i class="fa-solid fa-circle-xmark"></i> Failed
                                            </span>
                                        @else
                                            <span class="badge-status badge-pending">
                                                <i class="fa-regular fa-clock"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="empty-table-cell">
                                    <div class="empty-state-inner">
                                        <div class="empty-icon-circle">
                                            <i class="fa-solid fa-money-bill-transfer"></i>
                                        </div>
                                        <p class="empty-text-main">No withdrawal requests found</p>
                                        <p class="empty-text-sub">When you request a payout from your available balance, it will appear here.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if(method_exists($payouts, 'hasPages') && $payouts->hasPages())
                <div class="pagination-footer">
                    {{ $payouts->appends(request()->except('payouts_page'))->links() }}
                </div>
            @endif
        </div>

        <!-- Section 2: Completed Session Credits Ledger -->
        <div class="ledger-section-card">
            <div class="ledger-header">
                <div>
                    <h3 class="ledger-title">
                        <i class="fa-solid fa-graduation-cap" style="color: var(--secondary-blue);"></i>
                        <span>Completed Session Earnings</span>
                    </h3>
                    <p class="ledger-subtitle">Credits released into your balance from verified student tutoring sessions</p>
                </div>
            </div>

            <div class="table-responsive-wrapper">
                <table class="modern-ledger-table">
                    <thead>
                        <tr>
                            <th>Completed Date</th>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Gross Booking</th>
                            <th>Platform Fee (10%)</th>
                            <th>Net Credited</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($completedBookings) && count($completedBookings) > 0)
                            @foreach($completedBookings as $booking)
                                <tr>
                                    <td>
                                        <div class="table-date-cell">
                                            <span class="date-main">{{ $booking->updated_at->format('M d, Y') }}</span>
                                            <span class="date-sub">{{ $booking->updated_at->format('h:i A') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="student-profile-cell">
                                            <div class="student-avatar-sub">
                                                {{ strtoupper(substr($booking->student->name ?? 'S', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="student-name-text">{{ $booking->student->name ?? 'Student' }}</div>
                                                <div class="student-id-text">#{{ $booking->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="subject-tag">{{ $booking->subject->name ?? 'General Lesson' }}</span>
                                    </td>
                                    <td>
                                        <span class="gross-fee-val">GHS {{ number_format($booking->credits_paid, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="commission-cut-val">-GHS {{ number_format($booking->commission_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="net-credit-val">+GHS {{ number_format($booking->credits_paid - $booking->commission_amount, 2) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="empty-table-cell">
                                    <div class="empty-state-inner">
                                        <div class="empty-icon-circle">
                                            <i class="fa-solid fa-graduation-cap"></i>
                                        </div>
                                        <p class="empty-text-main">No completed lesson earnings yet</p>
                                        <p class="empty-text-sub">Completed student tutoring sessions will be automatically logged here with exact fee breakdowns.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if(method_exists($completedBookings, 'hasPages') && $completedBookings->hasPages())
                <div class="pagination-footer">
                    {{ $completedBookings->appends(request()->except('earnings_page'))->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Withdraw Modal -->
    <div id="withdrawModal" class="modal-backdrop-custom" onclick="handleModalBackdropClick(event)">
        <div class="modal-card-custom" onclick="event.stopPropagation()">
            <div class="modal-card-header">
                <div class="modal-title-wrap">
                    <div class="modal-title-icon">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div>
                        <h3 class="modal-title-text">Request Earnings Payout</h3>
                        <p class="modal-subtitle-text">Funds will be transferred to your registered payout account</p>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeWithdrawModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('tutors.earnings.payout') }}" method="POST" class="modal-form">
                @csrf
                <div class="modal-body-content">
                    <!-- Balance Callout -->
                    <div class="modal-balance-banner">
                        <div class="banner-balance-info">
                            <span class="banner-balance-lbl">Available to Withdraw</span>
                            <strong class="banner-balance-num">GHS {{ number_format($availableBalance, 2) }}</strong>
                        </div>
                        <span class="banner-min-badge">Min. GHS {{ number_format($minPayoutAmount, 2) }}</span>
                    </div>

                    <!-- Payout Destination Info -->
                    @if($tutorProfile->payout_method && $tutorProfile->payout_account_number)
                        <div class="modal-account-pill">
                            <i class="fa-solid fa-circle-check" style="color: #10b981;"></i>
                            <div class="modal-account-txt">
                                <strong>Destination:</strong>
                                <span>{{ strtoupper($tutorProfile->payout_method) }} — {{ $tutorProfile->payout_bank_name }} ({{ $tutorProfile->payout_account_number }})</span>
                            </div>
                        </div>
                    @else
                        <div class="modal-account-pill warning">
                            <i class="fa-solid fa-triangle-exclamation" style="color: var(--primary-red);"></i>
                            <div class="modal-account-txt">
                                <strong>No destination account set up:</strong>
                                <a href="{{ route('tutors.profile') }}" style="color: var(--secondary-blue); text-decoration: underline;">Configure payout details</a>
                            </div>
                        </div>
                    @endif

                    <!-- Amount Input -->
                    <div class="form-group-custom">
                        <label for="withdraw_amount" class="form-label-custom">
                            <span>Withdrawal Amount (GHS)</span>
                            <span class="req-star">*</span>
                        </label>
                        <div class="input-currency-wrapper">
                            <span class="input-prefix">GHS</span>
                            <input
                                type="number"
                                step="0.01"
                                min="{{ $minPayoutAmount }}"
                                max="{{ $availableBalance }}"
                                name="amount"
                                id="withdraw_amount"
                                class="form-control-custom"
                                placeholder="{{ number_format($minPayoutAmount, 2) }}"
                                value="{{ old('amount', $availableBalance >= $minPayoutAmount ? $availableBalance : '') }}"
                                required
                            >
                        </div>
                        <div class="input-hints-row">
                            <span class="hint-txt">Minimum: GHS {{ number_format($minPayoutAmount, 2) }}</span>
                            @if($availableBalance >= $minPayoutAmount)
                                <button type="button" class="btn-max-amount" onclick="document.getElementById('withdraw_amount').value = '{{ $availableBalance }}'">
                                    Use Max (GHS {{ number_format($availableBalance, 2) }})
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Security Note -->
                    <div class="modal-security-note">
                        <i class="fa-solid fa-lock" style="color: #64748b;"></i>
                        <span>Withdrawals are processed securely via Paystack transfers. Delivery time is typically 1-2 business days.</span>
                    </div>
                </div>

                <div class="modal-card-footer">
                    <button type="button" class="modal-btn-cancel" onclick="closeWithdrawModal()">Cancel</button>
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
        .transactions-page-container {
            padding: 1.5rem 2rem 3rem;
            max-width: 1400px;
            margin: 0 auto;
            color: #0f172a;
        }

        /* Top Header Bar */
        .transactions-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            width: 100%;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .transactions-header-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .transactions-header-sub {
            margin: 0.15rem 0 0 0;
            font-size: 0.8rem;
            color: #64748b;
        }

        .transactions-header-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
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
            margin-bottom: 1.5rem;
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
            color: var(--primary-red);
            border: 1px solid #fecaca;
        }

        /* Summary & Action Row */
        .summary-action-grid {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }

        .card-surface {
            background: #fafbfc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            position: relative;
        }

        .card-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .card-eyebrow {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
        }

        .balance-hero-amount {
            font-size: 1.85rem;
            font-weight: 800;
            color: #10b981;
            margin: 0.25rem 0 0 0;
            letter-spacing: -0.02em;
        }

        .balance-icon-circle {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .destination-account-box {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.85rem 1rem;
            margin-bottom: 1.25rem;
        }

        .account-row-label {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 0.35rem;
        }

        .account-details-val {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            font-size: 0.88rem;
        }

        .payout-type-tag {
            background: var(--secondary-blue);
            color: #ffffff;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            letter-spacing: 0.04em;
        }

        .payout-bank-name {
            font-weight: 700;
            color: #0f172a;
        }

        .payout-acc-num {
            color: #64748b;
            font-family: monospace;
            font-size: 0.85rem;
        }

        .account-details-missing {
            font-size: 0.85rem;
            color: var(--primary-red);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .setup-payout-link {
            font-weight: 700;
            color: var(--secondary-blue);
            text-decoration: underline;
        }

        .btn-full-withdraw {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: var(--secondary-blue);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.25rem;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(38, 119, 184, 0.25);
        }

        .btn-full-withdraw:hover:not(:disabled) {
            background: var(--secondary-blue-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(38, 119, 184, 0.35);
        }

        .btn-full-withdraw.btn-disabled {
            background: #cbd5e1;
            color: #64748b;
            box-shadow: none;
            cursor: not-allowed;
        }

        /* Fee & Settlement Policy Card */
        .fee-policy-card {
            background: #fafbfc;
            border: 1px solid #e2e8f0;
        }

        .policy-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.2rem;
        }

        .policy-icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(38, 119, 184, 0.1);
            color: var(--secondary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }

        .policy-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .policy-subtitle {
            margin: 0.15rem 0 0 0;
            font-size: 0.78rem;
            color: #64748b;
        }

        .policy-points-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .policy-point-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.82rem;
            line-height: 1.45;
            color: #334155;
        }

        .point-bullet {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.75rem;
            margin-top: 0.1rem;
        }

        .bullet-blue {
            background: rgba(38, 119, 184, 0.1);
            color: var(--secondary-blue);
        }

        .bullet-green {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .bullet-red {
            background: rgba(225, 30, 45, 0.1);
            color: var(--primary-red);
        }

        .point-text strong {
            color: #0f172a;
            font-weight: 700;
        }

        /* Ledger Section Cards */
        .ledger-section-card {
            background: #fafbfc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            overflow: hidden;
            margin-bottom: 1.75rem;
        }

        .ledger-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .ledger-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .ledger-subtitle {
            margin: 0.2rem 0 0 0;
            font-size: 0.8rem;
            color: #64748b;
        }

        .table-responsive-wrapper {
            overflow-x: auto;
        }

        .modern-ledger-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.88rem;
        }

        .modern-ledger-table thead tr {
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
        }

        .modern-ledger-table th {
            padding: 0.85rem 1.25rem;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #475569;
        }

        .modern-ledger-table tbody tr {
            border-bottom: 1px solid #edf2f7;
            transition: background-color 0.15s ease;
        }

        .modern-ledger-table tbody tr:hover {
            background: #f8fafc;
        }

        .modern-ledger-table td {
            padding: 0.95rem 1.25rem;
            color: #1e293b;
            vertical-align: middle;
        }

        .ref-code {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.2rem 0.45rem;
            font-size: 0.8rem;
            font-weight: 700;
            color: #0f172a;
        }

        .table-date-cell {
            display: flex;
            flex-direction: column;
        }

        .date-main {
            font-weight: 600;
            color: #0f172a;
            font-size: 0.85rem;
        }

        .date-sub {
            font-size: 0.75rem;
            color: #64748b;
        }

        .dest-cell {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: wrap;
            font-size: 0.84rem;
        }

        .payout-type-mini {
            background: rgba(38, 119, 184, 0.1);
            color: var(--secondary-blue);
            font-size: 0.68rem;
            font-weight: 800;
            padding: 0.12rem 0.4rem;
            border-radius: 4px;
        }

        .dest-name {
            font-weight: 600;
            color: #1e293b;
        }

        .dest-acc {
            color: #64748b;
            font-family: monospace;
            font-size: 0.8rem;
        }

        .payout-amount-val {
            font-weight: 800;
            color: #0f172a;
            font-size: 0.92rem;
        }

        /* Status Badges */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-processing {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-failed {
            background: #fee2e2;
            color: var(--primary-red);
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        /* Completed Earnings Table Details */
        .student-profile-cell {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .student-avatar-sub {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.78rem;
            flex-shrink: 0;
        }

        .student-name-text {
            font-weight: 700;
            color: #0f172a;
            font-size: 0.86rem;
        }

        .student-id-text {
            font-size: 0.72rem;
            color: #94a3b8;
        }

        .subject-tag {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .gross-fee-val {
            color: #475569;
            font-weight: 600;
            font-size: 0.86rem;
        }

        .commission-cut-val {
            color: var(--primary-red);
            font-weight: 700;
            font-size: 0.86rem;
        }

        .net-credit-val {
            color: #10b981;
            font-weight: 800;
            font-size: 0.92rem;
        }

        /* Empty States */
        .empty-table-cell {
            padding: 3rem 1.5rem;
            text-align: center;
        }

        .empty-state-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 360px;
            margin: 0 auto;
        }

        .empty-icon-circle {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 0.75rem;
        }

        .empty-text-main {
            font-weight: 700;
            color: #0f172a;
            font-size: 0.95rem;
            margin: 0 0 0.25rem 0;
        }

        .empty-text-sub {
            font-size: 0.82rem;
            color: #64748b;
            margin: 0;
            line-height: 1.4;
        }

        .pagination-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        /* Modal Backdrop & Card */
        .modal-backdrop-custom {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }

        .modal-backdrop-custom.active {
            display: flex;
        }

        .modal-card-custom {
            background: #fafbfc;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalPop {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(8px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
        }

        .modal-title-wrap {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-title-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(38, 119, 184, 0.1);
            color: var(--secondary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
        }

        .modal-title-text {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .modal-subtitle-text {
            font-size: 0.78rem;
            color: #64748b;
            margin: 0.15rem 0 0 0;
        }

        .modal-close-btn {
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 1.15rem;
            cursor: pointer;
            padding: 0.35rem;
            border-radius: 6px;
            transition: all 0.15s ease;
        }

        .modal-close-btn:hover {
            color: #0f172a;
            background: #e2e8f0;
        }

        .modal-form {
            margin: 0;
        }

        .modal-body-content {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .modal-balance-banner {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.85rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .banner-balance-info {
            display: flex;
            flex-direction: column;
        }

        .banner-balance-lbl {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
        }

        .banner-balance-num {
            font-size: 1.25rem;
            font-weight: 800;
            color: #10b981;
        }

        .banner-min-badge {
            font-size: 0.75rem;
            background: #e2e8f0;
            color: #475569;
            padding: 0.25rem 0.55rem;
            border-radius: 6px;
            font-weight: 600;
        }

        .modal-account-pill {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.82rem;
            color: #334155;
        }

        .modal-account-pill.warning {
            background: #fef2f2;
            border-color: #fecaca;
        }

        .form-group-custom {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .form-label-custom {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .req-star {
            color: var(--primary-red);
        }

        .input-currency-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-prefix {
            position: absolute;
            left: 0.9rem;
            font-size: 0.88rem;
            font-weight: 700;
            color: #64748b;
            pointer-events: none;
        }

        .form-control-custom {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.65rem 0.9rem 0.65rem 3.2rem;
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            transition: all 0.15s ease;
        }

        .form-control-custom:focus {
            outline: none;
            background: #ffffff;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.15);
        }

        .input-hints-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.25rem;
        }

        .hint-txt {
            font-size: 0.75rem;
            color: #64748b;
        }

        .btn-max-amount {
            background: transparent;
            border: none;
            color: var(--secondary-blue);
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
        }

        .modal-security-note {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.78rem;
            color: #64748b;
            line-height: 1.4;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 0.65rem 0.85rem;
            border-radius: 8px;
        }

        .modal-card-footer {
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .modal-btn-cancel {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 0.55rem 1.15rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .modal-btn-cancel:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .modal-btn-submit {
            background: var(--secondary-blue);
            border: none;
            color: #ffffff;
            padding: 0.55rem 1.35rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            box-shadow: 0 2px 6px rgba(38, 119, 184, 0.25);
            transition: all 0.15s ease;
        }

        .modal-btn-submit:hover:not(:disabled) {
            background: var(--secondary-blue-hover);
            transform: translateY(-1px);
        }

        .modal-btn-submit:disabled {
            background: #cbd5e1;
            color: #64748b;
            box-shadow: none;
            cursor: not-allowed;
        }

        /* Responsive Breakpoints */
        @media (max-width: 900px) {
            .summary-action-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .transactions-page-container {
                padding: 1rem 1rem 2.5rem;
            }

            .transactions-header-bar {
                flex-direction: column;
                align-items: flex-start;
            }

            .transactions-header-right {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        function openWithdrawModal() {
            const modal = document.getElementById('withdrawModal');
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeWithdrawModal() {
            const modal = document.getElementById('withdrawModal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        function handleModalBackdropClick(e) {
            if (e.target.id === 'withdrawModal') {
                closeWithdrawModal();
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeWithdrawModal();
            }
        });
    </script>
@endsection
