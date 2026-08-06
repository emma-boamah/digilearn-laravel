@extends('layouts.admin')

@section('title', 'Tutor Verification & Applications')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tutor Applications</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Review and verify tutor applications before granting active platform privileges.</p>
        </div>
        <div class="flex gap-3">
            <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-3 py-1.5 rounded-full flex items-center gap-1">
                <i class="fas fa-clock"></i> {{ $pendingCount }} Pending Verification
            </span>
            <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-3 py-1.5 rounded-full flex items-center gap-1">
                <i class="fas fa-check-circle"></i> {{ $approvedCount }} Approved Tutors
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-lg mb-6 flex items-center">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div class="bg-blue-100 border border-blue-300 text-blue-800 px-4 py-3 rounded-lg mb-6 flex items-center">
            <i class="fas fa-info-circle mr-2"></i> {{ session('info') }}
        </div>
    @endif

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700 mb-6 flex flex-wrap justify-between items-center gap-4">
        <div class="flex gap-2">
            <a href="{{ route('admin.tutors.index') }}" 
               class="px-4 py-2 text-sm font-medium rounded-lg {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
               All Applications
            </a>
            <a href="{{ route('admin.tutors.index', ['status' => 'pending']) }}" 
               class="px-4 py-2 text-sm font-medium rounded-lg {{ request('status') === 'pending' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
               Pending Only
            </a>
            <a href="{{ route('admin.tutors.index', ['status' => 'approved']) }}" 
               class="px-4 py-2 text-sm font-medium rounded-lg {{ request('status') === 'approved' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
               Approved Only
            </a>
        </div>

        <form method="GET" action="{{ route('admin.tutors.index') }}" class="flex gap-2">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..." 
                   class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition">
                Search
            </button>
        </form>
    </div>

    <!-- Tutors Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs font-semibold uppercase border-b border-gray-200 dark:border-gray-700">
                    <th class="p-4">Applicant</th>
                    <th class="p-4">Rate & Payout</th>
                    <th class="p-4">Submitted Date</th>
                    <th class="p-4">Verification Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                @forelse($tutors as $tutor)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                                    {{ strtoupper(substr($tutor->user->name ?? 'T', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $tutor->user->name ?? 'Unknown User' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $tutor->user->email ?? 'No email' }}</div>
                                    <div class="text-xs text-blue-600 dark:text-blue-400 mt-0.5">{{ $tutor->tagline ?? 'No tagline' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $tutor->rate_range }}/hr</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase flex items-center gap-1 mt-0.5">
                                <span>{{ $tutor->payout_method ?? 'Not set' }}</span>
                                @if($tutor->tutorSubjects && $tutor->tutorSubjects->count() > 0)
                                    <span class="text-gray-300 dark:text-gray-600">•</span>
                                    <span class="text-blue-600 dark:text-blue-400 font-medium normal-case">{{ $tutor->tutorSubjects->count() }} Subject(s)</span>
                                @endif
                            </div>
                        </td>
                        <td class="p-4 text-gray-600 dark:text-gray-300">
                            {{ $tutor->created_at ? $tutor->created_at->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="p-4">
                            @if($tutor->is_approved)
                                <span class="bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-400 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    Approved
                                </span>
                            @else
                                <span class="bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-400 text-xs font-semibold px-2.5 py-1 rounded-full">
                                    Pending Review
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.tutors.show', $tutor->id) }}" 
                                   class="px-3 py-1.5 bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 text-xs font-semibold rounded-lg hover:bg-blue-100 transition">
                                   Inspect Profile
                                </a>
                                @if(!$tutor->is_approved)
                                    <form method="POST" action="{{ route('admin.tutors.approve', $tutor->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 transition">
                                            Approve
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500 dark:text-gray-400">
                            No tutor applications found matching criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $tutors->links() }}
        </div>
    </div>
</div>
@endsection
