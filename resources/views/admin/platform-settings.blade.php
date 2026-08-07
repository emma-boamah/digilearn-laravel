@extends('layouts.admin')

@section('title', 'Tutor Commission & Payout Rules')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tutor Commission & Payout Rules</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Configure global tutor commission rates, minimum payout thresholds, and settlement clearance parameters.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-lg mb-6 flex items-center">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <form method="POST" action="{{ route('admin.platform-settings.update') }}" class="space-y-6">
            @csrf

            <!-- Commission Rate -->
            <div>
                <label for="tutor_commission_rate" class="block text-sm font-semibold text-gray-900 dark:text-white mb-1">
                    Tutor Platform Commission Rate (%)
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">The percentage retained by DigiLearn as escrow commission for every completed tutor booking.</p>
                <div class="relative max-w-xs">
                    <input type="number" step="0.1" min="0" max="100" id="tutor_commission_rate" name="tutor_commission_rate" value="{{ old('tutor_commission_rate', $commissionRate) }}" 
                           class="w-full pl-4 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                    <span class="absolute right-3 top-2.5 text-gray-500 font-bold">%</span>
                </div>
            </div>

            <hr class="border-gray-100 dark:border-gray-700">

            <!-- Minimum Withdrawal Threshold -->
            <div>
                <label for="min_payout_amount" class="block text-sm font-semibold text-gray-900 dark:text-white mb-1">
                    Minimum Payout Withdrawal Amount (GHS)
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">The minimum net earnings balance a tutor must accumulate before initiating a Paystack payout.</p>
                <div class="relative max-w-xs">
                    <span class="absolute left-3 top-2.5 text-gray-500 font-bold">GHS</span>
                    <input type="number" step="1" min="1" id="min_payout_amount" name="min_payout_amount" value="{{ old('min_payout_amount', $minPayout) }}" 
                           class="w-full pl-12 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>

            <hr class="border-gray-100 dark:border-gray-700">

            <!-- Payout Settlement Days -->
            <div>
                <label for="payout_processing_days" class="block text-sm font-semibold text-gray-900 dark:text-white mb-1">
                    Standard Payout Settlement Period (Days)
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Estimated number of business days for Paystack transfer clearance shown to tutors.</p>
                <div class="relative max-w-xs">
                    <input type="number" min="1" id="payout_processing_days" name="payout_processing_days" value="{{ old('payout_processing_days', $processingDays) }}" 
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-lg shadow transition">
                    <i class="fas fa-save mr-1"></i> Save Tutor Rules
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
