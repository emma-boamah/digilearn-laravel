@extends('settings.layout')

@section('title', 'Billing History')
@section('breadcrumb', 'Billing / History')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
    <div>
        <h1 class="page-title" style="margin-bottom: 0.5rem;">Billing History</h1>
        <p class="page-description">View and manage your school's subscription and payment records.</p>
    </div>
    <button style="display: flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; background-color: white; border: 1px solid var(--border-color); border-radius: 0.5rem; color: var(--text-main); font-weight: 600; cursor: pointer; font-size: 0.875rem;">
        <i class="fas fa-download"></i>
        Export CSV
    </button>
</div>

<!-- Filters -->
<div style="background-color: var(--bg-card); border-radius: 1rem; border: 1px solid var(--border-color); padding: 1rem; margin-bottom: 2rem;">
    <form action="{{ route('settings.billing-history') }}" method="GET" style="display: flex; gap: 1rem; align-items: center;">
        <div style="flex: 1; position: relative;">
            <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Invoice ID or Plan..." style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.75rem; border-radius: 0.75rem; border: 1px solid var(--border-color); background-color: var(--bg-body); font-size: 0.875rem;">
        </div>
        
        <select name="date_range" style="padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid var(--border-color); background-color: var(--bg-body); font-size: 0.875rem; min-width: 160px;">
            <option value="">All Time</option>
            <option value="3months" {{ request('date_range') == '3months' ? 'selected' : '' }}>Last 3 Months</option>
            <option value="6months" {{ request('date_range') == '6months' ? 'selected' : '' }}>Last 6 Months</option>
            <option value="12months" {{ request('date_range') == '12months' ? 'selected' : '' }}>Last 12 Months</option>
        </select>

        <select name="status" style="padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid var(--border-color); background-color: var(--bg-body); font-size: 0.875rem; min-width: 140px;">
            <option value="">All Statuses</option>
            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Paid</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
        </select>

        <button type="submit" style="padding: 0.75rem 1.5rem; background-color: var(--primary-color); color: white; border: none; border-radius: 0.75rem; font-weight: 600; cursor: pointer;">Filter</button>
    </form>
</div>

<!-- History Table -->
<div style="background-color: var(--bg-card); border-radius: 1rem; border: 1px solid var(--border-color); overflow: hidden; margin-bottom: 2rem;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background-color: var(--bg-body); border-bottom: 1px solid var(--border-color);">
            <tr>
                <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Invoice ID</th>
                <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Date</th>
                <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Plan</th>
                <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Amount (GHS)</th>
                <th style="padding: 1.25rem 1.5rem; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr style="border-bottom: 1px solid var(--border-color); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--bg-body)'" onmouseout="this.style.backgroundColor='transparent'">
                    <td style="padding: 1.25rem 1.5rem;">
                        <a href="#" style="color: var(--primary-color); font-weight: 700; text-decoration: none;">#INV-{{ $payment->created_at->format('Y') }}-{{ str_pad($payment->id, 3, '0', STR_PAD_LEFT) }}</a>
                    </td>
                    <td style="padding: 1.25rem 1.5rem;">
                        <div style="font-weight: 600; color: var(--text-main);">{{ $payment->created_at->format('M d, Y') }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $payment->created_at->format('H:i A') }}</div>
                    </td>
                    <td style="padding: 1.25rem 1.5rem;">
                        <div style="font-weight: 600; color: var(--text-main);">{{ $payment->pricingPlan->name ?? 'Unknown Plan' }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Subscription</div>
                    </td>
                    <td style="padding: 1.25rem 1.5rem;">
                        <span style="font-weight: 700; color: var(--text-main);">{{ number_format($payment->amount, 2) }}</span>
                    </td>
                    <td style="padding: 1.25rem 1.5rem;">
                        @if($payment->status === 'success')
                            <span style="background-color: #dcfce7; color: #166534; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; font-size: 0.75rem;">Paid</span>
                        @elseif($payment->status === 'pending')
                            <span style="background-color: #fef9c3; color: #854d0e; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; font-size: 0.75rem;">Pending</span>
                        @else
                            <span style="background-color: #fee2e2; color: #991b1b; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; font-size: 0.75rem;">{{ ucfirst($payment->status) }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 3rem; text-align: center; color: var(--text-muted);">No billing history found matching your filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Pagination -->
    @if($payments->hasPages())
        <div style="padding: 1.25rem 1.5rem; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 0.875rem; color: var(--text-secondary);">
                Showing 1 to {{ $payments->count() }} of {{ $payments->total() }} results
            </div>
            <div class="custom-pagination">
                {{ $payments->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
    <div style="background-color: var(--bg-card); border-radius: 1rem; border: 1px solid var(--border-color); padding: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Total Spent (Year)</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);">GHS {{ number_format($totalSpentYear, 2) }}</div>
        </div>
        <div style="width: 48px; height: 48px; background-color: #eff6ff; color: var(--primary-color); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
            <i class="fas fa-credit-card"></i>
        </div>
    </div>
    
    <div style="background-color: var(--bg-card); border-radius: 1rem; border: 1px solid var(--border-color); padding: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Next Invoice</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);">{{ $nextInvoiceDate ? $nextInvoiceDate->format('M d, Y') : 'N/A' }}</div>
        </div>
        <div style="width: 48px; height: 48px; background-color: #fff7ed; color: #f97316; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
            <i class="fas fa-calendar-alt"></i>
        </div>
    </div>

    <div style="background-color: var(--bg-card); border-radius: 1rem; border: 1px solid var(--border-color); padding: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Outstanding Balance</div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);">GHS {{ number_format($outstandingBalance, 2) }}</div>
        </div>
        <div style="width: 48px; height: 48px; background-color: #f0fdf4; color: #22c55e; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>
</div>

<style>
    .custom-pagination nav {
        display: flex;
        gap: 0.5rem;
    }
    .custom-pagination span, .custom-pagination a {
        padding: 0.5rem 0.875rem;
        border-radius: 0.5rem;
        border: 1px solid var(--border-color);
        background-color: white;
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.2s;
    }
    .custom-pagination .active span {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    .custom-pagination a:hover {
        background-color: var(--bg-body);
        color: var(--primary-color);
    }
</style>
@endsection
