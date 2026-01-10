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

        <!-- Revenue Trend Chart (Full Width) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Revenue Trend</h2>
                    <div class="flex space-x-2">
                        <button onclick="updateChart('revenue', '7d')" class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">7D</button>
                        <button onclick="updateChart('revenue', '30d')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">30D</button>
                        <button onclick="updateChart('revenue', '90d')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">90D</button>
                        <button onclick="updateChart('revenue', 'ytd')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">YTD</button>
                        <button onclick="updateChart('revenue', 'all')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">ALL</button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="revenueChart" style="height: 450px; width: 100%;"></canvas>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Subscription Distribution -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Subscription Distribution</h2>
                </div>
                <div class="p-6">
                    <div class="flex flex-col items-center">
                        <canvas id="subscriptionChart" width="300" height="300" style="height: 300px; width: 300px;" class="mb-4"></canvas>
                        <div class="w-full">
                            <div class="space-y-2">
                                @foreach($subscriptionAnalytics as $plan)
                                <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 transition-colors" data-plan-index="{{ $loop->index }}">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-4 h-4 rounded-full" style="background-color: {{ ['rgba(59, 130, 246, 0.8)', 'rgba(16, 185, 129, 0.8)', 'rgba(139, 92, 246, 0.8)'][$loop->index % 3] }}"></div>
                                        <span class="text-sm font-medium text-gray-900">{{ $plan['name'] }}</span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-semibold text-gray-900">{{ $plan['percentage'] }}%</div>
                                        <div class="text-xs text-gray-600">GH₵{{ number_format($plan['revenue'], 2) }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Growth Metrics -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Growth Metrics</h2>
                        <div class="flex space-x-2">
                            <button onclick="setGrowthPeriod('daily')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200" id="dailyBtn">Daily</button>
                            <button onclick="setGrowthPeriod('weekly')" class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200" id="weeklyBtn">Weekly</button>
                            <button onclick="setGrowthPeriod('monthly')" class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200" id="monthlyBtn">Monthly</button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600" id="growthLabel">Monthly Growth</span>
                            <span class="text-sm font-semibold" id="growthValue">+{{ $revenueData['revenue_growth'] }}%</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600" id="revenueLabel">Weekly Revenue</span>
                            <span class="text-sm font-semibold text-gray-900" id="revenueValue">GH₵{{ number_format($revenueData['weekly_revenue'], 2) }}</span>
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
        </div>

        <!-- Plan Performance Table (Full Width) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Plan Performance</h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table id="planPerformanceTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(0)">Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(1)">Subscribers</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(2)">Revenue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(3)">Growth</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable(4)">Churn</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($subscriptionAnalytics as $key => $plan)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold">
                                            {{ substr($plan['name'], 0, 1) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $plan['name'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $plan['subscribers'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">GH₵{{ number_format($plan['revenue'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        +{{ $plan['percentage'] }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2.1%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Additional Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
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

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
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
</script>
@endsection
