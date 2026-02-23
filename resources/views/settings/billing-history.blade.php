@extends('settings.layout')

@section('title', 'Billing History')
@section('breadcrumb', 'Billing / History')

@section('content')
<div class="page-header flex justify-between items-start mb-8 flex-wrap gap-4 max-w-full">
    <div class="flex-1 min-w-200">
        <h1 class="page-title mb-2 break-word">Billing History</h1>
        <p class="page-description text-wrap">View and manage your school's subscription and payment records.</p>
    </div>
    <button class="flex items-center gap-2 p-2-5 bg-white border border-color rounded-lg text-main font-semibold cursor-pointer text-sm shadow-sm no-underline whitespace-nowrap">
        <i class="fas fa-download"></i>
        Export CSV
    </button>
</div>

<!-- Filters -->
<div class="filters-container bg-card rounded-2xl border p-4 mb-8">
    <form action="{{ route('settings.billing-history') }}" method="GET" class="flex gap-4 items-center flex-wrap">
        <div class="flex-1 relative min-w-250">
            <i class="fas fa-search filter-search-icon"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Invoice ID or Plan..." class="w-full p-4 pl-11 rounded-xl border bg-body text-sm">
        </div>
        
        <div class="flex gap-4 flex-1 min-w-300 flex-wrap">
            <select name="date_range" class="flex-1 p-3 rounded-xl border bg-body text-sm min-w-140">
                <option value="">All Time</option>
                <option value="3months" {{ request('date_range') == '3months' ? 'selected' : '' }}>Last 3 Months</option>
                <option value="6months" {{ request('date_range') == '6months' ? 'selected' : '' }}>Last 6 Months</option>
                <option value="12months" {{ request('date_range') == '12months' ? 'selected' : '' }}>Last 12 Months</option>
            </select>
            <select name="status" class="flex-1 p-3 rounded-xl border bg-body text-sm min-w-140">
                <option value="">All Statuses</option>
                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Paid</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
            <button type="submit" class="p-3 px-6 bg-primary text-white border-none rounded-xl font-semibold cursor-pointer min-w-100">Filter</button>
        </div>
    </form>
</div>

<!-- History Table -->
<div class="bg-card rounded-2xl border overflow-hidden mb-8">
    <div class="table-responsive">
        <table class="w-full border-collapse text-left min-w-800">
            <thead class="bg-body border-bottom">
                <tr>
                    <th class="p-5 text-xs font-bold text-muted uppercase tracking-wider">Invoice ID</th>
                    <th class="p-5 text-xs font-bold text-muted uppercase tracking-wider">Date</th>
                    <th class="p-5 text-xs font-bold text-muted uppercase tracking-wider">Plan</th>
                    <th class="p-5 text-xs font-bold text-muted uppercase tracking-wider">Amount (GHS)</th>
                    <th class="p-5 text-xs font-bold text-muted uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr class="border-bottom row-hover-bg">
                        <td class="p-5">
                            <a href="#" class="text-primary font-bold no-underline">#INV-{{ $payment->created_at->format('Y') }}-{{ str_pad($payment->id, 3, '0', STR_PAD_LEFT) }}</a>
                        </td>
                        <td class="p-5">
                            <div class="font-semibold text-main">{{ $payment->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-muted">{{ $payment->created_at->format('H:i A') }}</div>
                        </td>
                        <td class="p-5">
                            <div class="font-semibold text-main">{{ $payment->pricingPlan->name ?? 'Unknown Plan' }}</div>
                            <div class="text-xs text-muted">Subscription</div>
                        </td>
                        <td class="p-5">
                            <span class="font-bold text-main">{{ number_format($payment->amount, 2) }}</span>
                        </td>
                        <td class="p-5">
                            @if($payment->status === 'success')
                                <span class="bg-green-50 text-green-700 px-3 py-1 rounded-full font-semibold text-xs">Paid</span>
                            @elseif($payment->status === 'pending')
                                <span class="bg-orange-50 text-orange-700 px-3 py-1 rounded-full font-semibold text-xs">Pending</span>
                            @else
                                <span class="bg-red-50 text-red-700 px-3 py-1 rounded-full font-semibold text-xs">{{ ucfirst($payment->status) }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-muted">No billing history found matching your filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($payments->hasPages())
        <div class="p-5 border-top flex justify-between items-center flex-wrap gap-4">
            <div class="text-sm text-secondary">
                Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} results
            </div>
            <div class="custom-pagination">
                {{ $payments->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Summary Cards -->
<div class="summary-cards-grid grid gap-6 max-w-full">
    <div class="bg-card rounded-2xl border p-6 flex justify-between items-center min-w-0">
        <div class="min-w-0 flex-1">
            <div class="text-sm text-secondary mb-2 whitespace-nowrap overflow-hidden ellipsis">Total Spent (Year)</div>
            <div class="text-xl font-extrabold text-main break-all">GHS {{ number_format($totalSpentYear, 2) }}</div>
        </div>
        <div class="w-12 h-12 bg-blue-50 text-primary rounded-xl flex items-center justify-center text-xl flex-shrink-0 ml-4">
            <i class="fas fa-credit-card"></i>
        </div>
    </div>
    
    <div class="bg-card rounded-2xl border p-6 flex justify-between items-center min-w-0">
        <div class="min-w-0 flex-1">
            <div class="text-sm text-secondary mb-2 whitespace-nowrap overflow-hidden ellipsis">Next Invoice</div>
            <div class="text-xl font-extrabold text-main">{{ $nextInvoiceDate ? $nextInvoiceDate->format('M d, Y') : 'N/A' }}</div>
        </div>
        <div class="w-12 h-12 bg-orange-50 text-orange-700 rounded-xl flex items-center justify-center text-xl flex-shrink-0 ml-4">
            <i class="fas fa-calendar-alt"></i>
        </div>
    </div>

    <div class="bg-card rounded-2xl border p-6 flex justify-between items-center min-w-0">
        <div class="min-w-0 flex-1">
            <div class="text-sm text-secondary mb-2 whitespace-nowrap overflow-hidden ellipsis">Outstanding Balance</div>
            <div class="text-xl font-extrabold text-main break-all">GHS {{ number_format($outstandingBalance, 2) }}</div>
        </div>
        <div class="w-12 h-12 bg-green-50 text-green-700 rounded-xl flex items-center justify-center text-xl flex-shrink-0 ml-4">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>
</div>

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .summary-cards-grid {
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    }
    .break-word { word-break: break-word; }
    .whitespace-nowrap { white-space: nowrap; }
    .ellipsis { text-overflow: ellipsis; }
    .min-w-200 { min-width: 200px; }
    .min-w-250 { min-width: 250px; }
    .min-w-300 { min-width: 300px; }
    .min-w-140 { min-width: 140px; }
    .min-w-100 { min-width: 100px; }
    .min-w-800 { min-width: 800px; }
    .p-2-5 { padding: 0.625rem 1rem; }
    .pl-11 { padding-left: 2.75rem; }
    .filter-search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
    .border-color { border-color: var(--border-color); }
    .row-hover-bg:hover { background-color: var(--bg-body); }
    .p-12 { padding: 3rem; }
    
    @media (max-width: 768px) {
        .min-w-200, .min-w-250, .min-w-300, .min-w-140, .min-w-100 {
            min-width: 0;
            width: 100%;
        }
        .filters-container form {
            flex-direction: column;
            align-items: stretch;
        }
        .filters-container .flex-1 {
            flex: none;
            width: 100%;
        }
        .filters-container .flex {
            flex-direction: column;
        }
    }
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
