@php
    $currentTab = $activeTab ?? (
        request()->routeIs('admin.platform-settings*') ? 'payouts' :
        (request()->routeIs('admin.hero-banners*') ? 'hero' :
        (request()->routeIs('admin.level-groups*') ? 'levels' :
        (request()->routeIs('admin.pricing*') ? 'pricing' : 'payouts')))
    );
@endphp

<div class="mb-8 bg-white dark:bg-gray-800 rounded-2xl p-2 border border-slate-200 dark:border-gray-700 shadow-xs">
    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-gray-700 mb-2">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-base">
                <i class="fas fa-sliders-h"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Platform Settings & Configurations</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Manage global marketing banners, academic level tiers, pricing plans, and payout rules.</p>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex flex-wrap gap-2 p-1">
        <a href="{{ route('admin.platform-settings.index') }}" 
           class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-sm transition-all {{ $currentTab === 'payouts' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-700/60' }}">
            <i class="fas fa-hand-holding-usd text-sm"></i>
            <span>Tutor Commission & Payouts</span>
        </a>

        <a href="{{ route('admin.hero-banners.index') }}" 
           class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-sm transition-all {{ $currentTab === 'hero' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-700/60' }}">
            <i class="fas fa-images text-sm"></i>
            <span>Hero Banners</span>
        </a>

        <a href="{{ route('admin.level-groups.index') }}" 
           class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-sm transition-all {{ $currentTab === 'levels' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-700/60' }}">
            <i class="fas fa-layer-group text-sm"></i>
            <span>Level Groups</span>
        </a>

        <a href="{{ route('admin.pricing.index') }}" 
           class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl font-bold text-sm transition-all {{ $currentTab === 'pricing' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-700/60' }}">
            <i class="fas fa-dollar-sign text-sm"></i>
            <span>Pricing Plans</span>
        </a>
    </div>
</div>
