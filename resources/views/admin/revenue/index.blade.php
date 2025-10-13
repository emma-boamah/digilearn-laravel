@extends('layouts.admin')

@section('title', 'Revenue Analytics')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Revenue Analytics</h1>
                    <p class="text-gray-600">Track subscription revenue and financial performance</p>
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Revenue Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Revenue</p>
                        <p class="text-3xl font-bold">GH₵{{ number_format($revenueData['total_revenue'], 2) }}</p>
                        <p class="text-blue-100 text-sm mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>+{{ $revenueData['revenue_growth'] }}% this month
                        </p>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 p-3 rounded-full">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Monthly Revenue</p>
                        <p class="text-3xl font-bold">GH₵{{ number_format($revenueData['monthly_revenue'], 2) }}</p>
                        <p class="text-green-100 text-sm mt-1">
                            {{ $revenueData['active_subscriptions'] }} active subscriptions
                        </p>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 p-3 rounded-full">
                        <i class="fas fa-calendar-alt text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Average Revenue Per User -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Avg Revenue Per User</p>
                        <p class="text-3xl font-bold">GH₵{{ number_format($revenueData['average_revenue_per_user'], 2) }}</p>
                        <p class="text-purple-100 text-sm mt-1">
                            Per active subscriber
                        </p>
                    </div>
                    <div class="bg-purple-400 bg-opacity-30 p-3 rounded-full">
                        <i class="fas fa-user-dollar text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Churn Rate -->
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Churn Rate</p>
                        <p class="text-3xl font-bold">{{ $revenueData['churn_rate'] }}%</p>
                        <p class="text-orange-100 text-sm mt-1">
                            +{{ $revenueData['new_subscriptions_today'] }} new today
                        </p>
                    </div>
                    <div class="bg-orange-400 bg-opacity-30 p-3 rounded-full">
                        <i class="fas fa-chart-pie text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Revenue Trend Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Revenue Trend</h2>
                        <div class="flex space-x-2">
                            <button onclick="updateChart('revenue', '6m')" class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">6M</button>
                            <button onclick="updateChart('revenue', '1y')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">1Y</button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Subscription Distribution -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Subscription Distribution</h2>
                </div>
                <div class="p-6">
                    <canvas id="subscriptionChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Detailed Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Plan Performance -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Plan Performance</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-6">
                            @foreach($subscriptionAnalytics as $key => $plan)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold">
                                        {{ substr($plan['name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $plan['name'] }}</h3>
                                        <p class="text-sm text-gray-600">{{ $plan['subscribers'] }} subscribers</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-900">GH₵{{ number_format($plan['revenue'], 2) }}</p>
                                    <p class="text-sm text-gray-600">{{ $plan['percentage'] }}% of total</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Metrics -->
            <div class="space-y-6">
                <!-- Growth Metrics -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Growth Metrics</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Monthly Growth</span>
                                <span class="text-sm font-semibold text-green-600">+{{ $revenueData['revenue_growth'] }}%</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Weekly Revenue</span>
                                <span class="text-sm font-semibold text-gray-900">GH₵{{ number_format($revenueData['weekly_revenue'], 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Daily Revenue</span>
                                <span class="text-sm font-semibold text-gray-900">GH₵{{ number_format($revenueData['daily_revenue'], 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Conversion Rate</span>
                                <span class="text-sm font-semibold text-blue-600">3.2%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performing Plans -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Top Performers</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($topPlans as $index => $plan)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $plan['plan'] }}</p>
                                        <p class="text-xs text-gray-600">+{{ $plan['growth'] }}% growth</p>
                                    </div>
                                </div>
                                <p class="font-semibold text-gray-900">GH₵{{ number_format($plan['revenue']) }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Quick Actions</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <button onclick="generateReport()" class="w-full flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-file-alt mr-2"></i>Generate Report
                            </button>
                            <button onclick="viewTransactions()" class="w-full flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-list mr-2"></i>View Transactions
                            </button>
                            <button onclick="managePlans()" class="w-full flex items-center justify-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                <i class="fas fa-cog mr-2"></i>Manage Plans
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Revenue Trend Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($revenueTrends, 'month')) !!},
            datasets: [{
                label: 'Revenue (GH₵)',
                data: {!! json_encode(array_column($revenueTrends, 'revenue')) !!},
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
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
            elements: {
                point: {
                    hoverBackgroundColor: 'rgb(59, 130, 246)'
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
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });

    // Functions
    function updateChart(chartType, period) {
        // Implementation for updating chart data based on period
        console.log(`Updating ${chartType} chart for ${period}`);
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
</script>
@endsection
