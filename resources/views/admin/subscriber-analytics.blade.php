@extends('layouts.admin')

@section('title', 'Subscriber Analytics')

@section('content')
<div class="min-h-screen bg-gray-50/50 p-4 md:p-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Subscriber Analytics Overview</h1>
        <p class="text-gray-500 mt-1">Visualizing growth patterns, revenue shifts, and retention health across your subscriber base for the current fiscal period.</p>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Subscribers -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                    <i class="fas fa-users text-lg"></i>
                </div>
                <div class="flex items-center space-x-1 px-2 py-1 bg-green-50 text-green-600 rounded-lg text-xs font-bold">
                    <i class="fas fa-arrow-up text-[10px]"></i>
                    <span>4.2%</span>
                </div>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Subscribers</p>
                <h3 class="text-2xl font-bold text-gray-900">{{ number_format($totalSubscribers) }}</h3>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-blue-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>

        <!-- MRR -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                    <i class="fas fa-money-bill-wave text-lg"></i>
                </div>
                <div class="flex items-center space-x-1 px-2 py-1 bg-green-50 text-green-600 rounded-lg text-xs font-bold">
                    <i class="fas fa-arrow-up text-[10px]"></i>
                    <span>8.1%</span>
                </div>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Monthly Recurring Revenue</p>
                <h3 class="text-2xl font-bold text-gray-900">GH₵{{ number_format($mrr, 2) }}</h3>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-indigo-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>

        <!-- Churn Rate -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600">
                    <i class="fas fa-user-minus text-lg"></i>
                </div>
                <div class="flex items-center space-x-1 px-2 py-1 bg-green-50 text-green-600 rounded-lg text-xs font-bold">
                    <i class="fas fa-arrow-down text-[10px]"></i>
                    <span>0.3%</span>
                </div>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Churn Rate</p>
                <h3 class="text-2xl font-bold text-gray-900">{{ number_format($churnRate, 1) }}%</h3>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-rose-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>

        <!-- ARPU -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                    <i class="fas fa-chart-line text-lg"></i>
                </div>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Avg. Revenue Per User</p>
                <h3 class="text-2xl font-bold text-gray-900">GH₵{{ number_format($arpu, 2) }}</h3>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-emerald-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Growth Trend -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Subscriber Growth Trend</h3>
                    <p class="text-sm text-gray-500">12-month performance analysis</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span class="text-xs font-medium text-gray-600">Active</span>
                    </div>
                </div>
            </div>
            <div class="h-64">
                <canvas id="growthChart"></canvas>
            </div>
        </div>

        <!-- Plan Distribution -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Subscription Plans</h3>
            <div class="flex-1 flex flex-col items-center justify-center relative">
                <div class="w-48 h-48">
                    <canvas id="plansChart"></canvas>
                </div>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-4">
                    <span class="text-2xl font-bold text-gray-900">72%</span>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Growth</span>
                </div>
            </div>
            <div class="mt-6 space-y-3">
                @foreach($planDistribution as $plan)
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 rounded-full @if($loop->first) bg-blue-600 @elseif($loop->iteration == 2) bg-indigo-500 @else bg-slate-300 @endif"></div>
                        <span class="text-sm font-medium text-gray-600">{{ $plan['name'] }}</span>
                    </div>
                    <span class="text-sm font-bold text-gray-900">{{ $plan['percentage'] }}%</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Recent Subscriber Activity</h3>
                <p class="text-sm text-gray-500">Live feed of global subscription events</p>
            </div>
            <div class="flex items-center space-x-2">
                <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-filter text-sm"></i>
                </button>
                <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-search text-sm"></i>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Name</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Plan</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Date Joined</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recentActivity as $activity)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full overflow-hidden flex items-center justify-center bg-gray-100 shadow-sm border border-gray-100">
                                    @if($activity->user && $activity->user->avatar_url)
                                        <img src="{{ $activity->user->avatar_url }}" alt="{{ $activity->user->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-[10px] font-bold uppercase tracking-wider">
                                            {{ $activity->user ? $activity->user->initials : 'U' }}
                                        </div>
                                    @endif
                                </div>
                                <span class="font-bold text-gray-900">{{ $activity->user->name ?? 'Unknown User' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-600">{{ $activity->pricingPlan->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-600">{{ $activity->started_at ? $activity->started_at->format('M d, Y') : 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $status = strtolower($activity->status);
                                $statusClasses = match($status) {
                                    'active' => 'bg-green-100 text-green-700',
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'cancelled', 'expired' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-gray-100 text-gray-700'
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $statusClasses }}">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-gray-900">GH₵{{ number_format($activity->amount_paid, 2) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">No recent subscriber activity found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-6 bg-gray-50/30 flex items-center justify-between border-t border-gray-50">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Showing {{ $recentActivity->count() }} of {{ $totalSubscribers }} subscribers</p>
            <div class="flex items-center space-x-2">
                <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:bg-white hover:text-blue-600 transition-all">
                    <i class="fas fa-chevron-left text-[10px]"></i>
                </button>
                <div class="flex items-center space-x-1">
                    <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-600 text-white font-bold text-xs ring-4 ring-blue-50">1</button>
                    <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-transparent text-gray-400 hover:border-gray-200 hover:bg-white hover:text-blue-600 font-bold text-xs">2</button>
                    <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-transparent text-gray-400 hover:border-gray-200 hover:bg-white hover:text-blue-600 font-bold text-xs">3</button>
                </div>
                <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:bg-white hover:text-blue-600 transition-all">
                    <i class="fas fa-chevron-right text-[10px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Growth Chart
    const growthCtx = document.getElementById('growthChart').getContext('2d');
    new Chart(growthCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($growthData, 'month')) !!},
            datasets: [{
                label: 'Subscribers',
                data: {!! json_encode(array_column($growthData, 'count')) !!},
                backgroundColor: '#3b82f6',
                borderRadius: 6,
                barThickness: 32,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 12, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11, weight: '500' }, color: '#94a3b8' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9', drawBorder: false },
                    ticks: { font: { size: 11, weight: '500' }, color: '#94a3b8' }
                }
            }
        }
    });

    // Plans Chart
    const plansCtx = document.getElementById('plansChart').getContext('2d');
    new Chart(plansCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_column($planDistribution, 'name')) !!},
            datasets: [{
                data: {!! json_encode(array_column($planDistribution, 'count')) !!},
                backgroundColor: ['#2563eb', '#6366f1', '#e2e8f0'],
                borderWidth: 0,
                cutout: '80%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true
                }
            }
        }
    });
</script>
@endpush
@endsection
