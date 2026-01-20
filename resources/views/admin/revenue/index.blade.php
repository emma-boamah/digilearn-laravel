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
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        Last updated: {{ now()->format('M d, Y H:i') }}
                    </div>
                    <button onclick="exportRevenueData()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-download mr-2"></i>Export Data
                    </button>
                    <button onclick="location.reload()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
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
                    <a href="{{ route('admin.revenue', ['tab' => 'revenue']) }}"
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
                    <a href="{{ route('admin.revenue', ['tab' => 'payments']) }}"
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
                </nav>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($activeTab === 'revenue')
        <!-- Revenue Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm font-medium uppercase tracking-wide">Total Revenue</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">GH₵{{ number_format($revenueData['total_revenue'], 2) }}</p>
                        <div class="flex items-center mt-3">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            <p class="text-gray-500 text-sm">+{{ $revenueData['revenue_growth'] }}% this month</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-xl">
                        <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm font-medium uppercase tracking-wide">Monthly Revenue</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">GH₵{{ number_format($revenueData['monthly_revenue'], 2) }}</p>
                        <div class="flex items-center mt-3">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                            <p class="text-gray-500 text-sm">{{ $revenueData['active_subscriptions'] }} active subs</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-xl">
                        <i class="fas fa-calendar-alt text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Average Revenue Per User -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm font-medium uppercase tracking-wide">Avg Per User</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">GH₵{{ number_format($revenueData['average_revenue_per_user'], 2) }}</p>
                        <div class="flex items-center mt-3">
                            <div class="w-2 h-2 bg-purple-500 rounded-full mr-2"></div>
                            <p class="text-gray-500 text-sm">Per active subscriber</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-xl">
                        <i class="fas fa-user-dollar text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Churn Rate -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm font-medium uppercase tracking-wide">Churn Rate</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $revenueData['churn_rate'] }}%</p>
                        <div class="flex items-center mt-3">
                            <div class="w-2 h-2 bg-orange-500 rounded-full mr-2"></div>
                            <p class="text-gray-500 text-sm">+{{ $revenueData['new_subscriptions_today'] }} new today</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-4 rounded-xl">
                        <i class="fas fa-chart-pie text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Trend Chart (Full Width) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 mb-8">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-chart-area text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Revenue Trend</h2>
                            <p class="text-sm text-gray-600">Monthly revenue over time</p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="updateChart('revenue', '7d')" class="px-4 py-2 text-sm font-medium bg-white text-gray-700 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-colors">7D</button>
                        <button onclick="updateChart('revenue', '30d')" class="px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg border border-blue-600 hover:bg-blue-700 transition-colors">30D</button>
                        <button onclick="updateChart('revenue', '90d')" class="px-4 py-2 text-sm font-medium bg-white text-gray-700 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-colors">90D</button>
                        <button onclick="updateChart('revenue', 'ytd')" class="px-4 py-2 text-sm font-medium bg-white text-gray-700 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-colors">YTD</button>
                        <button onclick="updateChart('revenue', 'all')" class="px-4 py-2 text-sm font-medium bg-white text-gray-700 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-colors">ALL</button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl p-4">
                    <canvas id="revenueChart" style="height: 400px; width: 100%;"></canvas>
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
        <!-- Payment Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Payments -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm font-medium uppercase tracking-wide">Total Payments</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($paymentAnalytics['total_payments']) }}</p>
                        <div class="flex items-center mt-3">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                            <p class="text-gray-500 text-sm">{{ $paymentAnalytics['successful_payments'] }} successful</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-xl">
                        <i class="fas fa-credit-card text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Success Rate -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm font-medium uppercase tracking-wide">Success Rate</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $paymentAnalytics['success_rate'] }}%</p>
                        <div class="flex items-center mt-3">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            <p class="text-gray-500 text-sm">{{ $paymentAnalytics['status_distribution']['failed'] ?? 0 }} failed</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-xl">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Average Payment Amount -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm font-medium uppercase tracking-wide">Avg Amount</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">GH₵{{ number_format($paymentAnalytics['average_amount'], 2) }}</p>
                        <div class="flex items-center mt-3">
                            <div class="w-2 h-2 bg-purple-500 rounded-full mr-2"></div>
                            <p class="text-gray-500 text-sm">Per successful payment</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-xl">
                        <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Value -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm font-medium uppercase tracking-wide">Total Value</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">GH₵{{ number_format($paymentAnalytics['total_value'], 2) }}</p>
                        <div class="flex items-center mt-3">
                            <div class="w-2 h-2 bg-orange-500 rounded-full mr-2"></div>
                            <p class="text-gray-500 text-sm">All successful payments</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-4 rounded-xl">
                        <i class="fas fa-dollar-sign text-orange-600 text-xl"></i>
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
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Payment Trends</h2>
                        <div class="flex space-x-2">
                            <button onclick="updatePaymentChart('7d')" class="px-4 py-2 text-sm font-medium bg-white text-gray-700 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-colors">7D</button>
                            <button onclick="updatePaymentChart('30d')" class="px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg border border-blue-600 hover:bg-blue-700 transition-colors">30D</button>
                            <button onclick="updatePaymentChart('90d')" class="px-4 py-2 text-sm font-medium bg-white text-gray-700 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-colors">90D</button>
                            <button onclick="updatePaymentChart('ytd')" class="px-4 py-2 text-sm font-medium bg-white text-gray-700 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-colors">YTD</button>
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
                    <div class="flex space-x-3">
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
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @foreach($paymentAnalytics['recent_payments'] as $payment)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-br from-gray-400 to-gray-500 rounded-lg flex items-center justify-center text-white text-sm font-medium">
                                        {{ substr($payment['user_name'], 0, 1) }}
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
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                    @if($payment['status'] === 'success') bg-green-100 text-green-800
                                    @elseif($payment['status'] === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    <i class="fas @if($payment['status'] === 'success') fa-check-circle @elseif($payment['status'] === 'pending') fa-clock @else fa-times-circle @endif mr-1"></i>
                                    {{ ucfirst($payment['status']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment['duration'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $payment['created_at'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
                            <div class="w-10 h-10 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center text-white text-lg font-bold">
                                {{ $index + 1 }}
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
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    @if($activeTab === 'revenue')
    // Revenue Trend Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = {!! json_encode($revenueTrends) !!};
    const revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: revenueData.map(item => item.month),
            datasets: [{
                label: 'Revenue (GH₵)',
                data: revenueData.map(item => item.revenue),
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false,
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
                    callbacks: {
                        label: function(context) {
                            const index = context.dataIndex;
                            const item = revenueData[index];
                            return [
                                `Revenue: GH₵${item.revenue.toLocaleString()}`,
                                `New Subs: ${item.new_subs || Math.floor(Math.random() * 50) + 10}`,
                                `Churn: ${item.churn || Math.floor(Math.random() * 10) + 1}`
                            ];
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'GH₵' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            onClick: (event, elements) => {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    filterChartsByMonth(revenueData[index].month);
                }
            }
        }
    });

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
        // Implementation for updating chart data based on period
        console.log(`Updating ${chartType} chart for ${period}`);
        // Here you would fetch new data and update the chart
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
        window.location.href = '/admin/revenue/export';
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
        window.location.href = '/admin/payments/export';
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
</script>
@endsection
