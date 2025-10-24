@extends('layouts.admin')

@section('title', 'Security Monitoring')
@section('page-title', 'Security Monitor')
@section('page-description', 'Monitor security events and system activities')

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .activity-item {
        transition: all 0.2s ease;
    }
    .activity-item:hover {
        background-color: #f8fafc;
        transform: translateX(4px);
    }
    .severity-critical { border-left: 4px solid #dc2626; }
    .severity-high { border-left: 4px solid #ea580c; }
    .severity-medium { border-left: 4px solid #ca8a04; }
    .severity-low { border-left: 4px solid #16a34a; }
    .severity-info { border-left: 4px solid #2563eb; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Security Monitor</h1>
                    <p class="text-gray-600">Monitor security events and system activities</p>
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
        <!-- Security Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Activities -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Activities</p>
                        <p class="text-3xl font-bold text-gray-900">{{ is_array($securityLogs) ? number_format(count($securityLogs)) : number_format($securityLogs->count()) }}</p>
                        <p class="text-sm text-green-600 mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>Last 24h
                        </p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-shield-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Failed Logins -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Failed Logins</p>
                        <p class="text-3xl font-bold text-gray-900">{{ is_array($failedLogins) ? number_format(count($failedLogins)) : number_format($failedLogins->count()) }}</p>
                        <p class="text-sm text-red-600 mt-1">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Requires attention
                        </p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-lock text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Suspicious Activities -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Suspicious Activities</p>
                        <p class="text-3xl font-bold text-gray-900">{{ is_array($suspiciousActivities) ? number_format(count($suspiciousActivities)) : number_format($suspiciousActivities->count()) }}</p>
                        <p class="text-sm text-orange-600 mt-1">
                            <i class="fas fa-eye mr-1"></i>Under monitoring
                        </p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-user-secret text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Active Threats -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Threats</p>
                        <p class="text-3xl font-bold text-gray-900">{{ is_array($suspiciousActivities) ? collect($suspiciousActivities)->where('risk', 'high')->count() + collect($suspiciousActivities)->where('risk', 'critical')->count() : $suspiciousActivities->where('risk', 'high')->count() + $suspiciousActivities->where('risk', 'critical')->count() }}</p>
                        <p class="text-sm text-gray-500 mt-1">
                            High & Critical
                        </p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Security Logs -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-gray-900">Security Logs</h2>
                            <div class="flex items-center space-x-2">
                                <select id="severityFilter" class="text-sm border border-gray-300 rounded-md px-3 py-1">
                                    <option value="">All Severities</option>
                                    <option value="critical">Critical</option>
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                    <option value="info">Info</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4" id="securityLogsContainer">
                            @foreach($securityLogs as $log)
                            <div class="activity-item flex items-start space-x-3 p-4 rounded-lg border border-gray-100 severity-{{ $log['level'] }}">
                                <div class="flex-shrink-0">
                                    @if($log['level'] === 'critical')
                                        <div class="bg-red-100 p-2 rounded-full">
                                            <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                                        </div>
                                    @elseif($log['level'] === 'warning')
                                        <div class="bg-orange-100 p-2 rounded-full">
                                            <i class="fas fa-exclamation-circle text-orange-600 text-sm"></i>
                                        </div>
                                    @elseif($log['level'] === 'info')
                                        <div class="bg-blue-100 p-2 rounded-full">
                                            <i class="fas fa-info text-blue-600 text-sm"></i>
                                        </div>
                                    @else
                                        <div class="bg-gray-100 p-2 rounded-full">
                                            <i class="fas fa-edit text-gray-600 text-sm"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900 font-medium">{{ $log['message'] }}</p>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                        <span><i class="fas fa-clock mr-1"></i>{{ $log['time'] }}</span>
                                        @if(isset($log['ip']))
                                            <span><i class="fas fa-globe mr-1"></i>{{ $log['ip'] }}</span>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            @if($log['level'] === 'critical') bg-red-100 text-red-800
                                            @elseif($log['level'] === 'warning') bg-orange-100 text-orange-800
                                            @elseif($log['level'] === 'info') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($log['level']) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Failed Logins & Suspicious Activities -->
            <div class="space-y-6">
                <!-- Failed Login Attempts -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Failed Login Attempts</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($failedLogins as $attempt)
                            <div class="flex items-start space-x-3 p-3 rounded-lg bg-red-50 border border-red-200">
                                <div class="flex-shrink-0">
                                    <div class="bg-red-100 p-2 rounded-full">
                                        <i class="fas fa-times text-red-600 text-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900 font-medium">{{ $attempt['email'] }}</p>
                                    <div class="flex items-center space-x-4 mt-1 text-xs text-gray-500">
                                        <span><i class="fas fa-clock mr-1"></i>{{ $attempt['last_attempt'] }}</span>
                                        <span><i class="fas fa-globe mr-1"></i>{{ $attempt['ip'] }}</span>
                                        <span class="bg-red-100 text-red-800 px-2 py-0.5 rounded-full text-xs font-medium">
                                            {{ $attempt['attempts'] }} attempts
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Suspicious Activities -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Suspicious Activities</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($suspiciousActivities as $activity)
                            <div class="flex items-start space-x-3 p-3 rounded-lg border border-gray-200
                                @if($activity['risk'] === 'high') bg-red-50 border-red-200
                                @elseif($activity['risk'] === 'medium') bg-orange-50 border-orange-200
                                @else bg-yellow-50 border-yellow-200 @endif">
                                <div class="flex-shrink-0">
                                    @if($activity['risk'] === 'high')
                                        <div class="bg-red-100 p-2 rounded-full">
                                            <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                                        </div>
                                    @elseif($activity['risk'] === 'medium')
                                        <div class="bg-orange-100 p-2 rounded-full">
                                            <i class="fas fa-exclamation-circle text-orange-600 text-sm"></i>
                                        </div>
                                    @else
                                        <div class="bg-yellow-100 p-2 rounded-full">
                                            <i class="fas fa-eye text-yellow-600 text-sm"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900 font-medium">{{ $activity['type'] }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $activity['description'] }}</p>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-xs text-gray-500">{{ $activity['user'] }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            @if($activity['risk'] === 'high') bg-red-100 text-red-800
                                            @elseif($activity['risk'] === 'medium') bg-orange-100 text-orange-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ ucfirst($activity['risk']) }} Risk
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Security Actions</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <button class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors w-full text-left">
                                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-download text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Export Security Logs</p>
                                    <p class="text-xs text-gray-500">Download security events</p>
                                </div>
                            </button>
                            <button class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors w-full text-left">
                                <div class="bg-green-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-cog text-green-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Configure Alerts</p>
                                    <p class="text-xs text-gray-500">Set up security notifications</p>
                                </div>
                            </button>
                            <button class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors w-full text-left">
                                <div class="bg-red-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-ban text-red-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Block IP Addresses</p>
                                    <p class="text-xs text-gray-500">Manage blocked IPs</p>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Refresh button handler
    document.getElementById('refreshButton').addEventListener('click', function() {
        location.reload();
    });

    // Severity filter handler
    document.getElementById('severityFilter').addEventListener('change', function() {
        const selectedSeverity = this.value;
        const logItems = document.querySelectorAll('#securityLogsContainer .activity-item');

        logItems.forEach(item => {
            if (selectedSeverity === '' || item.classList.contains('severity-' + selectedSeverity)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Auto-refresh every 30 seconds
    setInterval(function() {
        // Only auto-refresh if the page is visible
        if (!document.hidden) {
            // You could implement AJAX refresh here
            // For now, we'll just update the timestamp
            const timestampElement = document.querySelector('.text-sm.text-gray-500');
            if (timestampElement && timestampElement.textContent.includes('Last updated:')) {
                const now = new Date();
                const formatted = now.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
                timestampElement.textContent = 'Last updated: ' + formatted;
            }
        }
    }, 30000); // 30 seconds
</script>
@endsection