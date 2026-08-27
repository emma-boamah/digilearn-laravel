@extends('layouts.admin')

@section('title', 'Revenue Analytics')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Revenue & Payments Analytics</h1>
                    <p class="text-gray-600">Track subscription revenue and payment transactions</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Period / Year Filter Selector -->
                    <form method="GET" action="{{ route('admin.revenue') }}" id="periodFilterForm" class="flex items-center space-x-2">
                        <input type="hidden" name="tab" value="{{ $activeTab }}">
                        <label for="yearSelector" class="text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:inline">Period:</label>
                        <div class="relative">
                            <select id="yearSelector" name="year" onchange="this.form.submit()" class="appearance-none bg-white border border-gray-200 text-gray-800 text-xs font-semibold rounded-lg pl-3 pr-8 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm cursor-pointer hover:border-gray-300 transition-colors">
                                <option value="{{ now()->year }}" {{ $selectedYear == now()->year ? 'selected' : '' }}>
                                    Year {{ now()->year }} (Current)
                                </option>
                                @foreach($availableYears as $yr)
                                    @if($yr != now()->year)
                                        <option value="{{ $yr }}" {{ $selectedYear == (string)$yr ? 'selected' : '' }}>
                                            Year {{ $yr }}
                                        </option>
                                    @endif
                                @endforeach
                                <option value="rolling_12" {{ $selectedYear === 'rolling_12' ? 'selected' : '' }}>
                                    Rolling 12 Months
                                </option>
                                <option value="all" {{ $selectedYear === 'all' ? 'selected' : '' }}>
                                    All-Time (Lifetime)
                                </option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                    </form>

                    @if($activeTab === 'revenue')
                        <a href="{{ route('admin.revenue.export-trends') }}" class="bg-blue-600 text-white px-3.5 py-2 text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center shadow-sm">
                            <i class="fas fa-download mr-1.5"></i> Export Trends
                        </a>
                    @elseif($activeTab === 'payments')
                        <a href="{{ route('admin.revenue.export-payments', ['format' => 'csv']) }}" class="bg-blue-600 text-white px-3.5 py-2 text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center shadow-sm">
                            <i class="fas fa-download mr-1.5"></i> Export Payments
                        </a>
                    @endif
                    <button onclick="location.reload()" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-3.5 py-2 text-xs font-semibold rounded-lg transition-colors shadow-sm inline-flex items-center">
                        <i class="fas fa-sync-alt mr-1.5"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-2">
                    <a href="{{ route('admin.revenue', ['tab' => 'revenue', 'year' => $selectedYear]) }}"
                       class="group relative py-4 px-6 font-semibold text-sm rounded-t-lg transition-all duration-200 ease-in-out cursor-pointer
                       {{ $activeTab === 'revenue'
                           ? 'bg-blue-50 text-blue-700 border-b-2 border-blue-500 shadow-sm'
                           : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent hover:border-gray-300' }}">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-chart-line {{ $activeTab === 'revenue' ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                            <span>Revenue Analytics</span>
                        </div>
                        @if($activeTab === 'revenue')
                            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-blue-500 to-blue-600 rounded-t"></div>
                        @endif
                    </a>
                    <a href="{{ route('admin.revenue', ['tab' => 'payments', 'year' => $selectedYear]) }}"
                       class="group relative py-4 px-6 font-semibold text-sm rounded-t-lg transition-all duration-200 ease-in-out cursor-pointer
                       {{ $activeTab === 'payments'
                           ? 'bg-green-50 text-green-700 border-b-2 border-green-500 shadow-sm'
                           : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent hover:border-gray-300' }}">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-credit-card {{ $activeTab === 'payments' ? 'text-green-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                            <span>Payments Analytics</span>
                        </div>
                        @if($activeTab === 'payments')
                            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-green-500 to-green-600 rounded-t"></div>
                        @endif
                    </a>
                    <a href="{{ route('admin.revenue', ['tab' => 'summary', 'year' => $selectedYear]) }}"
                       class="group relative py-4 px-6 font-semibold text-sm rounded-t-lg transition-all duration-200 ease-in-out cursor-pointer
                       {{ $activeTab === 'summary'
                           ? 'bg-purple-50 text-purple-700 border-b-2 border-purple-500 shadow-sm'
                           : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-b-2 border-transparent hover:border-gray-300' }}">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-file-invoice-dollar {{ $activeTab === 'summary' ? 'text-purple-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                            <span>Summary Reports</span>
                        </div>
                        @if($activeTab === 'summary')
                            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-purple-500 to-purple-600 rounded-t"></div>
                        @endif
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($activeTab === 'revenue')
        <!-- Revenue Stats Grid (3 Clean Cards) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- 1. Revenue Overview -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">
                            {{ is_numeric($selectedYear) ? 'Year ' . $selectedYear . ' Revenue' : ($selectedYear === 'rolling_12' ? 'Rolling 12M Revenue' : 'Gross Revenue') }}
                        </p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">GH₵{{ number_format($revenueData['total_revenue'], 2) }}</p>
                        <div class="flex items-center flex-wrap gap-2 mt-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold {{ $revenueData['revenue_growth'] >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                <i class="fas {{ $revenueData['revenue_growth'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1 text-[10px]"></i>
                                {{ $revenueData['revenue_growth'] >= 0 ? '+' : '' }}{{ $revenueData['revenue_growth'] }}%
                            </span>
                            <span class="text-gray-400 text-xs">•</span>
                            <span class="text-gray-500 text-xs font-medium">{{ $revenueData['growth_label'] }}</span>
                            <span class="text-gray-400 text-xs">•</span>
                            <span class="text-gray-500 text-xs font-medium">{{ number_format($revenueData['lifetime_transactions']) }} paid orders</span>
                        </div>
                    </div>
                    <div class="bg-blue-50 p-3.5 rounded-xl text-blue-600 shadow-sm ml-3">
                        <i class="fas fa-coins text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- 2. Active Subscriptions -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Active Subscriptions</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($revenueData['active_subscriptions']) }}</p>
                        <div class="flex items-center flex-wrap gap-2 mt-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-blue-50 text-blue-700">
                                <i class="fas fa-user-plus mr-1 text-[10px]"></i>
                                +{{ $revenueData['new_subscriptions_today'] }} today
                            </span>
                            <span class="text-gray-400 text-xs">•</span>
                            <span class="text-gray-500 text-xs font-medium">{{ count($subscriptionAnalytics) }} active tiers</span>
                            <span class="text-gray-400 text-xs">•</span>
                            <span class="text-gray-500 text-xs font-medium">Monthly recurring</span>
                        </div>
                    </div>
                    <div class="bg-blue-50 p-3.5 rounded-xl text-blue-600 shadow-sm ml-3">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- 3. Customer Economics (ARPU & Churn) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Avg Per Paying User (ARPU)</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">GH₵{{ number_format($revenueData['average_revenue_per_user'], 2) }}</p>
                        <div class="flex items-center flex-wrap gap-2 mt-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-gray-100 text-gray-700">
                                <i class="fas fa-shield-alt mr-1 text-[10px] text-blue-600"></i>
                                {{ $revenueData['churn_rate'] }}% churn
                            </span>
                            <span class="text-gray-400 text-xs">•</span>
                            <span class="text-gray-500 text-xs font-medium">{{ $revenueData['lifetime_success_rate'] }}% checkout success</span>
                        </div>
                    </div>
                    <div class="bg-blue-50 p-3.5 rounded-xl text-blue-600 shadow-sm ml-3">
                        <i class="fas fa-user-check text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Trend Chart (Full Width) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 mb-8">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-chart-area text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">
                                Revenue Trend
                                <span class="text-sm font-semibold text-blue-600 ml-1">
                                    ({{ is_numeric($selectedYear) ? 'Year ' . $selectedYear : ($selectedYear === 'rolling_12' ? 'Rolling 12M' : 'Lifetime') }})
                                </span>
                            </h2>
                            <p class="text-xs text-gray-500">Earnings and active subscriptions trajectory</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 bg-gray-100 p-1 rounded-xl border border-gray-200">
                        <a href="{{ route('admin.revenue', ['tab' => 'revenue', 'year' => now()->year]) }}"
                           class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $selectedYear == now()->year ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                            {{ now()->year }}
                        </a>
                        @foreach($availableYears as $yr)
                            @if($yr != now()->year)
                                <a href="{{ route('admin.revenue', ['tab' => 'revenue', 'year' => $yr]) }}"
                                   class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $selectedYear == (string)$yr ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                    {{ $yr }}
                                </a>
                            @endif
                        @endforeach
                        <a href="{{ route('admin.revenue', ['tab' => 'revenue', 'year' => 'rolling_12']) }}"
                           class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $selectedYear === 'rolling_12' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                            Rolling 12M
                        </a>
                        <a href="{{ route('admin.revenue', ['tab' => 'revenue', 'year' => 'all']) }}"
                           class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $selectedYear === 'all' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                            Lifetime
                        </a>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl p-4">
                    <div id="revenueChart" style="min-height: 400px; width: 100%;"></div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Subscription Distribution -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Subscription Distribution</h2>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-users text-cyan-500"></i>
                            <span class="text-sm text-gray-600">By Plan Type</span>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex flex-col items-center">
                        <div class="relative mb-6">
                            <canvas id="subscriptionChart" width="280" height="280" class="max-w-full h-auto"></canvas>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <div class="text-lg font-bold text-gray-900">{{ count($subscriptionAnalytics) }}</div>
                                    <div class="text-sm text-gray-500">Active Plans</div>
                                </div>
                            </div>
                        </div>
                        <div class="w-full space-y-3">
                            @foreach($subscriptionAnalytics as $index => $plan)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ ['#3b82f6', '#10b981', '#8b5cf6'][(int)$index % 3] }}"></div>
                                    <span class="text-sm font-medium text-gray-900">{{ $plan['name'] }}</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-gray-900">{{ $plan['percentage'] }}%</div>
                                    <div class="text-xs text-gray-600">GH₵{{ number_format($plan['revenue'], 0) }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Growth Metrics -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Growth Metrics</h2>
                        <div class="flex space-x-2">
                            <button onclick="setGrowthPeriod('daily')" class="px-4 py-2 text-sm font-medium bg-white text-gray-700 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-colors">Daily</button>
                            <button onclick="setGrowthPeriod('weekly')" class="px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg border border-blue-600 hover:bg-blue-700 transition-colors">Weekly</button>
                            <button onclick="setGrowthPeriod('monthly')" class="px-4 py-2 text-sm font-medium bg-white text-gray-700 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-colors">Monthly</button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700" id="growthLabel">Monthly Growth</span>
                                <span class="text-lg font-bold text-green-600" id="growthValue">+{{ $revenueData['revenue_growth'] }}%</span>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700" id="revenueLabel">Weekly Revenue</span>
                                <span class="text-lg font-bold text-blue-600" id="revenueValue">GH₵{{ number_format($revenueData['weekly_revenue'], 2) }}</span>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700">Daily Revenue</span>
                                <span class="text-lg font-bold text-purple-600">GH₵{{ number_format($revenueData['daily_revenue'], 2) }}</span>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-xl p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700">Conversion Rate</span>
                                <span class="text-lg font-bold text-orange-600">3.2%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plan Performance Table (Full Width) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 mb-8">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-trophy text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Plan Performance</h2>
                        <p class="text-sm text-gray-600">Revenue and subscriber metrics by plan</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="planPerformanceTable" class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors" onclick="sortTable(0)">Plan</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors" onclick="sortTable(1)">Subscribers</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors" onclick="sortTable(2)">Revenue</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors" onclick="sortTable(3)">Growth</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors" onclick="sortTable(4)">Churn</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @foreach($subscriptionAnalytics as $key => $plan)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold text-lg">
                                        {{ substr($plan['name'], 0, 1) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $plan['name'] }}</div>
                                        <div class="text-xs text-gray-500">Active plan</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $plan['subscribers'] }}</div>
                                <div class="text-xs text-gray-500">subscribers</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">GH₵{{ number_format($plan['revenue'], 2) }}</div>
                                <div class="text-xs text-gray-500">total revenue</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    <i class="fas fa-arrow-up mr-1"></i>
                                    +{{ $plan['percentage'] }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                    <i class="fas fa-arrow-down mr-1"></i>
                                    2.1%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Additional Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Top Performing Plans -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-star text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Top Performers</h2>
                            <p class="text-sm text-gray-600">Highest revenue generating plans</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($topPlans as $index => $plan)
                        <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-xl p-4 border border-amber-100 hover:shadow-sm transition-shadow duration-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center text-white text-lg font-bold shadow-lg">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 text-sm">{{ $plan['plan'] }}</p>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                            <p class="text-xs text-green-700 font-medium">+{{ $plan['growth'] }}% growth</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900 text-lg">GH₵{{ number_format($plan['revenue'], 0) }}</p>
                                    <p class="text-xs text-gray-600">total revenue</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-slate-500 to-slate-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-bolt text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Quick Actions</h2>
                            <p class="text-sm text-gray-600">Common administrative tasks</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <button onclick="generateReport()" class="w-full flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 font-medium shadow-sm hover:shadow-md">
                            <i class="fas fa-file-alt mr-3"></i>
                            <span>Generate Report</span>
                        </button>
                        <button onclick="viewTransactions()" class="w-full flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition-all duration-200 font-medium shadow-sm hover:shadow-md">
                            <i class="fas fa-list mr-3"></i>
                            <span>View Transactions</span>
                        </button>
                        <button onclick="managePlans()" class="w-full flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl hover:from-purple-600 hover:to-purple-700 transition-all duration-200 font-medium shadow-sm hover:shadow-md">
                            <i class="fas fa-cog mr-3"></i>
                            <span>Manage Plans</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($activeTab === 'payments' && $paymentAnalytics)
        <!-- Payment Stats Grid (3 Clean Cards) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- 1. Processed Payment Volume -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">
                            {{ is_numeric($selectedYear) ? 'Year ' . $selectedYear . ' Volume' : ($selectedYear === 'rolling_12' ? 'Rolling 12M Volume' : 'Total Processed Volume') }}
                        </p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">GH₵{{ number_format($paymentAnalytics['total_value'], 2) }}</p>
                        <div class="flex items-center flex-wrap gap-2 mt-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-blue-50 text-blue-700">
                                <i class="fas fa-check-double mr-1 text-[10px]"></i>
                                {{ number_format($paymentAnalytics['successful_payments']) }} completed
                            </span>
                            <span class="text-gray-400 text-xs">•</span>
                            <span class="text-gray-500 text-xs font-medium">{{ number_format($paymentAnalytics['total_payments']) }} total attempts</span>
                        </div>
                    </div>
                    <div class="bg-blue-50 p-3.5 rounded-xl text-blue-600 shadow-sm ml-3">
                        <i class="fas fa-coins text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- 2. Gateway Success Rate -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Transaction Success Rate</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $paymentAnalytics['success_rate'] }}%</p>
                        <div class="flex items-center flex-wrap gap-2 mt-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold {{ $paymentAnalytics['success_rate'] >= 80 ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700' }}">
                                <i class="fas {{ $paymentAnalytics['success_rate'] >= 80 ? 'fa-check-circle' : 'fa-exclamation-triangle' }} mr-1 text-[10px]"></i>
                                {{ $paymentAnalytics['successful_payments'] }} passed
                            </span>
                            <span class="text-gray-400 text-xs">•</span>
                            <span class="text-gray-500 text-xs font-medium">{{ $paymentAnalytics['status_distribution']['failed'] ?? 0 }} failed</span>
                            <span class="text-gray-400 text-xs">•</span>
                            <span class="text-gray-500 text-xs font-medium">{{ $paymentAnalytics['status_distribution']['pending'] ?? 0 }} pending</span>
                        </div>
                    </div>
                    <div class="bg-blue-50 p-3.5 rounded-xl text-blue-600 shadow-sm ml-3">
                        <i class="fas fa-shield-alt text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- 3. Average Ticket Size -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Average Order Amount</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">GH₵{{ number_format($paymentAnalytics['average_amount'], 2) }}</p>
                        <div class="flex items-center flex-wrap gap-2 mt-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-gray-100 text-gray-700">
                                <i class="fas fa-credit-card mr-1 text-[10px] text-blue-600"></i>
                                Per successful order
                            </span>
                            <span class="text-gray-400 text-xs">•</span>
                            <span class="text-gray-500 text-xs font-medium">Paystack Gateway</span>
                        </div>
                    </div>
                    <div class="bg-blue-50 p-3.5 rounded-xl text-blue-600 shadow-sm ml-3">
                        <i class="fas fa-receipt text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Payment Status Distribution -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Payment Status</h2>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-600">Live Data</span>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex flex-col items-center">
                        <div class="relative mb-6">
                            <canvas id="paymentStatusChart" width="280" height="280" class="max-w-full h-auto"></canvas>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ $paymentAnalytics['total_payments'] }}</div>
                                    <div class="text-sm text-gray-500">Total Payments</div>
                                </div>
                            </div>
                        </div>
                        <div class="w-full space-y-3">
                            <div class="flex items-center justify-between p-3 rounded-xl bg-green-50 border border-green-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                    <span class="text-sm font-medium text-gray-900">Successful</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-gray-900">{{ $paymentAnalytics['status_distribution']['success'] ?? 0 }}</div>
                                    <div class="text-xs text-green-600 font-medium">{{ $paymentAnalytics['total_payments'] > 0 ? round((($paymentAnalytics['status_distribution']['success'] ?? 0) / $paymentAnalytics['total_payments']) * 100, 1) : 0 }}%</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-xl bg-yellow-50 border border-yellow-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                    <span class="text-sm font-medium text-gray-900">Pending</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-gray-900">{{ $paymentAnalytics['status_distribution']['pending'] ?? 0 }}</div>
                                    <div class="text-xs text-yellow-600 font-medium">{{ $paymentAnalytics['total_payments'] > 0 ? round((($paymentAnalytics['status_distribution']['pending'] ?? 0) / $paymentAnalytics['total_payments']) * 100, 1) : 0 }}%</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-xl bg-red-50 border border-red-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                    <span class="text-sm font-medium text-gray-900">Failed</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-gray-900">{{ $paymentAnalytics['status_distribution']['failed'] ?? 0 }}</div>
                                    <div class="text-xs text-red-600 font-medium">{{ $paymentAnalytics['total_payments'] > 0 ? round((($paymentAnalytics['status_distribution']['failed'] ?? 0) / $paymentAnalytics['total_payments']) * 100, 1) : 0 }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Trends -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Payment Volume Trends</h2>
                            <p class="text-xs text-gray-500">Monthly transaction and revenue progression</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5 bg-gray-100 p-1 rounded-xl border border-gray-200">
                            <a href="{{ route('admin.revenue', ['tab' => 'payments', 'year' => now()->year]) }}"
                               class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all {{ $selectedYear == now()->year ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                {{ now()->year }}
                            </a>
                            @foreach($availableYears as $yr)
                                @if($yr != now()->year)
                                    <a href="{{ route('admin.revenue', ['tab' => 'payments', 'year' => $yr]) }}"
                                       class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all {{ $selectedYear == (string)$yr ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                        {{ $yr }}
                                    </a>
                                @endif
                            @endforeach
                            <a href="{{ route('admin.revenue', ['tab' => 'payments', 'year' => 'rolling_12']) }}"
                               class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all {{ $selectedYear === 'rolling_12' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                12M
                            </a>
                            <a href="{{ route('admin.revenue', ['tab' => 'payments', 'year' => 'all']) }}"
                               class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all {{ $selectedYear === 'all' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                Lifetime
                            </a>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 mb-4">
                        <canvas id="paymentTrendsChart" style="height: 280px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Metadata Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Duration Distribution -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Payment Durations</h2>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-clock text-purple-500"></i>
                            <span class="text-sm text-gray-600">By Plan Length</span>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4">
                        <canvas id="durationChart" style="height: 240px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Plan Distribution -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Plan Distribution</h2>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-chart-pie text-green-500"></i>
                            <span class="text-sm text-gray-600">By Plan Type</span>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4">
                        <canvas id="planChart" style="height: 240px; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 mb-8">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-receipt text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Recent Payments</h2>
                            <p class="text-sm text-gray-600">Latest payment transactions</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="relative">
                            <select id="statusFilter" class="appearance-none bg-white border border-gray-200 rounded-lg px-4 py-2 pr-8 text-sm font-medium text-gray-700 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Status</option>
                                <option value="success">Successful</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.payments.sync-all-pending') }}" class="inline" onsubmit="return confirm('This will check all pending payments against Paystack and activate valid subscriptions. Proceed?')">
                            @csrf
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2 shadow-sm">
                                <i class="fas fa-sync-alt"></i>
                                <span>Sync Pending</span>
                            </button>
                        </form>
                        <button onclick="exportPaymentsData()" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-2 text-sm font-medium rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-download"></i>
                            <span>Export</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="paymentsTable" class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Plan</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @foreach($paymentAnalytics['recent_payments'] as $payment)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-lg overflow-hidden flex items-center justify-center bg-gray-100">
                                        @if($payment['user_avatar_url'])
                                            <img src="{{ $payment['user_avatar_url'] }}" alt="{{ $payment['user_name'] }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-gray-400 to-gray-500 flex items-center justify-center text-white text-sm font-medium">
                                                {{ $payment['user_initials'] }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $payment['user_name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $payment['user_email'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $payment['plan_name'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">GH₵{{ number_format($payment['amount'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                        @if($payment['status'] === 'success') bg-green-100 text-green-800
                                         @elseif($payment['status'] === 'pending') bg-yellow-100 text-yellow-800
                                         @else bg-red-100 text-red-800 @endif">
                                        <i class="fas @if($payment['status'] === 'success') fa-check-circle @elseif($payment['status'] === 'pending') fa-clock @else fa-times-circle @endif mr-1"></i>
                                        {{ ucfirst($payment['status']) }}
                                    </span>
                                    @if(!empty($payment['verified_via_sync']))
                                        <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 inline-flex items-center" title="Verified via Paystack Sync">
                                            <i class="fas fa-check-double mr-0.5"></i> Synced
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment['duration'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $payment['created_at'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex items-center space-x-2">
                                    <button type="button" onclick='openPaymentModal(@json($payment))' class="inline-flex items-center px-2.5 py-1.5 border border-gray-200 text-xs font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm" title="View Transaction Details">
                                        <i class="fas fa-info-circle mr-1 text-gray-500"></i> Details
                                    </button>
                                    @if($payment['status'] === 'pending')
                                    <form method="POST" action="{{ route('admin.payments.verify', $payment['id']) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none shadow-sm transition-colors" title="Verify with Paystack">
                                            <i class="fas fa-sync-alt mr-1"></i> Verify
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(isset($paymentAnalytics['recent_payments']) && method_exists($paymentAnalytics['recent_payments'], 'links') && $paymentAnalytics['recent_payments']->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $paymentAnalytics['recent_payments']->links() }}
                </div>
            @endif
        </div>

        <!-- Top Paying Users -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-trophy text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Top Paying Users</h2>
                        <p class="text-sm text-gray-600">Highest value customers</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($paymentAnalytics['top_users'] as $index => $user)
                    <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl p-4 border border-gray-100 hover:shadow-sm transition-shadow duration-200">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl overflow-hidden flex items-center justify-center bg-gray-100 relative">
                                        @if($user['user_avatar_url'])
                                            <img src="{{ $user['user_avatar_url'] }}" alt="{{ $user['user_name'] }}" class="w-full h-full object-cover">
                                            <div class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg flex items-center justify-center text-white text-[10px] font-bold border-2 border-white shadow-sm">
                                                {{ $index + 1 }}
                                            </div>
                                        @else
                                            <div class="w-full h-full bg-gradient-to-r from-yellow-400 to-orange-500 flex items-center justify-center text-white text-lg font-bold">
                                                {{ $index + 1 }}
                                            </div>
                                        @endif
                                    </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-gray-900">GH₵{{ number_format($user['total_amount'], 0) }}</div>
                                <div class="text-xs text-gray-500">{{ $user['payment_count'] }} payments</div>
                            </div>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ $user['user_name'] }}</p>
                            <p class="text-xs text-gray-600 truncate">{{ $user['user_email'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if($activeTab === 'summary' && $summaryReports)
        <div class="space-y-8">
            <!-- Annual Summary -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center text-white">
                                <i class="fas fa-calendar text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Annual Revenue Summary</h2>
                                <p class="text-sm text-gray-600">Year-over-year performance</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.revenue.export', ['type' => 'annual']) }}" class="inline-flex items-center px-4 py-2 bg-white border border-purple-200 text-purple-700 text-sm font-bold rounded-xl hover:bg-purple-50 transition-all shadow-sm">
                            <i class="fas fa-download mr-2"></i>
                            Export CSV
                        </a>
                    </div>
                </div>
                <!-- Annual Trend Chart -->
                <div class="p-6 bg-gray-50/50 border-b border-gray-100">
                    <div id="annualSummaryChart" style="min-height: 200px;"></div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Year</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Revenue</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payments</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">New Subscriptions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            @foreach($summaryReports['annual'] as $report)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $report->period_date ? $report->period_date->format('Y') : 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">GH₵{{ number_format($report->revenue, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($report->payments_count) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($report->subscriptions_count) }}</td>
                            </tr>
                            @endforeach
                            @if($summaryReports['annual']->isEmpty())
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">No annual data aggregated yet.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @if($summaryReports['annual']->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $summaryReports['annual']->links() }}
                </div>
                @endif
            </div>

            <!-- Monthly Summary -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center text-white">
                                <i class="fas fa-calendar-alt text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Monthly Revenue Summary</h2>
                                <p class="text-sm text-gray-600">Month-by-month performance</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.revenue.export', ['type' => 'monthly']) }}" class="inline-flex items-center px-4 py-2 bg-white border border-blue-200 text-blue-700 text-sm font-bold rounded-xl hover:bg-blue-50 transition-all shadow-sm">
                            <i class="fas fa-download mr-2"></i>
                            Export CSV
                        </a>
                    </div>
                </div>
                <!-- Monthly Trend Chart -->
                <div class="p-6 bg-gray-50/50 border-b border-gray-100">
                    <div id="monthlySummaryChart" style="min-height: 250px;"></div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Month</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Revenue</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payments</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">New Subscriptions</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Top Plan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            @foreach($summaryReports['monthly'] as $report)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $report->period_date ? $report->period_date->format('M Y') : 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">GH₵{{ number_format($report->revenue, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($report->payments_count) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($report->subscriptions_count) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    @php
                                        $breakdown = $report->metadata['plan_breakdown'] ?? [];
                                        if (!empty($breakdown)) {
                                            arsort($breakdown);
                                            echo array_key_first($breakdown);
                                        } else {
                                            echo 'N/A';
                                        }
                                    @endphp
                                </td>
                            </tr>
                            @endforeach
                            @if($summaryReports['monthly']->isEmpty())
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">No monthly data aggregated yet.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @if($summaryReports['monthly']->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $summaryReports['monthly']->links() }}
                </div>
                @endif
            </div>

            <!-- Weekly Summary -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-green-50 to-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center text-white">
                                <i class="fas fa-layer-group text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Weekly Revenue Summary</h2>
                                <p class="text-sm text-gray-600">Week-by-week performance</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.revenue.export', ['type' => 'weekly']) }}" class="inline-flex items-center px-4 py-2 bg-white border border-green-200 text-green-700 text-sm font-bold rounded-xl hover:bg-green-50 transition-all shadow-sm">
                            <i class="fas fa-download mr-2"></i>
                            Export CSV
                        </a>
                    </div>
                </div>
                <!-- Weekly Trend Chart -->
                <div class="p-6 bg-gray-50/50 border-b border-gray-100">
                    <div id="weeklySummaryChart" style="min-height: 250px;"></div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Week Starting</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Revenue</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payments</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">New Subscriptions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            @foreach($summaryReports['weekly'] as $report)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $report->period_date ? $report->period_date->format('M d, Y') : 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">GH₵{{ number_format($report->revenue, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($report->payments_count) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($report->subscriptions_count) }}</td>
                            </tr>
                            @endforeach
                            @if($summaryReports['weekly']->isEmpty())
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">No weekly data aggregated yet.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @if($summaryReports['weekly']->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $summaryReports['weekly']->links() }}
                </div>
                @endif
            </div>
            
            <!-- Quick Catchup Action -->
            <div class="flex flex-col items-center py-4 pb-8">
                <form id="recalculateForm" action="{{ route('admin.revenue.aggregate') }}" method="POST">
                    @csrf
                    <button id="recalculateBtn" type="submit" class="group relative inline-flex items-center px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-lg hover:shadow-indigo-200 active:scale-95 overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <i class="fas fa-sync-alt mr-2 group-hover:rotate-180 transition-transform duration-500"></i>
                        <span>Recalculate All Summaries (Historical)</span>
                    </button>
                </form>
                
                <div id="recalculateLoading" class="hidden mt-6 text-center animate-pulse">
                    <div class="inline-flex items-center px-6 py-3 bg-indigo-50 text-indigo-700 rounded-2xl border border-indigo-100 shadow-sm">
                        <div class="flex flex-col items-center">
                            <div class="flex items-center mb-1">
                                <i class="fas fa-history mr-2 text-indigo-500"></i>
                                <span class="font-bold italic">Processing historical data...</span>
                            </div>
                            <span class="text-xs text-indigo-600 opaicty-75">This may take up to 30 seconds. Please relax and do not refresh the page.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Transaction Details Modal -->
<div id="paymentDetailsModal" class="fixed inset-0 z-50 overflow-y-auto hidden items-center justify-center p-4" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop: Translucent so revenue page remains clearly visible -->
    <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="closePaymentModal()"></div>
    
    <!-- Modal Card -->
    <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-lg border border-gray-100 z-10 my-8">
        <div class="bg-gradient-to-r from-gray-50 to-white px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg shadow-sm">
                    <i class="fas fa-receipt"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Transaction Details</h3>
                    <p class="text-xs text-gray-500 font-mono" id="modalRefText">PAY-xxxx</p>
                </div>
            </div>
            <button type="button" onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>
        
        <div class="p-6 space-y-4">
            <!-- Status & Gateway Response Banner -->
            <div id="modalStatusBanner" class="p-4 rounded-xl border flex items-start space-x-3">
                <div id="modalStatusIcon" class="mt-0.5 text-lg"></div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <span id="modalStatusBadge" class="font-bold text-sm"></span>
                        <span id="modalSyncBadge" class="hidden text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800"><i class="fas fa-check-double mr-1"></i>Synced via Paystack</span>
                    </div>
                    <p id="modalGatewayResponse" class="text-xs mt-1 font-medium"></p>
                </div>
            </div>

            <!-- Customer Details -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 space-y-2">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Customer</div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-gray-900" id="modalUserName"></p>
                        <p class="text-xs text-gray-500" id="modalUserEmail"></p>
                        <p class="text-xs text-gray-500" id="modalUserPhone"></p>
                    </div>
                    <a id="modalUserLink" href="#" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-100 rounded-lg hover:bg-blue-100 transition-colors">
                        <i class="fas fa-user mr-1.5"></i> Profile
                    </a>
                </div>
            </div>

            <!-- Payment Info Grid -->
            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <span class="text-xs text-gray-500 font-medium">Plan & Duration</span>
                    <p class="text-sm font-bold text-gray-900 mt-0.5" id="modalPlanName"></p>
                    <span class="text-xs text-gray-500 font-medium" id="modalDuration"></span>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <span class="text-xs text-gray-500 font-medium">Amount & Provider</span>
                    <p class="text-sm font-bold text-gray-900 mt-0.5" id="modalAmount"></p>
                    <span class="text-xs text-gray-500 font-medium" id="modalProvider"></span>
                </div>
            </div>

            <!-- Technical Audit Details -->
            <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100 space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-500">Paystack Transaction ID:</span>
                    <span class="font-mono font-semibold text-gray-800" id="modalTransactionId"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Attempted Date:</span>
                    <span class="text-gray-800 font-medium" id="modalCreatedAt"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Paid/Completed Date:</span>
                    <span class="text-gray-800 font-medium" id="modalPaidAt"></span>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <button type="button" onclick="closePaymentModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                Close
            </button>
            <div id="modalVerifyAction">
                <form id="modalVerifyForm" method="POST" action="" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-sm flex items-center space-x-1.5 transition-colors">
                        <i class="fas fa-sync-alt"></i>
                        <span>Re-Verify with Paystack</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    @if($activeTab === 'revenue')
    // ApexCharts Modern Revenue Trend
    const trends = @json($revenueTrends);
    const revenueDataArr = trends.map(t => t.revenue);
    const labels = trends.map(t => t.month);
    
    const revenueOptions = {
        series: [{
            name: 'Revenue',
            data: revenueDataArr
        }],
        chart: {
            type: 'area',
            height: 400,
            toolbar: { show: false },
            zoom: { enabled: false },
            fontFamily: 'Inter, sans-serif',
            dropShadow: {
                enabled: true,
                top: 10,
                left: 0,
                blur: 3,
                color: '#2563eb',
                opacity: 0.1
            }
        },
        colors: ['#2563eb'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [20, 100]
            }
        },
        dataLabels: { enabled: false },
        stroke: {
            curve: 'smooth',
            width: 3,
            lineCap: 'round'
        },
        xaxis: {
            categories: labels,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                formatter: function (val) {
                    return "GH₵" + val.toLocaleString();
                },
                style: {
                    colors: '#64748b',
                    fontSize: '12px'
                }
            }
        },
        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 4,
            padding: {
                left: 20,
                right: 20
            }
        },
        markers: {
            size: 0,
            hover: {
                size: 6,
                sizeOffset: 3
            }
        },
        tooltip: {
            theme: 'light',
            x: { show: true },
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                const val = series[seriesIndex][dataPointIndex];
                const label = w.globals.labels[dataPointIndex];
                let growthTxt = '';
                let growthColor = 'text-green-600';
                
                if (dataPointIndex > 0) {
                    const prev = series[seriesIndex][dataPointIndex - 1];
                    const change = prev > 0 ? ((val - prev) / prev * 100).toFixed(1) : 0;
                    const isPositive = change >= 0;
                    growthTxt = `${isPositive ? '+' : ''}${change}%`;
                    growthColor = isPositive ? 'text-green-600' : 'text-red-600';
                    growthBg = isPositive ? 'bg-green-50' : 'bg-red-50';
                }

                return `
                    <div class="p-3 bg-white shadow-xl border border-gray-100 rounded-lg">
                        <div class="text-xs font-medium text-gray-500 mb-1">${label}</div>
                        <div class="flex items-center space-x-2">
                            <span class="text-lg font-bold text-gray-900">GH₵${val.toLocaleString()}</span>
                            ${growthTxt ? `<span class="text-xs font-bold ${growthColor} px-1.5 py-0.5 rounded ${isPositive ? 'bg-green-50' : 'bg-red-50'}">${growthTxt}</span>` : ''}
                        </div>
                    </div>
                `;
            }
        }
    };

    window.revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
    revenueChart.render();

    // Subscription Distribution Chart
    const subscriptionCtx = document.getElementById('subscriptionChart').getContext('2d');
    const subscriptionChart = new Chart(subscriptionCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_column($subscriptionAnalytics, 'name')) !!},
            datasets: [{
                data: {!! json_encode(array_column($subscriptionAnalytics, 'subscribers')) !!},
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(139, 92, 246, 0.8)'
                ],
                borderColor: [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(139, 92, 246)'
                ],
                borderWidth: 2,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + ' subscribers';
                        }
                    }
                }
            },
            cutout: '60%',
            onHover: (event, elements) => {
                const legendRows = document.querySelectorAll('[data-plan-index]');
                legendRows.forEach(row => row.classList.remove('bg-blue-50'));
                if (elements.length > 0) {
                    const index = elements[0].index;
                    const row = document.querySelector(`[data-plan-index="${index}"]`);
                    if (row) row.classList.add('bg-blue-50');
                }
            }
        }
    });

    // Functions
    function updateChart(chartType, period) {
        if (chartType !== 'revenue' || !window.revenueChart) return;

        let filteredTrends = [...trends];
        const now = new Date();
        const currentYear = now.getFullYear();

        if (period === '7d') filteredTrends = trends.slice(-1);
        else if (period === '30d') filteredTrends = trends.slice(-3);
        else if (period === '90d') filteredTrends = trends.slice(-6);
        else if (period === 'ytd') {
            filteredTrends = trends.filter(t => {
                const parts = t.month.split(' ');
                return parts[1] == currentYear;
            });
        }
        // 'all' uses full trends

        const newSeries = [{
            name: 'Revenue',
            data: filteredTrends.map(t => t.revenue)
        }];
        
        const newLabels = filteredTrends.map(t => t.month);

        window.revenueChart.updateSeries(newSeries);
        window.revenueChart.updateOptions({
            xaxis: { categories: newLabels }
        });

        // Update button styles for this chart section
        const chartWrapper = document.querySelector('#revenueChart').closest('.bg-white');
        const buttons = chartWrapper.querySelectorAll('button[onclick^="updateChart(\'revenue\'"]');
        buttons.forEach(btn => {
            if (btn.getAttribute('onclick').includes(`'${period}'`)) {
                btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
                btn.classList.remove('bg-white', 'text-gray-700', 'border-gray-200');
            } else {
                btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                btn.classList.add('bg-white', 'text-gray-700', 'border-gray-200');
            }
        });
    }

    function filterChartsByMonth(month) {
        console.log(`Filtering charts by month: ${month}`);
        // Update subscription chart and plan performance table based on selected month
        // For demo, just log
    }

    function sortTable(columnIndex) {
        const table = document.getElementById('planPerformanceTable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const isNumeric = columnIndex > 0; // Plan is text, others numeric

        rows.sort((a, b) => {
            const aVal = a.cells[columnIndex].textContent.trim();
            const bVal = b.cells[columnIndex].textContent.trim();

            if (isNumeric) {
                const aNum = parseFloat(aVal.replace(/[^\d.-]/g, ''));
                const bNum = parseFloat(bVal.replace(/[^\d.-]/g, ''));
                return aNum - bNum;
            } else {
                return aVal.localeCompare(bVal);
            }
        });

        rows.forEach(row => tbody.appendChild(row));
    }

    function exportRevenueData() {
        window.location.href = "{{ route('admin.revenue.export-trends') }}";
    }

    function generateReport() {
        alert('Report generation functionality would be implemented here');
    }

    function viewTransactions() {
        window.location.href = '/admin/transactions';
    }

    function managePlans() {
        window.location.href = '/admin/pricing-plans';
    }

    // Auto-refresh charts every 5 minutes
    setInterval(function() {
        // Refresh chart data
        location.reload();
    }, 300000);
    @endif

    @if($activeTab === 'summary' && $summaryCharts)
    // Initialize Summary tab charts
    document.addEventListener('DOMContentLoaded', function() {
        // Annual Chart
        const annualData = Object.values(@json($summaryCharts['annual']));
        if (annualData.length > 0) {
            new ApexCharts(document.querySelector("#annualSummaryChart"), {
                series: [{ name: 'Revenue', data: annualData.map(d => d.revenue) }],
                chart: { type: 'bar', height: 200, toolbar: { show: false } },
                colors: ['#8b5cf6'],
                xaxis: { categories: annualData.map(d => d.label) },
                yaxis: { labels: { formatter: (v) => "GH₵" + v.toLocaleString() } },
                tooltip: { y: { formatter: (v) => "GH₵" + v.toLocaleString() } }
            }).render();
        }

        // Monthly Chart
        const monthlyData = Object.values(@json($summaryCharts['monthly']));
        if (monthlyData.length > 0) {
            new ApexCharts(document.querySelector("#monthlySummaryChart"), {
                series: [{ name: 'Revenue', data: monthlyData.map(d => d.revenue) }],
                chart: { type: 'area', height: 250, toolbar: { show: false } },
                colors: ['#3b82f6'],
                stroke: { curve: 'smooth', width: 3 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.1 } },
                xaxis: { categories: monthlyData.map(d => d.label) },
                yaxis: { labels: { formatter: (v) => "GH₵" + v.toLocaleString() } },
                tooltip: { y: { formatter: (v) => "GH₵" + v.toLocaleString() } }
            }).render();
        }

        // Weekly Chart
        const weeklyData = Object.values(@json($summaryCharts['weekly']));
        if (weeklyData.length > 0) {
            new ApexCharts(document.querySelector("#weeklySummaryChart"), {
                series: [{ name: 'Revenue', data: weeklyData.map(d => d.revenue) }],
                chart: { type: 'area', height: 250, toolbar: { show: false } },
                colors: ['#10b981'],
                stroke: { curve: 'smooth', width: 3 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.1 } },
                xaxis: { categories: weeklyData.map(d => d.label) },
                yaxis: { labels: { formatter: (v) => "GH₵" + v.toLocaleString() } },
                tooltip: { y: { formatter: (v) => "GH₵" + v.toLocaleString() } }
            }).render();
        }
    });
    @endif

    @if($activeTab === 'payments' && $paymentAnalytics)
    // Payment Status Distribution Chart
    const paymentStatusCtx = document.getElementById('paymentStatusChart').getContext('2d');
    const paymentStatusChart = new Chart(paymentStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Successful', 'Pending', 'Failed'],
            datasets: [{
                data: [
                    {{ $paymentAnalytics['status_distribution']['success'] ?? 0 }},
                    {{ $paymentAnalytics['status_distribution']['pending'] ?? 0 }},
                    {{ $paymentAnalytics['status_distribution']['failed'] ?? 0 }}
                ],
                backgroundColor: [
                    'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                    'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
                    'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)'
                ].map(gradient => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const gradientObj = ctx.createLinearGradient(0, 0, 0, 400);
                    if (gradient.includes('#10b981')) {
                        gradientObj.addColorStop(0, '#10b981');
                        gradientObj.addColorStop(1, '#059669');
                    } else if (gradient.includes('#f59e0b')) {
                        gradientObj.addColorStop(0, '#f59e0b');
                        gradientObj.addColorStop(1, '#d97706');
                    } else {
                        gradientObj.addColorStop(0, '#ef4444');
                        gradientObj.addColorStop(1, '#dc2626');
                    }
                    return gradientObj;
                }),
                borderColor: [
                    '#059669',
                    '#d97706',
                    '#dc2626'
                ],
                borderWidth: 3,
                hoverOffset: 15,
                hoverBorderWidth: 4
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + context.parsed + ' payments (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '65%',
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });

    // Payment Trends Chart
    const paymentTrendsCtx = document.getElementById('paymentTrendsChart').getContext('2d');
    const paymentTrendsData = {!! json_encode($paymentAnalytics['trends']) !!};

    // Create gradient for bars
    const paymentTrendsGradient = paymentTrendsCtx.createLinearGradient(0, 0, 0, 400);
    paymentTrendsGradient.addColorStop(0, 'rgba(59, 130, 246, 0.8)');
    paymentTrendsGradient.addColorStop(1, 'rgba(147, 197, 253, 0.4)');

    const paymentTrendsChart = new Chart(paymentTrendsCtx, {
        type: 'bar',
        data: {
            labels: paymentTrendsData.map(item => item.month),
            datasets: [{
                label: 'Payment Amount (GH₵)',
                data: paymentTrendsData.map(item => item.amount),
                backgroundColor: paymentTrendsGradient,
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(59, 130, 246, 0.9)',
                hoverBorderColor: 'rgb(37, 99, 235)',
                hoverBorderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 8,
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            const index = context.dataIndex;
                            const item = paymentTrendsData[index];
                            return [
                                '💰 Amount: GH₵' + item.amount.toLocaleString(),
                                '📊 Count: ' + item.count + ' payments'
                            ];
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        borderDash: [5, 5]
                    },
                    ticks: {
                        callback: function(value) {
                            return 'GH₵' + value.toLocaleString();
                        },
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        color: '#6b7280'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        color: '#6b7280'
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart',
                delay: function(context) {
                    return context.dataIndex * 200;
                }
            },
            onHover: (event, elements) => {
                event.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
            }
        }
    });

    // Duration Chart
    const durationCtx = document.getElementById('durationChart').getContext('2d');
    const durationData = {!! json_encode($paymentAnalytics['metadata']['durations']) !!};

    // Create gradient for duration bars
    const durationGradient = durationCtx.createLinearGradient(0, 0, 0, 400);
    durationGradient.addColorStop(0, 'rgba(139, 92, 246, 0.8)');
    durationGradient.addColorStop(1, 'rgba(196, 181, 253, 0.4)');

    const durationChart = new Chart(durationCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(durationData),
            datasets: [{
                label: 'Payments',
                data: Object.values(durationData),
                backgroundColor: durationGradient,
                borderColor: 'rgb(139, 92, 246)',
                borderWidth: 2,
                borderRadius: 6,
                hoverBackgroundColor: 'rgba(139, 92, 246, 0.9)',
                hoverBorderColor: 'rgb(124, 58, 237)',
                hoverBorderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 8,
                    callbacks: {
                        title: function(context) {
                            return 'Duration: ' + context[0].label;
                        },
                        label: function(context) {
                            return '📊 ' + context.parsed.y + ' payments';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        color: '#6b7280'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        borderDash: [5, 5]
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        color: '#6b7280'
                    },
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart',
                delay: function(context) {
                    return context.dataIndex * 150;
                }
            }
        }
    });

    // Plan Chart
    const planCtx = document.getElementById('planChart').getContext('2d');
    const planData = {!! json_encode($paymentAnalytics['metadata']['plan_names']) !!};

    // Create gradient for plan bars
    const planGradient = planCtx.createLinearGradient(0, 0, 0, 400);
    planGradient.addColorStop(0, 'rgba(16, 185, 129, 0.8)');
    planGradient.addColorStop(1, 'rgba(110, 231, 183, 0.4)');

    const planChart = new Chart(planCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(planData),
            datasets: [{
                label: 'Payments',
                data: Object.values(planData),
                backgroundColor: planGradient,
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 2,
                borderRadius: 6,
                hoverBackgroundColor: 'rgba(16, 185, 129, 0.9)',
                hoverBorderColor: 'rgb(5, 150, 105)',
                hoverBorderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 8,
                    callbacks: {
                        title: function(context) {
                            return 'Plan: ' + context[0].label;
                        },
                        label: function(context) {
                            return '📊 ' + context.parsed.y + ' payments';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        color: '#6b7280'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        borderDash: [5, 5]
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        color: '#6b7280'
                    },
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart',
                delay: function(context) {
                    return context.dataIndex * 150;
                }
            }
        }
    });

    // Functions
    function updatePaymentChart(period) {
        // Implementation for updating payment chart data based on period
        console.log(`Updating payment chart for ${period}`);
        // Here you would fetch new data and update the chart
    }

    function exportPaymentsData() {
        window.location.href = "{{ route('admin.revenue.export-payments') }}";
    }

    function openPaymentModal(payment) {
        if (!payment) return;
        
        document.getElementById('modalRefText').textContent = payment.reference || 'N/A';
        document.getElementById('modalUserName').textContent = payment.user_name || 'Unknown';
        document.getElementById('modalUserEmail').textContent = payment.user_email || 'No email';
        document.getElementById('modalUserPhone').textContent = payment.user_phone && payment.user_phone !== 'N/A' ? payment.user_phone : '';
        
        const userLink = document.getElementById('modalUserLink');
        if (payment.user_id) {
            userLink.href = `/admin/users/${payment.user_id}`;
            userLink.classList.remove('hidden');
        } else {
            userLink.classList.add('hidden');
        }
        
        document.getElementById('modalPlanName').textContent = payment.plan_name || 'N/A';
        document.getElementById('modalDuration').textContent = payment.duration || 'Monthly';
        document.getElementById('modalAmount').textContent = `GH₵${parseFloat(payment.amount || 0).toFixed(2)}`;
        document.getElementById('modalProvider').textContent = payment.payment_provider || 'Paystack';
        document.getElementById('modalTransactionId').textContent = payment.transaction_id || 'N/A';
        document.getElementById('modalCreatedAt').textContent = payment.created_at || 'N/A';
        document.getElementById('modalPaidAt').textContent = payment.paid_at || 'Not completed yet';
        
        const banner = document.getElementById('modalStatusBanner');
        const icon = document.getElementById('modalStatusIcon');
        const badge = document.getElementById('modalStatusBadge');
        const responseText = document.getElementById('modalGatewayResponse');
        const syncBadge = document.getElementById('modalSyncBadge');
        const verifyAction = document.getElementById('modalVerifyAction');
        const verifyForm = document.getElementById('modalVerifyForm');
        
        if (payment.verified_via_sync) {
            syncBadge.classList.remove('hidden');
        } else {
            syncBadge.classList.add('hidden');
        }
        
        if (payment.status === 'success') {
            banner.className = 'p-4 rounded-xl border flex items-start space-x-3 bg-green-50 border-green-200 text-green-900';
            icon.innerHTML = '<i class="fas fa-check-circle text-green-600"></i>';
            badge.textContent = 'Payment Successful';
            badge.className = 'font-bold text-sm text-green-800';
            responseText.textContent = payment.gateway_response || 'Transaction approved and subscription active.';
            responseText.className = 'text-xs mt-1 text-green-700';
            verifyAction.classList.add('hidden');
        } else if (payment.status === 'pending') {
            banner.className = 'p-4 rounded-xl border flex items-start space-x-3 bg-yellow-50 border-yellow-200 text-yellow-900';
            icon.innerHTML = '<i class="fas fa-clock text-yellow-600"></i>';
            badge.textContent = 'Payment Pending';
            badge.className = 'font-bold text-sm text-yellow-800';
            responseText.textContent = payment.gateway_response || 'Waiting for customer authorization / USSD approval.';
            responseText.className = 'text-xs mt-1 text-yellow-700';
            verifyAction.classList.remove('hidden');
            verifyForm.action = `/admin/payments/${payment.id}/verify`;
        } else {
            banner.className = 'p-4 rounded-xl border flex items-start space-x-3 bg-red-50 border-red-200 text-red-900';
            icon.innerHTML = '<i class="fas fa-times-circle text-red-600"></i>';
            badge.textContent = 'Payment Failed / Abandoned';
            badge.className = 'font-bold text-sm text-red-800';
            responseText.textContent = payment.gateway_response || 'Payment was declined or cancelled by the user.';
            responseText.className = 'text-xs mt-1 text-red-700';
            verifyAction.classList.remove('hidden');
            verifyForm.action = `/admin/payments/${payment.id}/verify`;
        }
        
        const modal = document.getElementById('paymentDetailsModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closePaymentModal() {
        const modal = document.getElementById('paymentDetailsModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    // Filter payments table
    document.getElementById('statusFilter').addEventListener('change', function() {
        const filterValue = this.value.toLowerCase();
        const table = document.getElementById('paymentsTable');
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const statusCell = row.querySelector('td:nth-child(4) span');
            if (!statusCell) return;

            const status = statusCell.textContent.toLowerCase().trim();
            if (filterValue === '' || status === filterValue) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    @endif
    // Recalculate Form Handling
    const recalculateForm = document.getElementById('recalculateForm');
    if (recalculateForm) {
        recalculateForm.addEventListener('submit', function() {
            const btn = document.getElementById('recalculateBtn');
            const loading = document.getElementById('recalculateLoading');
            
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                btn.querySelector('i').classList.add('fa-spin');
                btn.querySelector('span').textContent = 'Processing...';
            }
            
            if (loading) {
                loading.classList.remove('hidden');
                loading.classList.add('flex');
            }
        });
    }
</script>
@endsection
