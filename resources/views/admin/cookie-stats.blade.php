@extends('layouts.admin')

@section('title', 'Cookie Statistics - Admin')
@section('page-title', 'Cookie Analytics')
@section('page-description', 'Monitor cookie consent and privacy compliance')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Cookie Analytics</h1>
                    <p class="text-gray-600">Monitor cookie consent and privacy compliance</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        Last updated: {{ now()->format('M d, Y H:i') }}
                    </div>
                    <button id="refreshButton" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Consents -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Consents</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_consents']) }}</p>
                        <p class="text-sm text-green-600 mt-1">
                            <i class="fas fa-check-circle mr-1"></i>All-time records
                        </p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-cookie-bite text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Recent Consents -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Recent Consents</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['recent_consents']) }}</p>
                        <p class="text-sm text-blue-600 mt-1">
                            <i class="fas fa-clock mr-1"></i>Last 30 days
                        </p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Unique IPs -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Unique Visitors</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['unique_ips']) }}</p>
                        <p class="text-sm text-purple-600 mt-1">
                            <i class="fas fa-users mr-1"></i>IP addresses
                        </p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-globe text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Analytics Accepted -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Analytics Opt-in</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['consent_types']['analytics'] ?? 0) }}</p>
                        <p class="text-sm text-orange-600 mt-1">
                            <i class="fas fa-chart-line mr-1"></i>Accepted tracking
                        </p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-chart-bar text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Consent Breakdown -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Cookie Type Breakdown</h2>
                <div class="text-sm text-gray-500">
                    Based on latest consent records
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($stats['consent_types'] ?? [] as $type => $count)
                <div class="text-center">
                    <div class="relative w-32 h-32 mx-auto mb-4">
                        <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 36 36">
                            <path d="M18 2.0845
                                  a 15.9155 15.9155 0 0 1 0 31.831
                                  a 15.9155 15.9155 0 0 1 0 -31.831"
                                  fill="none"
                                  stroke="#e2e8f0"
                                  stroke-width="2"/>
                            <path d="M18 2.0845
                                  a 15.9155 15.9155 0 0 1 0 31.831
                                  a 15.9155 15.9155 0 0 1 0 -31.831"
                                  fill="none"
                                  stroke="{{ $type === 'preference' ? '#10b981' : ($type === 'analytics' ? '#f59e0b' : '#8b5cf6') }}"
                                  stroke-width="2"
                                  stroke-dasharray="{{ $stats['total_consents'] > 0 ? ($count / $stats['total_consents']) * 100 : 0 }}, 100"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-2xl font-bold text-gray-900">{{ $stats['total_consents'] > 0 ? round(($count / $stats['total_consents']) * 100) : 0 }}%</span>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 capitalize">{{ $type }}</h3>
                    <p class="text-sm text-gray-600">{{ number_format($count) }} consents</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Consent Trends Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Consent Trends</h2>
                <div class="text-sm text-gray-500">
                    Daily consents over the last 7 days
                </div>
            </div>
            <div class="h-64">
                <canvas id="consentChart"></canvas>
            </div>
        </div>

        <!-- Recent Consents Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Recent Cookie Consents</h2>
                    <div class="text-sm text-gray-500">
                        Latest 20 consent records
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preferences</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Browser/Device</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consented At</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentConsents as $consent)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <i class="fas fa-user-secret text-gray-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            Anonymous User
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            IP: {{ $consent->ip_address ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $consent->ip_address ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">
                                    @if($consent->latitude && $consent->longitude)
                                        <div class="font-medium text-gray-900">
                                            <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>
                                            {{ number_format($consent->latitude, 6) }}, {{ number_format($consent->longitude, 6) }}
                                        </div>
                                        @if($consent->city || $consent->country)
                                            <div class="text-gray-500 text-xs">
                                                {{ collect([$consent->city, $consent->region, $consent->country])->filter()->implode(', ') }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-gray-400">Not available</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    @if(($consent->consent_data['preference'] ?? false))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Essential
                                        </span>
                                    @endif
                                    @if(($consent->consent_data['analytics'] ?? false))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Analytics
                                        </span>
                                    @endif
                                    @if(($consent->consent_data['consent'] ?? false))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Consent
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $consent->user_agent }}">
                                {{ Str::limit($consent->user_agent, 50) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $consent->consented_at->format('M d, Y H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No consent records found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}" src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Refresh button handler
    document.getElementById('refreshButton').addEventListener('click', function() {
        location.reload();
    });

    // Consent trends chart
    const ctx = document.getElementById('consentChart').getContext('2d');
    const consentData = {!! json_encode($consentTrends) !!};

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: consentData.map(item => item.date),
            datasets: [{
                label: 'Daily Consents',
                data: consentData.map(item => item.consents),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>
@endsection