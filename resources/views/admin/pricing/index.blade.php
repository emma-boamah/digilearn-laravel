@extends('layouts.admin')

@section('title', 'Manage Pricing Plans')
@section('page-title', 'Manage Pricing Plans')
@section('page-description', 'Create, edit, and manage subscription pricing plans for your platform.')

@section('content')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .pricing-container {
        max-width: 80rem;
        margin: 0 auto;
        padding: 1rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background-color: var(--white);
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid var(--gray-200);
    }

    .stat-icon {
        width: 4rem;
        height: 4rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon.primary {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue);
    }

    .stat-icon.secondary {
        background-color: var(--accent-red-light);
        color: var(--accent-red);
    }

    .stat-icon.accent {
        background-color: #f0f9ff;
        color: #0284c7;
    }

    .plans-section {
        background-color: var(--white);
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        border: 1px solid var(--gray-200);
        padding: 1.5rem;
    }

    .filter-section {
        background-color: #f8fafc;
        padding: 1rem;
        border-radius: 0.5rem;
        border: 1px solid var(--gray-200);
        margin-bottom: 1.5rem;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .plans-table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 0.5rem;
        overflow: hidden;
        border: 1px solid var(--gray-200);
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }

    .plans-table th {
        background-color: #f8fafc;
        padding: 0.75rem 1.5rem;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .plans-table td {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--gray-200);
        background-color: var(--white);
    }

    .plans-table tr:hover td {
        background-color: #f8fafc;
    }

    .action-btn {
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        margin-right: 0.5rem;
        transition: all 0.2s;
    }

    .action-btn.edit {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue);
    }

    .action-btn.delete {
        background-color: var(--accent-red-light);
        color: var(--accent-red);
    }

    .action-btn:hover {
        opacity: 0.8;
        transform: translateY(-1px);
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-active {
        background-color: #dcfce7;
        color: #166534;
    }

    .status-inactive {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .featured-badge {
        background-color: #fef3c7;
        color: #92400e;
        padding: 0.125rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.625rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
    }

    .empty-icon {
        width: 4rem;
        height: 4rem;
        background-color: var(--gray-100);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: var(--gray-400);
    }

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid var(--gray-300);
        background-color: var(--white);
        color: var(--gray-900);
        transition: all 0.2s;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px var(--primary-blue-light);
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .btn-primary {
        background-color: var(--primary-blue);
        color: var(--white);
        padding: 0.75rem 2rem;
        border-radius: 0.5rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary:hover {
        background-color: var(--primary-blue-hover);
    }

    .btn-secondary {
        background-color: var(--accent-red);
        color: var(--white);
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-secondary:hover {
        background-color: var(--accent-red-hover);
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="pricing-container">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div>
                <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-500);">Total Plans</h3>
                <p style="font-size: 2.25rem; font-weight: 700; color: var(--primary-blue); margin-top: 0.5rem;">{{ $totalPlans }}</p>
            </div>
            <div class="stat-icon primary">
                <svg style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-500);">Active Plans</h3>
                <p style="font-size: 2.25rem; font-weight: 700; color: var(--accent-red); margin-top: 0.5rem;">{{ $activePlans }}</p>
                <div style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.25rem;">
                    {{ $inactivePlans }} inactive
                </div>
            </div>
            <div class="stat-icon secondary">
                <svg style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-500);">Most Popular</h3>
                @if($pricingPlans->where('is_featured', true)->first())
                    <p style="font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin-top: 0.5rem;">{{ $pricingPlans->where('is_featured', true)->first()->name }}</p>
                    <p style="font-size: 0.875rem; color: var(--gray-500);">GHS {{ number_format($pricingPlans->where('is_featured', true)->first()->price, 2) }}</p>
                @else
                    <p style="font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin-top: 0.5rem;">N/A</p>
                    <p style="font-size: 0.875rem; color: var(--gray-500);">No featured plans</p>
                @endif
            </div>
            <div class="stat-icon accent">
                <svg style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Plans List -->
    <div class="plans-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 600; color: var(--gray-900);">All Pricing Plans</h2>
            <a href="{{ route('admin.pricing.create') }}" class="btn-primary">
                <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create New Plan
            </a>
        </div>

        <!-- Search and Filter Section -->
        <div class="filter-section">
            <form action="{{ route('admin.pricing.index') }}" method="GET" class="filter-grid">
                <div>
                    <label for="search" class="form-label">Search Plans</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search by name..." class="form-input">
                </div>
                <div>
                    <label for="period" class="form-label">Period</label>
                    <select name="period" id="period" class="form-input">
                        <option value="">All Periods</option>
                        <option value="monthly" {{ request('period') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ request('period') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                        <option value="lifetime" {{ request('period') == 'lifetime' ? 'selected' : '' }}>Lifetime</option>
                        <option value="one-time" {{ request('period') == 'one-time' ? 'selected' : '' }}>One-time</option>
                    </select>
                </div>
                <div>
                    <label for="is_active" class="form-label">Status</label>
                    <select name="is_active" id="is_active" class="form-input">
                        <option value="">All</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn-primary">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.pricing.index') }}" style="background-color: var(--gray-100); color: var(--gray-500); padding: 0.75rem 1rem; border-radius: 0.5rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Plans Table -->
        <div style="overflow-x: auto;">
            <table class="plans-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th style="width: 8rem;">Price</th>
                        <th style="width: 6rem;">Period</th>
                        <th style="width: 6rem;">Status</th>
                        <th style="width: 6rem;">Featured</th>
                        <th style="width: 8rem;">Sort Order</th>
                        <th style="width: 10rem;">Created</th>
                        <th style="width: 12rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pricingPlans as $plan)
                    <tr>
                        <td style="font-size: 0.875rem; font-weight: 500; color: var(--gray-900);">
                            {{ $plan->name }}
                            <div style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.25rem;">{{ Str::limit($plan->description, 50) }}</div>
                        </td>
                        <td style="font-size: 0.875rem; color: var(--gray-900); font-weight: 600;">
                            {{ $plan->currency }} {{ number_format($plan->price, 2) }}
                        </td>
                        <td style="font-size: 0.875rem; color: var(--gray-500); text-transform: capitalize;">
                            {{ $plan->period }}
                        </td>
                        <td>
                            <span class="status-badge status-{{ $plan->is_active ? 'active' : 'inactive' }}">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @if($plan->is_featured)
                                <span class="featured-badge">Featured</span>
                            @else
                                <span style="color: var(--gray-400); font-size: 0.75rem;">—</span>
                            @endif
                        </td>
                        <td style="font-size: 0.875rem; color: var(--gray-500);">
                            {{ $plan->sort_order }}
                        </td>
                        <td style="font-size: 0.875rem; color: var(--gray-500);">
                            {{ $plan->created_at->format('M d, Y') }}
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <a href="{{ route('admin.pricing.edit', $plan) }}" class="action-btn edit">
                                    Edit
                                </a>
                                <form action="{{ route('admin.pricing.toggle-active', $plan) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    <button type="submit" class="action-btn {{ $plan->is_active ? 'delete' : 'edit' }}" style="background-color: {{ $plan->is_active ? 'var(--accent-red-light)' : 'var(--primary-blue-light)' }}; color: {{ $plan->is_active ? 'var(--accent-red)' : 'var(--primary-blue)' }};">
                                        {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.pricing.destroy', $plan) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this pricing plan?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <div class="empty-icon">
                                <svg style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 style="font-size: 1.125rem; font-weight: 500; color: var(--gray-900); margin-bottom: 0.5rem;">No pricing plans found</h3>
                            <p style="color: var(--gray-600); margin-bottom: 1rem;">Get started by creating your first pricing plan</p>
                            <a href="{{ route('admin.pricing.create') }}" class="btn-primary">
                                Create Pricing Plan
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="margin-top: 1.5rem;">
            {{ $pricingPlans->links() }}
        </div>
    </div>
</div>
@endsection