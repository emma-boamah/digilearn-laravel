@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
    <div style="flex: 1; display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main);">Earnings & Payout Transactions</h2>
    </div>
@endsection

@section('content')
    <div style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
        <!-- Completed Session Earnings Table -->
        <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 2rem;">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin: 0;">Completed Session Credits Released</h3>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: var(--gray-50); border-bottom: 1px solid var(--border-color); color: var(--text-muted);">
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Date</th>
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Student</th>
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Subject</th>
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Gross Paid</th>
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Platform Fee</th>
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Net Credited</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($completedBookings) && count($completedBookings) > 0)
                            @foreach($completedBookings as $booking)
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 0.85rem 1.25rem; color: var(--text-main);">{{ $booking->updated_at->format('M d, Y @ h:i A') }}</td>
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
            @if(method_exists($completedBookings, 'links'))
                <div style="padding: 1rem;">
                    {{ $completedBookings->links() }}
                </div>
            @endif
        </div>

        <!-- Payout Requests Table -->
        <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin: 0;">Withdrawal / Payout Requests</h3>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: var(--gray-50); border-bottom: 1px solid var(--border-color); color: var(--text-muted);">
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Reference</th>
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Date</th>
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Method & Account</th>
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Amount</th>
                            <th style="padding: 0.85rem 1.25rem; font-weight: 600;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($payouts) && count($payouts) > 0)
                            @foreach($payouts as $payout)
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 0.85rem 1.25rem; font-family: monospace; font-weight: 600;">{{ $payout->reference }}</td>
                                    <td style="padding: 0.85rem 1.25rem; color: var(--text-main);">{{ $payout->created_at->format('M d, Y @ h:i A') }}</td>
                                    <td style="padding: 0.85rem 1.25rem; color: var(--text-main);">
                                        <span style="text-transform: uppercase; font-weight: 600;">{{ $payout->payout_method }}</span> - {{ $payout->bank_name }} ({{ $payout->account_number }})
                                    </td>
                                    <td style="padding: 0.85rem 1.25rem; color: var(--text-main); font-weight: 700;">GHS {{ number_format($payout->amount, 2) }}</td>
                                    <td style="padding: 0.85rem 1.25rem;">
                                        @if($payout->status === 'completed')
                                            <span style="background-color: #d1fae5; color: #065f46; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;">Completed</span>
                                        @elseif($payout->status === 'processing')
                                            <span style="background-color: #dbeafe; color: #1e40af; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;">Processing</span>
                                        @elseif($payout->status === 'failed')
                                            <span style="background-color: #fee2e2; color: #991b1b; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;">Failed</span>
                                        @else
                                            <span style="background-color: #fef3c7; color: #92400e; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" style="padding: 2rem; text-align: center; color: var(--text-muted);">No payout requests found.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @if(method_exists($payouts, 'links'))
                <div style="padding: 1rem;">
                    {{ $payouts->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
