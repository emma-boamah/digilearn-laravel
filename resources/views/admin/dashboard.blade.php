@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Overview of your learning platform')

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Chart and dashboard styles */
    .kpi-card:hover {
        transform: translateY(-2px);
    }

    .sparkline-container {
        opacity: 0.7;
    }

    .time-filter.active {
        background-color: #3b82f6;
        color: white;
    }

    .metric-toggle.active {
        background-color: #3b82f6;
        color: white;
    }

    .revenue-bar-group:hover .revenue-tooltip {
        display: block !important;
    }

    .revenue-bar-group:hover .revenue-bar {
        background: linear-gradient(180deg, #60a5fa 0%, #3b82f6 100%) !important;
        transform: scaleY(1.02);
        transform-origin: bottom;
    }

    .sortable:hover {
        cursor: pointer;
        background-color: #f9fafb;
    }

    .sortable.sort-asc i:before {
        content: '\f145';
        /* sort-up */
    }

    .sortable.sort-desc i:before {
        content: '\f144';
        /* sort-down */
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-4">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                    <p class="text-gray-600">Welcome back, {{ Auth::user()->name }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        Last updated: {{ now()->format('M d, Y H:i') }}
                    </div>
                    @role('super-admin')
                    <button id="refreshButton"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                    <button id="toggleLockButton"
                        class="@if($websiteLocked) bg-green-600 @else bg-red-600 @endif text-white px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">
                        <i class="fas @if($websiteLocked) fa-unlock @else fa-lock @endif mr-2"></i>
                        @if($websiteLocked) Unlock Website @else Lock Website @endif
                    </button>
                    @endrole
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-4 py-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-lg transition-all duration-200 cursor-pointer kpi-card"
                data-target="revenue-chart">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</p>
                        <div class="flex items-center justify-between mt-2">
                            <p class="text-sm text-green-600">
                                <i class="fas fa-arrow-up mr-1"></i>+{{ $stats['new_users_this_week'] }} this week
                            </p>
                            <div class="sparkline-container w-16 h-4">
                                <canvas id="users-sparkline" width="64" height="16"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-lg transition-all duration-200 cursor-pointer kpi-card"
                data-target="revenue-chart">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600">Online Users</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['online_users']) }}</p>
                        <div class="flex items-center justify-between mt-2">
                            <p class="text-sm text-gray-500">
                                {{ number_format(($stats['online_users'] / $stats['total_users']) * 100, 1) }}% of total
                            </p>
                            <div class="sparkline-container w-16 h-4">
                                <canvas id="online-sparkline" width="64" height="16"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-user-check text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            @role('super-admin')
            <!-- Total Revenue -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-lg transition-all duration-200 cursor-pointer kpi-card"
                data-target="revenue-chart">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                        <p class="text-3xl font-bold text-gray-900">GH₵{{ number_format($revenueData['total_revenue'] ??
                            0, 0) }}</p>
                        <div class="flex items-center justify-between mt-2">
                            <p class="text-sm text-green-600">
                                <i class="fas fa-arrow-up mr-1"></i>+{{ $revenueData['revenue_growth'] ?? 0 }}%
                            </p>
                            <div class="sparkline-container w-16 h-4">
                                <canvas id="revenue-sparkline" width="64" height="16"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Active Subscriptions -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 hover:shadow-lg transition-all duration-200 cursor-pointer kpi-card"
                data-target="plan-performance">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600">Active Subscriptions</p>
                        <p class="text-3xl font-bold text-gray-900">{{
                            number_format($revenueData['active_subscriptions'] ?? 0) }}</p>
                        <div class="flex items-center justify-between mt-2">
                            <p class="text-sm text-blue-600">
                                <i class="fas fa-calendar-day mr-1"></i>{{
                                number_format($revenueData['new_subscriptions_today'] ?? 0) }} new today
                            </p>
                            <div class="sparkline-container w-16 h-4">
                                <canvas id="subscriptions-sparkline" width="64" height="16"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-crown text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
            @endrole
        </div>
        @role('super-admin')
        <!-- Revenue Trend Chart (Full Width) - Pure CSS -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8" id="revenue-chart">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Revenue Trend</h2>
                    <p class="text-sm text-gray-600">Monthly revenue performance over time</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span
                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                            <span class="w-2 h-2 rounded-full bg-blue-500 mr-1"></span>Revenue
                        </span>
                        <span
                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1"></span>Subscriptions
                        </span>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600 export-btn" title="Export data">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>

            @php
            $trends = $revenueTrends ?? [];
            $maxRevenue = collect($trends)->max('revenue') ?: 1;
            $maxSubs = collect($trends)->max('subscriptions') ?: 1;
            @endphp

            <div
                style="display: flex; align-items: flex-end; gap: 6px; height: 320px; padding: 20px 0 0 0; border-bottom: 2px solid #e5e7eb; position: relative;">
                {{-- Y-axis labels --}}
                <div
                    style="position: absolute; left: -5px; top: 20px; bottom: 0; display: flex; flex-direction: column; justify-content: space-between; pointer-events: none;">
                    <span style="font-size: 10px; color: #9ca3af;">GH₵{{ number_format($maxRevenue, 0) }}</span>
                    <span style="font-size: 10px; color: #9ca3af;">GH₵{{ number_format($maxRevenue / 2, 0) }}</span>
                    <span style="font-size: 10px; color: #9ca3af;">GH₵0</span>
                </div>

                {{-- Grid lines --}}
                <div style="position: absolute; left: 50px; right: 0; top: 20px; height: 1px; background: #f3f4f6;">
                </div>
                <div style="position: absolute; left: 50px; right: 0; top: 50%; height: 1px; background: #f3f4f6;">
                </div>

                {{-- Bars --}}
                <div style="display: flex; align-items: flex-end; gap: 6px; flex: 1; margin-left: 55px; height: 100%;">
                    @foreach($trends as $trend)
                    @php
                    $barHeight = $maxRevenue > 0 ? ($trend['revenue'] / $maxRevenue) * 100 : 0;
                    $barHeight = max($barHeight, 2); // min height for visibility
                    $subsHeight = $maxSubs > 0 ? ($trend['subscriptions'] / $maxSubs) * 80 : 0;
                    $monthShort = \Illuminate\Support\Str::before($trend['month'], ' ');
                    @endphp
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end; position: relative;"
                        class="revenue-bar-group">
                        {{-- Tooltip --}}
                        <div class="revenue-tooltip"
                            style="display: none; position: absolute; bottom: calc({{ $barHeight }}% + 10px); left: 50%; transform: translateX(-50%); background: #1e293b; color: white; padding: 8px 12px; border-radius: 8px; font-size: 11px; white-space: nowrap; z-index: 10; box-shadow: 0 4px 6px rgba(0,0,0,0.15);">
                            <div style="font-weight: 600; margin-bottom: 2px;">{{ $trend['month'] }}</div>
                            <div><span style="color: #93c5fd;">Revenue:</span> GH₵{{ number_format($trend['revenue'], 0)
                                }}</div>
                            <div><span style="color: #6ee7b7;">Subs:</span> {{ $trend['subscriptions'] }}</div>
                            <div
                                style="position: absolute; bottom: -4px; left: 50%; transform: translateX(-50%) rotate(45deg); width: 8px; height: 8px; background: #1e293b;">
                            </div>
                        </div>
                        {{-- Subscription indicator dot --}}
                        @if($trend['subscriptions'] > 0)
                        <div
                            style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; margin-bottom: 4px; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);">
                        </div>
                        @endif
                        {{-- Revenue bar --}}
                        <div style="width: 100%; max-width: 40px; height: {{ $barHeight }}%; background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%); border-radius: 6px 6px 0 0; transition: all 0.2s ease; cursor: pointer; position: relative;"
                            class="revenue-bar"></div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- X-axis labels --}}
            <div style="display: flex; gap: 6px; margin-left: 55px; margin-top: 8px;">
                @foreach($trends as $trend)
                @php $monthShort = \Illuminate\Support\Str::before($trend['month'], ' '); @endphp
                <div style="flex: 1; text-align: center; font-size: 11px; color: #6b7280; font-weight: 500;">{{
                    $monthShort }}</div>
                @endforeach
            </div>

            {{-- Summary row --}}
            <div style="display: flex; gap: 16px; margin-top: 16px; padding-top: 16px; border-top: 1px solid #f3f4f6;">
                <div style="flex: 1; text-align: center; padding: 8px; background: #eff6ff; border-radius: 8px;">
                    <div style="font-size: 11px; color: #6b7280; font-weight: 500;">Total Revenue</div>
                    <div style="font-size: 18px; font-weight: 700; color: #1e40af;">GH₵{{
                        number_format(collect($trends)->sum('revenue'), 0) }}</div>
                </div>
                <div style="flex: 1; text-align: center; padding: 8px; background: #ecfdf5; border-radius: 8px;">
                    <div style="font-size: 11px; color: #6b7280; font-weight: 500;">Total Subscriptions</div>
                    <div style="font-size: 18px; font-weight: 700; color: #065f46;">{{
                        number_format(collect($trends)->sum('subscriptions')) }}</div>
                </div>
                <div style="flex: 1; text-align: center; padding: 8px; background: #faf5ff; border-radius: 8px;">
                    <div style="font-size: 11px; color: #6b7280; font-weight: 500;">Avg Monthly</div>
                    <div style="font-size: 18px; font-weight: 700; color: #7e22ce;">GH₵{{
                        number_format(collect($trends)->avg('revenue'), 0) }}</div>
                </div>
            </div>
        </div>
        @endrole

        @role('super-admin')
        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Subscription Distribution -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Subscription Distribution</h2>
                        <p class="text-sm text-gray-600">Revenue breakdown by plan type</p>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600 export-btn" title="Export data">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
                <div class="flex items-center">
                    <div class="flex-1">
                        <canvas id="subscriptionChart" width="200" height="200"></canvas>
                    </div>
                    <div class="ml-6 space-y-3" id="subscription-legend">
                        <!-- Legend will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Growth Metrics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Growth Metrics</h2>
                        <p class="text-sm text-gray-600">Key performance indicators</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button
                            class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors metric-toggle active"
                            data-metric="monthly">Monthly</data-metric>
                            <button
                                class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors metric-toggle"
                                data-metric="weekly">Weekly</data-metric>
                    </div>
                </div>
                <div class="space-y-6">
                    <div
                        class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-blue-900">Monthly Growth</p>
                            <p class="text-2xl font-bold text-blue-800" id="monthly-growth">+{{
                                $revenueData['revenue_growth'] ?? 0 }}%</p>
                        </div>
                        <div class="text-blue-600">
                            <i class="fas fa-chart-line text-3xl"></i>
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-green-900">Weekly Revenue</p>
                            <p class="text-2xl font-bold text-green-800" id="weekly-revenue">GH₵{{
                                number_format($revenueData['weekly_revenue'] ?? 0, 0) }}</p>
                        </div>
                        <div class="text-green-600">
                            <i class="fas fa-dollar-sign text-3xl"></i>
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-purple-900">Churn Rate</p>
                            <p class="text-2xl font-bold text-purple-800">{{ $revenueData['churn_rate'] ?? 0 }}%</p>
                        </div>
                        <div class="text-purple-600">
                            <i class="fas fa-users text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endrole

        @role('super-admin')
        <!-- Plan Performance Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8" id="plan-performance">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Plan Performance</h2>
                    <p class="text-sm text-gray-600">Detailed breakdown of subscription plans</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <input type="text" placeholder="Search plans..."
                            class="px-3 py-1 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            id="plan-search">
                    </div>
                    <button class="text-gray-400 hover:text-gray-600 export-btn" title="Export data">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="plan-performance-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable"
                                data-sort="plan">
                                Plan <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable"
                                data-sort="subscribers">
                                Subscribers <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable"
                                data-sort="revenue">
                                Revenue <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable"
                                data-sort="growth">
                                Growth <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable"
                                data-sort="churn">
                                Churn <i class="fas fa-sort ml-1"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($topPlans ?? [] as $plan)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full bg-blue-500 mr-3"></div>
                                    <div class="text-sm font-medium text-gray-900">{{ $plan['plan'] }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                                number_format($plan['subscribers']) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">GH₵{{
                                number_format($plan['revenue'], 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($plan['growth'] > 0) bg-green-100 text-green-800
                                    @elseif($plan['growth'] < 0) bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    @if($plan['growth'] > 0)+@endif{{ $plan['growth'] }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ rand(1, 5) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endrole

        @role('super-admin')
        <!-- Cookie Consent Stats -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Cookie Consent Analytics</h2>
                <a href="{{ route('admin.cookie-stats') }}"
                    class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    View Details <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-green-900">Total Consents</h3>
                            <p class="text-2xl font-bold text-green-800">{{
                                number_format($stats['cookie_consents']['total_consents']) }}</p>
                            <p class="text-sm text-green-700">{{
                                number_format($stats['cookie_consents']['consent_rate'], 1) }}% of users</p>
                        </div>
                        <div class="text-green-600">
                            <i class="fas fa-cookie-bite text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-blue-900">Analytics Accepted</h3>
                            <p class="text-2xl font-bold text-blue-800">{{
                                number_format($stats['cookie_consents']['analytics_accepted']) }}</p>
                            <p class="text-sm text-blue-700">Optional tracking</p>
                        </div>
                        <div class="text-blue-600">
                            <i class="fas fa-chart-line text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-purple-900">Recent Activity</h3>
                            <p class="text-2xl font-bold text-purple-800">{{
                                number_format($stats['cookie_consents']['recent_consents']) }}</p>
                            <p class="text-sm text-purple-700">Last 30 days</p>
                        </div>
                        <div class="text-purple-600">
                            <i class="fas fa-clock text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endrole

        @role('super-admin')
        <!-- Storage Monitoring Dashboard -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Storage Monitoring</h2>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if(isset($systemHealth['storage_usage']['issues']) && !empty($systemHealth['storage_usage']['issues']))
                            bg-red-100 text-red-800
                        @else
                            bg-green-100 text-green-800
                        @endif">
                        @if(isset($systemHealth['storage_usage']['issues']) &&
                        !empty($systemHealth['storage_usage']['issues']))
                        <i class="fas fa-exclamation-triangle mr-1"></i>Issues Detected
                        @else
                        <i class="fas fa-check-circle mr-1"></i>Healthy
                        @endif
                    </span>
                    <button id="storageDetailsBtn" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        View Details <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <!-- Overall Storage Usage -->
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-blue-900">Storage Usage</h3>
                            <p class="text-xl font-bold text-blue-800">{{
                                $systemHealth['storage_usage']['used_percentage'] ?? 'N/A' }}</p>
                            <p class="text-xs text-blue-700">
                                {{ $systemHealth['storage_usage']['total_used'] ?? 'N/A' }} used of {{
                                $systemHealth['storage_usage']['total_capacity'] ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="text-blue-600">
                            <i class="fas fa-hdd text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Storage Status -->
                <div class="bg-gradient-to-r
                    @if(isset($systemHealth['storage_usage']['status']) && $systemHealth['storage_usage']['status'] === 'critical') from-red-50 to-red-100 border-red-200
                    @elseif(isset($systemHealth['storage_usage']['status']) && $systemHealth['storage_usage']['status'] === 'warning') from-yellow-50 to-yellow-100 border-yellow-200
                    @elseif(isset($systemHealth['storage_usage']['status']) && $systemHealth['storage_usage']['status'] === 'caution') from-orange-50 to-orange-100 border-orange-200
                    @else from-green-50 to-green-100 border-green-200
                    @endif rounded-lg p-4 border">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold
                                @if(isset($systemHealth['storage_usage']['status']) && $systemHealth['storage_usage']['status'] === 'critical') text-red-900
                                @elseif(isset($systemHealth['storage_usage']['status']) && $systemHealth['storage_usage']['status'] === 'warning') text-yellow-900
                                @elseif(isset($systemHealth['storage_usage']['status']) && $systemHealth['storage_usage']['status'] === 'caution') text-orange-900
                                @else text-green-900
                                @endif">Status</h3>
                            <p class="text-xl font-bold
                                @if(isset($systemHealth['storage_usage']['status']) && $systemHealth['storage_usage']['status'] === 'critical') text-red-800
                                @elseif(isset($systemHealth['storage_usage']['status']) && $systemHealth['storage_usage']['status'] === 'warning') text-yellow-800
                                @elseif(isset($systemHealth['storage_usage']['status']) && $systemHealth['storage_usage']['status'] === 'caution') text-orange-800
                                @else text-green-800
                                @endif">{{ ucfirst($systemHealth['storage_usage']['status'] ?? 'Unknown') }}</p>
                        </div>
                        <div class="
                            @if(isset($systemHealth['storage_usage']['status']) && $systemHealth['storage_usage']['status'] === 'critical') text-red-600
                            @elseif(isset($systemHealth['storage_usage']['status']) && $systemHealth['storage_usage']['status'] === 'warning') text-yellow-600
                            @elseif(isset($systemHealth['storage_usage']['status']) && $systemHealth['storage_usage']['status'] === 'caution') text-orange-600
                            @else text-green-600
                            @endif">
                            @if(isset($systemHealth['storage_usage']['status']) &&
                            $systemHealth['storage_usage']['status'] === 'critical')
                            <i class="fas fa-exclamation-triangle text-2xl"></i>
                            @elseif(isset($systemHealth['storage_usage']['status']) &&
                            $systemHealth['storage_usage']['status'] === 'warning')
                            <i class="fas fa-exclamation-circle text-2xl"></i>
                            @elseif(isset($systemHealth['storage_usage']['status']) &&
                            $systemHealth['storage_usage']['status'] === 'caution')
                            <i class="fas fa-exclamation text-2xl"></i>
                            @else
                            <i class="fas fa-check-circle text-2xl"></i>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Alerts -->
                <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-purple-900">Recent Alerts</h3>
                            <p class="text-xl font-bold text-purple-800">{{
                                \App\Models\StorageAlert::recent(24)->count() }}</p>
                            <p class="text-xs text-purple-700">Last 24 hours</p>
                        </div>
                        <div class="text-purple-600">
                            <i class="fas fa-bell text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Growth Rate -->
                <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 rounded-lg p-4 border border-indigo-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-indigo-900">Growth Trend</h3>
                            <p class="text-xl font-bold text-indigo-800">
                                @php
                                $latestAnalytics =
                                \App\Models\StorageAnalytic::forPath(storage_path())->latest('measured_at')->first();
                                $growthRate = $latestAnalytics ? $latestAnalytics->growth_rate_percentage : 0;
                                @endphp
                                @if($growthRate > 0)
                                <span class="text-red-600">+{{ number_format($growthRate, 1) }}%</span>
                                @elseif($growthRate < 0) <span class="text-green-600">{{ number_format($growthRate, 1)
                                    }}%</span>
                                    @else
                                    <span>0.0%</span>
                                    @endif
                            </p>
                            <p class="text-xs text-indigo-700">Per hour</p>
                        </div>
                        <div class="text-indigo-600">
                            @if($growthRate > 0)
                            <i class="fas fa-arrow-up text-2xl"></i>
                            @elseif($growthRate < 0) <i class="fas fa-arrow-down text-2xl"></i>
                                @else
                                <i class="fas fa-minus text-2xl"></i>
                                @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Storage Usage Progress Bar -->
            <div class="mb-4">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Storage Capacity</span>
                    <span class="text-gray-900">{{ $systemHealth['storage_usage']['used_percentage'] ?? 'N/A' }}</span>
                </div>
                <div class="bg-gray-200 rounded-full h-3">
                    @php
                    $percentage = isset($systemHealth['storage_usage']['used_percentage']) ? str_replace('%', '',
                    $systemHealth['storage_usage']['used_percentage']) : '0';
                    $percentage = min(100, max(0, floatval($percentage)));
                    @endphp
                    <div class="h-3 rounded-full transition-all duration-300 storage-progress-bar
                        @if($percentage >= 95) bg-red-600
                        @elseif($percentage >= 85) bg-yellow-500
                        @elseif($percentage >= 75) bg-orange-500
                        @else bg-green-600
                        @endif" data-width="{{ $percentage }}"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>{{ $systemHealth['storage_usage']['total_used'] ?? 'N/A' }} used</span>
                    <span>{{ $systemHealth['storage_usage']['total_capacity'] ?? 'N/A' }} total</span>
                </div>
            </div>

            <!-- Issues Alert -->
            @if(isset($systemHealth['storage_usage']['issues']) && !empty($systemHealth['storage_usage']['issues']))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Storage Issues Detected</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach($systemHealth['storage_usage']['issues'] as $issue)
                                <li>{{ $issue }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endrole

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        </div>

        @role('super-admin')
        <!-- Subscription Plans Badges -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Subscription Plans</h2>
                <a href="{{ route('admin.revenue') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    View Revenue Analytics <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($stats['subscription_plans'] as $plan)
                <div
                    class="bg-gradient-to-r from-{{ $plan['color'] }}-50 to-{{ $plan['color'] }}-100 rounded-lg p-4 border border-{{ $plan['color'] }}-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-{{ $plan['color'] }}-900">{{ $plan['name'] }}</h3>
                            <p class="text-2xl font-bold text-{{ $plan['color'] }}-800">{{
                                number_format($plan['subscribers']) }}</p>
                            <p class="text-sm text-{{ $plan['color'] }}-700">subscribers</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-{{ $plan['color'] }}-900">${{
                                number_format($plan['revenue'], 0) }}</p>
                            <p class="text-sm text-{{ $plan['color'] }}-700">revenue</p>
                        </div>
                    </div>
                    <div class="mt-3 bg-{{ $plan['color'] }}-200 rounded-full h-2">
                        <div class="bg-{{ $plan['color'] }}-600 h-2 rounded-full plan-progress-bar"
                            data-width="{{ collect($stats['subscription_plans'])->sum('subscribers') > 0 ? ($plan['subscribers'] / collect($stats['subscription_plans'])->sum('subscribers')) * 100 : 0 }}">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endrole

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Activities -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-gray-900">Recent Activities</h2>
                            <a href="{{ route('admin.security') }}"
                                class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                View All <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($recentActivities as $activity)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    @if($activity->type === 'user_registration')
                                    <div class="bg-green-100 p-2 rounded-full">
                                        <i class="fas fa-user-plus text-green-600 text-sm"></i>
                                    </div>
                                    @elseif($activity->type === 'lesson_view')
                                    <div class="bg-blue-100 p-2 rounded-full">
                                        <i class="fas fa-play text-blue-600 text-sm"></i>
                                    </div>
                                    @elseif($activity->type === 'login_attempt')
                                    <div class="bg-purple-100 p-2 rounded-full">
                                        <i class="fas fa-sign-in-alt text-purple-600 text-sm"></i>
                                    </div>
                                    @else
                                    <div class="bg-gray-100 p-2 rounded-full">
                                        <i class="fas fa-edit text-gray-600 text-sm"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">{{ $activity->user ? $activity->user->name : 'Unknown
                                            User' }}</span>
                                        @if($activity->type === 'page_view')
                                        {{ $activity->description }}
                                        @elseif($activity->type === 'user_registration')
                                        registered a new account
                                        @elseif($activity->type === 'lesson_view')
                                        viewed lesson "{{ $activity->metadata['lesson'] ?? 'N/A' }}"
                                        @elseif($activity->type === 'login_attempt')
                                        {{ ($activity->metadata['status'] ?? 'unknown') === 'success' ? 'logged in
                                        successfully' : 'failed to log in' }}
                                        @elseif($activity->type === 'profile_update')
                                        updated their profile
                                        @elseif($activity->type === 'password_change')
                                        changed their password
                                        @else
                                        {{ $activity->description }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $activity->created_at->diffForHumans() }}
                                        @if($activity->ip_address)
                                        • {{ $activity->ip_address }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            @role('super-admin')
            <!-- System Health -->
            <div class="space-y-6">
                <!-- System Status -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">System Health</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Server Status</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-circle text-green-400 mr-1 text-xs"></i>
                                    {{ ucfirst($systemHealth['server_status'] ?? 'Running') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Database</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-circle text-green-400 mr-1 text-xs"></i>
                                    {{ ucfirst($systemHealth['database_status']['status'] ?? 'Connected') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Cache</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-circle text-green-400 mr-1 text-xs"></i>
                                    {{ ucfirst($systemHealth['cache_status']['status'] ?? 'Ready') }}
                                </span>
                            </div>
                            <div class="pt-4 border-t border-gray-200">
                                <div class="space-y-3">
                                    <div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Storage Usage</span>
                                            <span class="text-gray-900">{{
                                                $systemHealth['storage_usage']['used_percentage'] ?? '0%' }}</span>
                                        </div>
                                        <div class="mt-1 bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full progress-bar-storage"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Memory Usage</span>
                                            <span class="text-gray-900">{{ $systemHealth['memory_usage'] ?? 'N/A'
                                                }}</span>
                                        </div>
                                        <div class="mt-1 bg-gray-200 rounded-full h-2">
                                            <div class="bg-yellow-500 h-2 rounded-full progress-bar-memory"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">CPU Usage</span>
                                            <span class="text-gray-900">{{ $systemHealth['cpu_usage'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="mt-1 bg-gray-200 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full progress-bar-cpu"></div>
                                        </div>
                                    </div>
                                    <a href="{{ route('admin.credentials') }}"
                                        class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                        <div class="bg-indigo-100 p-2 rounded-lg mr-3">
                                            <i class="fas fa-key text-indigo-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Superuser Credentials</p>
                                            <p class="text-xs text-gray-500">Manage website lock credentials</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-gray-200 text-xs text-gray-500">
                                <p>Uptime: {{ $systemHealth['uptime'] ?? 'N/A' }}</p>
                                <p>Last Backup: {{ $systemHealth['last_backup'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endrole

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Quick Actions</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @role('super-admin')
                            <a href="{{ route('admin.users') }}"
                                class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-users text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Manage Users</p>
                                    <p class="text-xs text-gray-500">View and manage user accounts</p>
                                </div>
                            </a>
                            @endrole

                            <a href="{{ route('admin.contents.index') }}"
                                class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-book text-purple-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Content Management</p>
                                    <p class="text-xs text-gray-500">Manage lessons and content</p>
                                </div>
                            </a>

                            <a href="{{ route('admin.subjects.index') }}"
                                class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="bg-teal-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-tags text-teal-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Manage Subjects</p>
                                    <p class="text-xs text-gray-500">Organize content by subjects</p>
                                </div>
                            </a>

                            @role('super-admin')
                            <a href="{{ route('admin.analytics') }}"
                                class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="bg-green-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-chart-bar text-green-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Analytics</p>
                                    <p class="text-xs text-gray-500">View platform analytics</p>
                                </div>
                            </a>

                            <a href="{{ route('admin.security') }}"
                                class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="bg-red-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-shield-alt text-red-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Security Monitor</p>
                                    <p class="text-xs text-gray-500">Monitor security events</p>
                                </div>
                            </a>
                            @endrole
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Initialize everything when DOM is loaded
    document.addEventListener('DOMContentLoaded', function () {
        // Attach critical administrative listeners first for resilience
        const refreshBtn = document.getElementById('refreshButton');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function () {
                location.reload();
            });
        }

        const toggleLockBtn = document.getElementById('toggleLockButton');
        if (toggleLockBtn) {
            toggleLockBtn.addEventListener('click', function () {
                handleToggleLock.call(this);
            });
        }

        initializeProgressBars();
        initializeSparklines();
        initializeCharts();
        setupEventListeners();
    });

    // Initialize progress bars with data attributes
    function initializeProgressBars() {
        // Storage progress bar
        const storageBar = document.querySelector('.storage-progress-bar');
        if (storageBar) {
            const width = storageBar.getAttribute('data-width') || '0';
            storageBar.style.width = width + '%';
        }

        // Plan progress bars
        const planBars = document.querySelectorAll('.plan-progress-bar');
        planBars.forEach(bar => {
            const width = bar.getAttribute('data-width') || '0';
            bar.style.width = width + '%';
        });
    }

    // Initialize sparklines for KPI cards
    function initializeSparklines() {
        // Sample data for sparklines
        const sparklineData = {
            users: [1200, 1250, 1180, 1320, 1280, 1350, 1420],
            online: [85, 92, 78, 95, 88, 102, 98],
            revenue: [8500, 9200, 8800, 9600, 9100, 9800, 10200],
            subscriptions: [45, 48, 42, 52, 49, 55, 58]
        };

        // Create sparklines
        Object.keys(sparklineData).forEach(key => {
            const canvas = document.getElementById(key + '-sparkline');
            if (canvas) {
                createSparkline(canvas, sparklineData[key]);
            }
        });
    }

    // Create sparkline chart
    function createSparkline(canvas, data) {
        const ctx = canvas.getContext('2d');
        const width = canvas.width;
        const height = canvas.height;

        // Clear canvas
        ctx.clearRect(0, 0, width, height);

        // Find min/max
        const min = Math.min(...data);
        const max = Math.max(...data);
        const range = max - min || 1;

        // Draw line
        ctx.strokeStyle = '#3b82f6';
        ctx.lineWidth = 1.5;
        ctx.beginPath();

        data.forEach((value, index) => {
            const x = (index / (data.length - 1)) * width;
            const y = height - ((value - min) / range) * height;

            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });

        ctx.stroke();
    }

    // Initialize all charts
    function initializeCharts() {
        initializeSubscriptionChart();
    }

    // Initialize subscription distribution chart
    function initializeSubscriptionChart() {
        const ctx = document.getElementById('subscriptionChart');
        if (!ctx) return;

        // Get data from PHP variables
        const subscriptionData = {!! json_encode($subscriptionAnalytics ?? []) !!};
    const labels = subscriptionData.map(item => item.name);
    const data = subscriptionData.map(item => item.revenue);

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(139, 92, 246, 0.8)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // We'll use custom legend
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': GH₵' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Create custom legend
    createSubscriptionLegend(subscriptionData);
    }

    // Create custom legend for subscription chart
    function createSubscriptionLegend(data) {
        const legendContainer = document.getElementById('subscription-legend');
        if (!legendContainer) return;

        const total = data.reduce((sum, item) => sum + item.revenue, 0);

        legendContainer.innerHTML = data.map(item => {
            const percentage = ((item.revenue / total) * 100).toFixed(1);
            return `
                <div class="flex items-center justify-between py-1">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div>
                        <span class="text-sm font-medium text-gray-900">${item.name}</span>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold text-gray-900">GH₵${item.revenue.toLocaleString()}</div>
                        <div class="text-xs text-gray-500">${percentage}%</div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Setup event listeners
    function setupEventListeners() {
        // KPI card clicks
        document.querySelectorAll('.kpi-card').forEach(card => {
            card.addEventListener('click', function () {
                const target = this.getAttribute('data-target');
                if (target) {
                    const element = document.getElementById(target);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        // Add highlight effect
                        element.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.3)';
                        setTimeout(() => {
                            element.style.boxShadow = '';
                        }, 2000);
                    }
                }
            });
        });

        // Time filter buttons
        document.querySelectorAll('.time-filter').forEach(button => {
            button.addEventListener('click', function () {
                document.querySelectorAll('.time-filter').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                // In real app, this would filter the chart data
                console.log('Time filter changed to:', this.getAttribute('data-period'));
            });
        });

        // Metric toggle buttons
        document.querySelectorAll('.metric-toggle').forEach(button => {
            button.addEventListener('click', function () {
                document.querySelectorAll('.metric-toggle').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                // In real app, this would update the metrics display
                console.log('Metric view changed to:', this.getAttribute('data-metric'));
            });
        });

        // Table sorting
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', function () {
                const sortBy = this.getAttribute('data-sort');
                const table = document.getElementById('plan-performance-table');
                // In real app, this would sort the table
                console.log('Sorting by:', sortBy);
            });
        });

        // Export buttons
        document.querySelectorAll('.export-btn').forEach(button => {
            button.addEventListener('click', function () {
                // In real app, this would trigger export
                alert('Export functionality would be implemented here');
            });
        });
    }

    // Refactored Toggle Lock logic for reuse
    function handleToggleLock() {
        // Show loading state
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        this.disabled = true;

        fetch('{{ route("admin.toggle-lock") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                // Restore button state
                this.innerHTML = originalText;
                this.disabled = false;

                if (data.requires_recovery_codes) {
                    // Show recovery code warning modal
                    showRecoveryCodeWarning();
                    return;
                }

                const icon = this.querySelector('i');
                const textNodes = Array.from(this.childNodes).filter(node => node.nodeType === 3);
                const textNode = textNodes[textNodes.length - 1];

                if (data.locked) {
                    icon.className = 'fas fa-unlock mr-2';
                    textNode.textContent = ' Unlock Website';
                    this.classList.remove('bg-red-600');
                    this.classList.add('bg-green-600');
                } else {
                    icon.className = 'fas fa-lock mr-2';
                    textNode.textContent = ' Lock Website';
                    this.classList.remove('bg-green-600');
                    this.classList.add('bg-red-600');
                }

                alert(data.message);
            })
            .catch(error => {
                // Restore button state on error
                this.innerHTML = originalText;
                this.disabled = false;
                alert('An error occurred. Please try again.');
            });
    }

    // Function to show recovery code warning modal
    function showRecoveryCodeWarning() {
        // Create modal HTML
        const modalHTML = `
            <div id="recoveryCodeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="flex items-center justify-center mb-4">
                            <div class="bg-yellow-100 p-3 rounded-full">
                                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 text-center mb-2">Recovery Codes Required</h3>
                        <div class="mt-2 px-7 py-3">
                            <p class="text-sm text-gray-700 text-center">
                                You cannot lock the website without valid recovery codes for superusers.
                                Please generate recovery codes first to ensure you can unlock the website later.
                            </p>
                        </div>
                        <div class="flex items-center px-4 py-3 space-x-3">
                            <button id="generateCodesBtn" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i class="fas fa-key mr-2"></i>Generate Recovery Codes
                            </button>
                            <button id="cancelLockBtn" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add modal to page
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Handle generate codes button
        document.getElementById('generateCodesBtn').addEventListener('click', function () {
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generating...';
            this.disabled = true;

            fetch('{{ route("admin.credentials.recovery") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('recoveryCodeModal').remove();
                    alert('Recovery codes generated successfully! You can now lock the website.');
                    location.reload(); // Refresh to show updated codes
                })
                .catch(error => {
                    this.innerHTML = '<i class="fas fa-key mr-2"></i>Generate Recovery Codes';
                    this.disabled = false;
                    alert('Failed to generate recovery codes. Please try again.');
                });
        });

        // Handle cancel button
        document.getElementById('cancelLockBtn').addEventListener('click', function () {
            document.getElementById('recoveryCodeModal').remove();
        });
    }

    // Auto-refresh dashboard every 30 seconds for better real-time updates
    setInterval(function () {
        // Only auto-refresh if the page is visible and user hasn't interacted recently
        if (!document.hidden && !recentUserInteraction) {
            updateDashboardStats();
        }
    }, 30000); // 30 seconds

    // Track recent user interactions to avoid interrupting work
    let recentUserInteraction = false;
    let interactionTimeout;

    function markUserInteraction() {
        recentUserInteraction = true;
        clearTimeout(interactionTimeout);
        interactionTimeout = setTimeout(() => {
            recentUserInteraction = false;
        }, 10000); // Reset after 10 seconds of no interaction
    }

    // Track user interactions
    document.addEventListener('click', markUserInteraction);
    document.addEventListener('keydown', markUserInteraction);
    document.addEventListener('scroll', markUserInteraction);

    // Function to update dashboard stats without full page reload
    function updateDashboardStats() {
        fetch('{{ route("admin.dashboard.stats") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStatsDisplay(data.stats);
                    updateLastUpdatedTime();
                }
            })
            .catch(error => {
                console.log('Failed to update dashboard stats:', error);
            });
    }

    // Update the stats display with new data
    function updateStatsDisplay(stats) {
        // Update Total Users
        const totalUsersElement = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4.gap-6.mb-8 > div:nth-child(1) p.text-3xl.font-bold.text-gray-900');
        if (totalUsersElement) {
            totalUsersElement.textContent = stats.total_users.toLocaleString();
        }

        // Update New Today count
        const newTodayElement = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4.gap-6.mb-8 > div:nth-child(4) p.text-3xl.font-bold.text-gray-900');
        if (newTodayElement) {
            newTodayElement.textContent = stats.new_users_today.toLocaleString();
        }

        // Update Online Users
        const onlineUsersElement = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4.gap-6.mb-8 > div:nth-child(2) p.text-3xl.font-bold.text-gray-900');
        if (onlineUsersElement) {
            onlineUsersElement.textContent = stats.online_users.toLocaleString();

            // Update percentage
            const percentageElement = onlineUsersElement.closest('.bg-white.rounded-xl').querySelector('.text-sm.text-gray-500');
            if (percentageElement && percentageElement.textContent.includes('% of total')) {
                const percentage = stats.total_users > 0 ? ((stats.online_users / stats.total_users) * 100).toFixed(1) : 0;
                percentageElement.innerHTML = percentage + '% of total <i class="fas fa-wifi text-green-500 mr-1"></i> Active in last 5 minutes';
            }
        }

        // Update New Users This Week
        const newWeekElement = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4.gap-6.mb-8 > div:nth-child(1) p.text-sm.text-green-600');
        if (newWeekElement && newWeekElement.innerHTML.includes('this week')) {
            newWeekElement.innerHTML = '<i class="fas fa-arrow-up mr-1"></i>+' + stats.new_users_this_week + ' this week';
        }
    }

    // Update the "Last updated" timestamp
    function updateLastUpdatedTime() {
        const lastUpdatedElement = document.querySelector('.flex.items-center.space-x-4 .text-sm.text-gray-500');
        if (lastUpdatedElement) {
            const now = new Date();
            const formatted = now.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            lastUpdatedElement.textContent = 'Last updated: ' + formatted;
        }
    }

    // Real-time online users updates with Laravel Echo
    function updateOnlineUsersCount(count) {
        // Update the online users count
        const onlineUsersElement = document.querySelector('.text-3xl.font-bold.text-gray-900');
        const onlineUsersCard = onlineUsersElement.closest('.bg-white.rounded-xl');

        if (onlineUsersCard && onlineUsersCard.querySelector('.text-sm.font-medium.text-gray-600')) {
            const label = onlineUsersCard.querySelector('.text-sm.font-medium.text-gray-600');
            if (label.textContent.includes('Online Users')) {
                onlineUsersElement.textContent = count.toLocaleString();

                // Update percentage
                const percentageElement = onlineUsersCard.querySelector('.text-sm.text-gray-500');
                if (percentageElement && percentageElement.textContent.includes('% of total')) {
                    const totalUsers = parseInt(onlineUsersCard.getAttribute('data-total-users')) || 0;
                    const percentage = totalUsers > 0 ? ((count / totalUsers) * 100).toFixed(1) : 0;
                    percentageElement.innerHTML = percentage + '% of total <i class="fas fa-wifi text-green-500 mr-1"></i> Active in last 5 minutes';
                }
            }
        }
    }

    // Get initial online users count
    function fetchOnlineUsersCount() {
        fetch('{{ route("online-users") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateOnlineUsersCount(data.count);
                }
            })
            .catch(error => {
                console.log('Failed to fetch online users count:', error);
            });
    }

    // Listen for real-time user online events
    if (window.Echo) {
        window.Echo.channel('online-users')
            .listen('.user.came-online', (e) => {
                console.log('User came online:', e.user);
                // Fetch updated count when a user comes online
                fetchOnlineUsersCount();
            });
    }

    // Fallback: Update online users count every 30 seconds (in case WebSocket fails)
    setInterval(fetchOnlineUsersCount, 30000);

    // Initial update
    setTimeout(fetchOnlineUsersCount, 2000);
</script>

@if(auth()->check() && (auth()->user()->hasRole('super-admin') || auth()->user()->is_superuser))
@if(!\Illuminate\Support\Facades\Cache::has('admin_invite_notice_seen_' . auth()->id()))
<!-- Invite Admin Feature Modal -->
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .modal-card-bg {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 1rem;
        overflow: hidden;
        z-index: -1;
    }

    .modal-card-bg::before {
        content: '';
        position: absolute;
        z-index: -1;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: conic-gradient(transparent,
                #ef4444,
                transparent 30%,
                #3b82f6,
                transparent 50%);
        animation: rotate-border-modal 8s linear infinite;
    }

    .modal-card-bg::after {
        content: '';
        position: absolute;
        z-index: -1;
        inset: 3px;
        background: white;
        border-radius: calc(1rem - 3px);
    }

    @keyframes rotate-border-modal {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }
</style>
<div id="inviteAdminNoticeModal" class="fixed inset-0 z-[100] flex items-center justify-center hidden"
    style="backdrop-filter: blur(4px);">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-gray-900 bg-opacity-40 transition-opacity" onclick="dismissInviteAdminNotice()">
    </div>

    <!-- Modal Content -->
    <div class="relative rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all scale-95 opacity-0 duration-300 p-[3px]"
        id="inviteAdminNoticeContent">

        <div class="modal-card-bg"></div>

        <div class="p-8 bg-white rounded-[calc(1rem-3px)] relative z-10">
            <div
                class="flex items-center justify-center w-16 h-16 mx-auto bg-indigo-50 text-indigo-600 rounded-full mb-6 ring-8 ring-indigo-50/50">
                <i class="fas fa-user-plus text-2xl"></i>
            </div>

            <h3 class="text-2xl font-bold text-center text-gray-900 mb-2">New Feature Update!</h3>
            <p class="text-center text-gray-600 mb-8 leading-relaxed">
                You can now invite other administrators and assign them restricted roles to collaboratively manage the
                learning platform.
            </p>

            <div class="flex flex-col space-y-3">
                <a href="{{ route('admin.users') }}" onclick="dismissInviteAdminNotice()"
                    class="w-full flex justify-center items-center px-4 py-3 border border-transparent text-sm font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm transition-all">
                    <i class="fas fa-users mr-2"></i> Try it out now
                </a>
                <button type="button" onclick="dismissInviteAdminNotice()"
                    class="w-full flex justify-center items-center px-4 py-3 border border-gray-300 text-sm font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                    Maybe Later
                </button>
            </div>
        </div>

        <!-- Close Button (X) -->
        <button type="button" onclick="dismissInviteAdminNotice()"
            class="absolute top-4 right-4 z-20 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('inviteAdminNoticeModal');
        const content = document.getElementById('inviteAdminNoticeContent');

        // Short delay for animation effect
        setTimeout(() => {
            if (modal) {
                modal.classList.remove('hidden');
                // Trigger reflow
                void modal.offsetWidth;
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }
        }, 500);
    });

    function dismissInviteAdminNotice() {
        const modal = document.getElementById('inviteAdminNoticeModal');
        const content = document.getElementById('inviteAdminNoticeContent');

        // Animation out
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);

        // Mark as seen in backend
        fetch('{{ route("admin.mark-invite-notice-seen") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
            .catch(error => console.error('Error marking notice as seen:', error));
    }
</script>
@endif
@endif
@endsection