@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
    <div style="flex: 1; display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main);">Wallet & Earnings Tracker</h2>
    </div>
@endsection

@section('content')
    <div style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
        @if(session('success'))
            <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; border: 1px solid #a7f3d0;">
                <i class="fa-solid fa-circle-check" style="margin-right: 0.5rem;"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; border: 1px solid #fca5a5;">
                <i class="fa-solid fa-circle-exclamation" style="margin-right: 0.5rem;"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Earnings Hero Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.5rem; box-shadow: var(--shadow-sm);">
                <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Available Balance</span>
                <h3 style="font-size: 2rem; font-weight: 800; color: var(--secondary-blue); margin: 0.35rem 0 0.5rem 0;">GHS {{ number_format($availableBalance, 2) }}</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Ready for Paystack withdrawal</span>
            </div>

            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.5rem; box-shadow: var(--shadow-sm);">
                <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Pending Escrow</span>
                <h3 style="font-size: 2rem; font-weight: 800; color: #f59e0b; margin: 0.35rem 0 0.5rem 0;">GHS {{ number_format($pendingEscrow, 2) }}</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Released upon session completion</span>
            </div>

            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.5rem; box-shadow: var(--shadow-sm);">
                <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Lifetime Earnings</span>
                <h3 style="font-size: 2rem; font-weight: 800; color: #10b981; margin: 0.35rem 0 0.5rem 0;">GHS {{ number_format($lifetimeEarnings, 2) }}</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Total net revenue earned</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <!-- Chart Widget -->
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.5rem; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.25rem;">Monthly Revenue Trend</h3>
                <canvas id="revenueChart" height="220"></canvas>
            </div>

            <!-- Payout Request Card -->
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.5rem; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem;">Withdraw Earnings</h3>
                
                <form action="{{ route('tutors.earnings.payout') }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Withdrawal Amount (GHS)</label>
                        <input type="number" step="0.01" min="{{ $minPayoutAmount }}" max="{{ $availableBalance }}" name="amount" value="{{ old('amount', min($availableBalance, $minPayoutAmount)) }}" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-weight: 600;">
                        <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.25rem;">Min. withdrawal: GHS {{ number_format($minPayoutAmount, 2) }}</span>
                    </div>

                    <div style="background: var(--gray-50); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.85rem; margin-bottom: 1rem;">
                        <span style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">Receiving Account</span>
                        <span style="display: block; font-size: 0.8rem; color: var(--text-muted);">
                            @if($tutorProfile->payout_method === 'momo')
                                Mobile Money: {{ $tutorProfile->payout_momo_network }} ({{ $tutorProfile->payout_momo_number }})
                            @elseif($tutorProfile->payout_method === 'bank')
                                Bank: {{ $tutorProfile->payout_bank_name }} - {{ $tutorProfile->payout_bank_account_number }}
                            @else
                                No payout method set
                            @endif
                        </span>
                        <a href="{{ route('tutors.profile.settings') }}" style="display: inline-block; font-size: 0.75rem; color: var(--secondary-blue); font-weight: 600; margin-top: 0.35rem; text-decoration: none;">Edit Payout Method</a>
                    </div>

                    <button type="submit" @if($availableBalance < $minPayoutAmount || !$tutorProfile->payout_method) disabled @endif style="width: 100%; background: var(--secondary-blue); color: white; border: none; padding: 0.75rem; border-radius: 8px; font-weight: 700; cursor: pointer; opacity: {{ ($availableBalance < $minPayoutAmount || !$tutorProfile->payout_method) ? '0.5' : '1' }};">
                        <i class="fa-solid fa-paper-plane" style="margin-right: 0.35rem;"></i> Request Paystack Payout
                    </button>
                </form>

                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 1rem; line-height: 1.4;">
                    <i class="fa-solid fa-shield-halved" style="color: var(--secondary-blue);"></i> Payouts are dispatched via Paystack Transfers API to your verified account based on Paystack settlement timelines.
                </p>
            </div>
        </div>

        <!-- Session Earnings Table -->
        <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin: 0;">Completed Session Earnings</h3>
                <a href="{{ route('tutors.earnings.transactions') }}" style="font-size: 0.85rem; font-weight: 600; color: var(--secondary-blue); text-decoration: none;">View Full History</a>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: var(--gray-50); border-bottom: 1px solid var(--border-color); color: var(--text-muted);">
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Date</th>
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Student</th>
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Subject</th>
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Gross Paid</th>
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Commission</th>
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Net Payout</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($recentEarnings) && count($recentEarnings) > 0)
                            @foreach($recentEarnings as $booking)
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 0.85rem 1.25rem; color: var(--text-main);">{{ $booking->updated_at->format('M d, Y') }}</td>
                                    <td style="padding: 0.85rem 1.25rem; color: var(--text-main); font-weight: 600;">{{ $booking->student->name ?? 'Student' }}</td>
                                    <td style="padding: 0.85rem 1.25rem; color: var(--text-muted);">{{ $booking->subject->name ?? 'General' }}</td>
                                    <td style="padding: 0.85rem 1.25rem; color: var(--text-main);">GHS {{ number_format($booking->credits_paid, 2) }}</td>
                                    <td style="padding: 0.85rem 1.25rem; color: var(--primary-red);">-GHS {{ number_format($booking->commission_amount, 2) }}</td>
                                    <td style="padding: 0.85rem 1.25rem; color: #10b981; font-weight: 700;">+GHS {{ number_format($booking->credits_paid - $booking->commission_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-muted);">No completed session earnings records found.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Script for Revenue Line Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Net Revenue (GHS)',
                        data: @json($chartData),
                        borderColor: '#2677B8',
                        backgroundColor: 'rgba(38, 119, 184, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>
@endsection
