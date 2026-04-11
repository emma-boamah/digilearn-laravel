@extends('layouts.admin')

@section('title', 'Reports & Web Analytics')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Reports & Web Analytics</h1>
                    <p class="text-gray-600">Track platform engagement and user feedback</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        Last updated: {{ now()->format('M d, Y H:i') }}
                    </div>
                    <button onclick="location.reload()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
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
                <nav class="-mb-px flex space-x-8">
                    <a href="{{ route('admin.analytics', ['tab' => 'report']) }}"
                       class="group relative py-4 px-1 font-semibold text-sm transition-all duration-200 ease-in-out cursor-pointer
                       {{ $activeTab === 'report'
                           ? 'text-blue-700 border-b-2 border-blue-500'
                           : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300' }}">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-chart-bar {{ $activeTab === 'report' ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                            <span>Usage Report</span>
                        </div>
                    </a>
                    <a href="{{ route('admin.analytics', ['tab' => 'web']) }}"
                       class="group relative py-4 px-1 font-semibold text-sm transition-all duration-200 ease-in-out cursor-pointer
                       {{ $activeTab === 'web'
                           ? 'text-indigo-700 border-b-2 border-indigo-500'
                           : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300' }}">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-globe {{ $activeTab === 'web' ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                            <span>Web Analytics</span>
                        </div>
                    </a>
                    <a href="{{ route('admin.analytics', ['tab' => 'feedbacks']) }}"
                       class="group relative py-4 px-1 font-semibold text-sm transition-all duration-200 ease-in-out cursor-pointer
                       {{ $activeTab === 'feedbacks'
                           ? 'text-purple-700 border-b-2 border-purple-500'
                           : 'text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300' }}">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-comments {{ $activeTab === 'feedbacks' ? 'text-purple-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                            <span>User Feedback</span>
                        </div>
                        @if($feedbackStats && $feedbackStats['failed'] > 0)
                            <span class="absolute top-3 -right-4 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[10px] text-white items-center justify-center">{{ $feedbackStats['failed'] }}</span>
                            </span>
                        @endif
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($activeTab === 'report')
            <!-- Usage Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-sm font-medium text-gray-500 uppercase">Recent Registrations (30d)</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ collect($analyticsData['user_registrations'])->sum('count') }}</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-sm font-medium text-gray-500 uppercase">Avg. Daily Active Users</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-2">
                        {{ round(collect($analyticsData['active_users'])->avg('count')) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Daily Active Users Trend -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Daily Active Users</h2>
                        <i class="fas fa-chart-line text-indigo-500"></i>
                    </div>
                    <div class="p-6">
                        <canvas id="activeUsersChart" height="250"></canvas>
                    </div>
                </div>

                <!-- Registration Trend -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Registration Trend</h2>
                        <i class="fas fa-user-plus text-green-500"></i>
                    </div>
                    <div class="p-6">
                        <canvas id="registrationTrendChart" height="250"></canvas>
                    </div>
                </div>

                <!-- Lesson Views -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Lesson Views by Subject</h2>
                        <i class="fas fa-book-open text-blue-500"></i>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                            @foreach($analyticsData['lesson_views'] as $view)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-medium text-gray-700">{{ $view['subject'] }}</span>
                                        <span class="text-gray-500">{{ number_format($view['views']) }} views</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, ($view['views'] / ($analyticsData['lesson_views'][0]['views'] ?: 1)) * 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($activeTab === 'web')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Popular Subjects -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Subject Distribution</h2>
                        <i class="fas fa-chart-pie text-indigo-500"></i>
                    </div>
                    <div class="p-6 flex flex-col items-center">
                        <canvas id="subjectPieChart" width="300" height="300"></canvas>
                        <div class="mt-6 w-full space-y-2">
                            @foreach($analyticsData['popular_subjects'] as $subject)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">{{ $subject['name'] }}</span>
                                    <span class="font-bold text-gray-900">{{ $subject['percentage'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- User Engagement -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Platform Engagement</h2>
                        <i class="fas fa-bolt text-yellow-500"></i>
                    </div>
                    <div class="p-6">
                        <div class="space-y-6">
                            @foreach($analyticsData['user_engagement'] as $metric)
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400">
                                        <i class="fas fa-circle-info"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $metric['label'] }}</p>
                                        <div class="mt-1 w-full bg-gray-100 rounded-full h-2">
                                            <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $metric['value'] }}%"></div>
                                        </div>
                                    </div>
                                    <span class="text-sm font-bold text-indigo-600">{{ $metric['value'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($activeTab === 'feedbacks')
            <!-- Feedback Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-sm font-medium text-gray-500 uppercase">Total Submissions</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $feedbackStats['total'] }}</p>
                    <div class="mt-4 flex space-x-4 text-xs">
                        <span class="text-blue-600 font-medium">{{ $feedbackStats['contact_type'] }} Contact</span>
                        <span class="text-purple-600 font-medium">{{ $feedbackStats['feedback_type'] }} Feedback</span>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-sm font-medium text-gray-500 uppercase">Delivery Success</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $feedbackStats['sent'] }}</p>
                    <p class="text-xs text-gray-500 mt-4">Successfully delivered to inbox</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-sm font-medium text-gray-500 uppercase">Delivery Failures</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ $feedbackStats['failed'] }}</p>
                    @if($feedbackStats['failed'] > 0)
                        <p class="text-xs text-red-500 mt-4 font-medium animate-pulse">Action required: check service limits</p>
                    @else
                        <p class="text-xs text-gray-500 mt-4">All systems operational</p>
                    @endif
                </div>
            </div>

            <!-- Feedback Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Recent Feedback & Contact Messages</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Message</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            @forelse($feedbacks as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-xs font-bold mr-3">
                                                {{ $item->user ? substr($item->user->name, 0, 1) : '?' }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $item->user->name ?? 'Deleted User' }}</div>
                                                <div class="text-xs text-gray-500">{{ $item->user->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $item->type === 'contact' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                            {{ ucfirst($item->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-light text-sm text-gray-600 max-w-xs truncate">
                                        {{ $item->message }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($item->status === 'sent')
                                                <span class="flex h-2 w-2 rounded-full bg-green-500 mr-2"></span>
                                                <span class="text-sm text-green-700 font-medium">Delivered</span>
                                            @elseif($item->status === 'failed')
                                                <span class="flex h-2 w-2 rounded-full bg-red-500 mr-2"></span>
                                                <span class="text-sm text-red-700 font-medium">Failed</span>
                                                <i class="fas fa-circle-exclamation text-red-400 ml-2 cursor-help" title="{{ $item->error_message }}"></i>
                                            @else
                                                <span class="flex h-2 w-2 rounded-full bg-yellow-500 mr-2 animate-pulse"></span>
                                                <span class="text-sm text-yellow-700 font-medium">Pending</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $item->created_at->format('M d, H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center space-x-3">
                                            @php $mailSearchUrl = "https://mail.zoho.com/zm/#search/from:{$item->user->email}"; @endphp
                                            <a href="{{ $mailSearchUrl }}" target="_blank" class="text-blue-600 hover:text-blue-900" title="View in Mailbox">
                                                <i class="fas fa-envelope-open-text"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                        No feedback received yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($feedbacks && $feedbacks->hasPages())
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                        {{ $feedbacks->appends(['tab' => 'feedbacks'])->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($activeTab === 'report' || $activeTab === 'web')
            
            // Initialization for Active Users Chart
            if (document.getElementById('activeUsersChart')) {
                const ctx = document.getElementById('activeUsersChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode(collect($analyticsData['active_users'])->pluck('date')) !!},
                        datasets: [{
                            label: 'Active Users',
                            data: {!! json_encode(collect($analyticsData['active_users'])->pluck('count')) !!},
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 2,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { 
                            y: { 
                                beginAtZero: true, 
                                ticks: { stepSize: 1, color: '#94a3b8' },
                                grid: { color: 'rgba(0,0,0,0.05)' }
                            },
                            x: {
                                ticks: { color: '#94a3b8' },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }

            // Initialization for Registration Trend Chart
            if (document.getElementById('registrationTrendChart')) {
                const ctx = document.getElementById('registrationTrendChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode(collect($analyticsData['user_registrations'])->pluck('date')) !!},
                        datasets: [{
                            label: 'New Users',
                            data: {!! json_encode(collect($analyticsData['user_registrations'])->pluck('count')) !!},
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 2,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { 
                            y: { 
                                beginAtZero: true, 
                                ticks: { stepSize: 1, color: '#94a3b8' },
                                grid: { color: 'rgba(0,0,0,0.05)' }
                            },
                            x: {
                                ticks: { color: '#94a3b8' },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }

            // Initialization for Subject Distribution
            if (document.getElementById('subjectPieChart')) {
                const ctx = document.getElementById('subjectPieChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode(collect($analyticsData['popular_subjects'])->pluck('name')) !!},
                        datasets: [{
                            data: {!! json_encode(collect($analyticsData['popular_subjects'])->pluck('percentage')) !!},
                            backgroundColor: ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b']
                        }]
                    },
                    options: {
                        cutout: '70%',
                        plugins: { legend: { display: false } }
                    }
                });
            }
        @endif
    });
</script>
@endpush
@endsection
