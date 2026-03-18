@extends('layouts.admin')

@section('title', 'Cookie Statistics - Admin')
@section('page-title', 'Cookie Analytics')
@section('page-description', 'Monitor cookie consent and privacy compliance')

@section('content')
<div class="min-h-screen bg-[#f8fafc] py-8">
    <div class="max-w-[1440px] mx-auto px-2 sm:px-6 lg:px-8">

        <!-- Dashboard Header -->
        <div class="flex flex-wrap items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-cookie-bite text-blue-600"></i>
                    Cookie Overview
                </h1>
                <p class="text-slate-500 mt-1">Monitor consent logs and privacy compliance analytics</p>
            </div>
            <div class="flex items-center gap-3">
                <button
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-medium transition-all shadow-sm flex items-center gap-2">
                    Save Changes
                </button>
                <button
                    class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 px-5 py-2.5 rounded-lg font-medium transition-all shadow-sm">
                    Reset to Defaults
                </button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-5 mb-8">
            <!-- Consent Logs -->
            <div
                class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow text-center">
                <div class="w-12 h-12 bg-purple-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-history text-purple-600 text-lg"></i>
                </div>
                <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_consents']) }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Consent Logs</div>
            </div>

            <!-- Total Cookies -->
            <div
                class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow text-center">
                <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cookie text-amber-600 text-lg"></i>
                </div>
                <div class="text-2xl font-bold text-slate-900">{{ $stats['total_cookies'] ?? 11 }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1 mb-3">Total Cookies</div>
                <button
                    class="text-[10px] bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1 rounded uppercase font-bold transition-colors">Scan
                    Cookies</button>
            </div>

            <!-- Cookies Consent Accepted -->
            <div
                class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow text-center">
                <div
                    class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4 relative">
                    <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
                    <span
                        class="absolute -top-1 -right-1 bg-emerald-500 text-white text-[10px] rounded-full w-5 h-5 flex items-center justify-center border-2 border-white">✓</span>
                </div>
                <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['accepted_all']) }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Cookies Consent Accepted
                </div>
            </div>

            <!-- Cookies Consent Rejected -->
            <div
                class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow text-center">
                <div class="w-12 h-12 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-4 relative">
                    <i class="fas fa-times-circle text-rose-600 text-lg"></i>
                    <span
                        class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] rounded-full w-5 h-5 flex items-center justify-center border-2 border-white">✗</span>
                </div>
                <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['rejected_all']) }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Cookies Consent Rejected
                </div>
            </div>

            <!-- Terms Accepted -->
            <div
                class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow text-center">
                <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-file-contract text-blue-600 text-lg"></i>
                </div>
                <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['terms_accepted']) }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Terms & Conditions
                    Accepted</div>
            </div>

            <!-- Privacy Accepted -->
            <div
                class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow text-center">
                <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-shield text-slate-600 text-lg"></i>
                </div>
                <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['privacy_accepted']) }}</div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Privacy Policy Accepted
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Consent Trends -->
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-bold text-slate-800">Consent Trends</h2>
                    <span class="text-xs text-slate-400">Past 7 Days</span>
                </div>
                <div class="p-6">
                    <div class="h-[300px]">
                        <canvas id="consentChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Geo Traffic Map -->
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-bold text-slate-800">Global Traffic Distribution</h2>
                    <span class="text-xs text-slate-400">Live Heatmap</span>
                </div>
                <div class="p-6">
                    <div id="world-map" style="height: 400px; width: 100%; min-height: 400px;" class="relative overflow-hidden"></div>
                </div>
            </div>
        </div>

        <!-- Data Table Container -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 class="font-bold text-slate-800">Consent Logs <span
                            class="text-slate-400 ml-2 font-normal">Total: {{ number_format($stats['total_consents'])
                            }}</span></h2>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            Logs to load:
                            <select class="border border-slate-200 rounded px-2 py-1 bg-slate-50">
                                <option>50</option>
                                <option>100</option>
                                <option>200</option>
                            </select>
                        </div>
                        <button
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-md text-sm font-semibold transition-all shadow-sm">Load
                            Logs</button>
                        <button
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-md text-sm font-semibold transition-all shadow-sm">Export
                            CSV</button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th
                                class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-100">
                                Consent Type</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-100">
                                Status</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-100 text-center">
                                User ID</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-100">
                                IP Address</th>
                             <th
                                 class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-100">
                                 Country</th>
                             <th
                                 class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-100">
                                 Device/Browser</th>
                             <th
                                 class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-100">
                                Consent Info</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-100">
                                Page URL</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                Date Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentConsents as $consent)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-600 border-r border-slate-100">Cookies Consent</td>
                            <td class="px-6 py-4 border-r border-slate-100">
                                @php $isAccepted = $consent->consent_data['analytics'] ?? false; @endphp
                                <span
                                    class="px-3 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider {{ $isAccepted ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100' }}">
                                    {{ $isAccepted ? 'Accepted' : 'Declined' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 border-r border-slate-100 text-center">1</td>
                            <td class="px-6 py-4 text-sm text-blue-500 hover:underline border-r border-slate-100">
                                <a href="https://whois.com/whois/{{ $consent->ip_address }}" target="_blank">{{
                                    $consent->ip_address }}</a>
                            </td>
                             <td class="px-6 py-4 text-sm text-slate-600 border-r border-slate-100">
                                 <div class="flex items-center gap-2">
                                     <span class="font-medium">{{ $consent->country ?? 'Unknown' }}</span>
                                 </div>
                             </td>
                             <td class="px-6 py-4 text-sm text-slate-600 border-r border-slate-100">
                                 <div class="flex items-center gap-2">
                                     @php $browser = strtolower($consent->browser); @endphp
                                     <i class="fab fa-{{ in_array($browser, ['chrome', 'firefox', 'edge', 'safari', 'opera']) ? $browser : 'chrome' }} text-slate-400"></i>
                                     <span class="font-medium">{{ $consent->browser }}</span>
                                     <span class="mx-1.5 text-slate-300">|</span>
                                     @php $device = strtolower($consent->device); @endphp
                                     <i class="fas fa-{{ $device === 'mobile' ? 'mobile-alt' : ($device === 'tablet' ? 'tablet-alt' : 'desktop') }} text-slate-400"></i>
                                     <span class="text-xs text-slate-500">{{ $consent->device }}</span>
                                 </div>
                             </td>
                             <td
                                class="px-6 py-4 text-[11px] text-slate-500 border-r border-slate-100 leading-relaxed max-w-[200px]">
                                <div class="text-rose-500 font-bold mb-0.5">Necessary Cookies,</div>
                                <div class="text-rose-500 font-bold mb-0.5">Preferences Cookies,</div>
                                <div class="text-rose-500 font-bold">Marketing Cookies.</div>
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-blue-500 hover:underline border-r border-slate-100 max-w-[250px] truncate">
                                <a href="{{ $consent->page_url ?? '#' }}" target="_blank">{{ $consent->page_url ?? 'N/A'
                                    }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-700 font-medium">{{ $consent->consented_at->format('j M
                                    Y') }}</div>
                                <div class="text-[10px] text-slate-400 uppercase font-bold">{{
                                    $consent->consented_at->format('g:i a') }}</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap/dist/css/jsvectormap.min.css" />
<style>
    .jvm-container {
        width: 100% !important;
        height: 100% !important;
        border-radius: 0.75rem;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap/dist/maps/world.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function() {
        // Chart Initialization
        const ctx = document.getElementById('consentChart').getContext('2d');
        const consentTrendData = {!! json_encode($consentTrends) !!};
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: consentTrendData.map(d => d.date),
                datasets: [{
                    label: 'Consents',
                    data: consentTrendData.map(d => d.consents),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Map Initialization
        const countryData = {!! json_encode($countryBreakdown) !!};
        const mapData = {};
        
        // Mapping for common countries to ISO codes
        const commonCodes = { 
            'Ghana': 'GH', 'United States': 'US', 'United Kingdom': 'GB', 
            'France': 'FR', 'Nigeria': 'NG', 'Canada': 'CA', 'Germany': 'DE',
            'India': 'IN', 'China': 'CN', 'Japan': 'JP', 'Brazil': 'BR'
        };

        Object.keys(countryData).forEach(country => {
            const code = commonCodes[country] || country;
            mapData[code] = countryData[country];
        });

        const map = new jsVectorMap({
            selector: '#world-map',
            map: 'world',
            backgroundColor: 'transparent',
            draggable: true,
            zoomButtons: true,
            zoomOnScroll: false,
            bindResize: true,
            regionStyle: {
                initial: { fill: '#e2e8f0', stroke: '#fff', strokeWidth: 0.5 },
                hover: { fill: '#94a3b8' }
            },
            series: {
                regions: [{
                    values: mapData,
                    scale: ['#dcfce7', '#22c55e'],
                    normalizeFunction: 'polynomial'
                }]
            },
            onRegionTooltipShow(event, tooltip, code) {
                const count = mapData[code] || 0;
                const countryName = tooltip.text();
                tooltip.text(`<b>${countryName}</b><br/>Consents: ${count}`);
            }
        });

        // Force a resize calculation after a short delay
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 500);
    });
</script>
@endsection