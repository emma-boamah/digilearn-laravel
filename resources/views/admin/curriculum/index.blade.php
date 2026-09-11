@extends('layouts.admin')

@section('title', 'Curriculum Management')

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-6" x-data="{ uploadModalOpen: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                <i class="fas fa-graduation-cap text-blue-600"></i>
                Curriculum Management
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Upload national curricula (GES, NaCCA, WAEC), extract learning hierarchies, and power the Guided Learning system.
            </p>
        </div>
        <div>
            <button @click="uploadModalOpen = true"
                class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-xl shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-cloud-upload-alt mr-2"></i>
                Upload Curriculum PDF
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Curricula</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $curricula->total() }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600">
                    <i class="fas fa-book-reader text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Approved & Active</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ \App\Models\Curriculum::where('is_approved', true)->count() }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600">
                    <i class="fas fa-check-circle text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending Review</p>
                    <p class="text-2xl font-bold text-amber-500 mt-1">{{ \App\Models\Curriculum::where('extraction_status', 'extracted')->where('is_approved', false)->count() }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-500">
                    <i class="fas fa-clock text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Indicators</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ \App\Models\CurriculumIndicator::count() }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600">
                    <i class="fas fa-list-check text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search (Modern, comfortable dropdowns & inputs) -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.curriculum.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <!-- Subject Dropdown -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Subject</label>
                <div class="relative">
                    <select name="subject_id" class="w-full h-11 pl-4 pr-10 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 shadow-sm appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all cursor-pointer">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Education Body -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Education Body</label>
                <input type="text" name="education_body" value="{{ request('education_body') }}" placeholder="e.g. GES, NaCCA, WAEC"
                    class="w-full h-11 px-4 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
            </div>

            <!-- Extraction Status Dropdown -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Extraction Status</label>
                <div class="relative">
                    <select name="status" class="w-full h-11 pl-4 pr-10 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 shadow-sm appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all cursor-pointer">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="extracted" {{ request('status') == 'extracted' ? 'selected' : '' }}>Extracted</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Filter / Reset Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 h-11 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-filter text-xs"></i>
                    Filter
                </button>
                <a href="{{ route('admin.curriculum.index') }}" class="h-11 px-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-xl transition-all flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Curriculum Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50/80 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Curriculum</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Subject & Grade</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Structure</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">AI Status</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Approval</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($curricula as $curriculum)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900 dark:text-white">{{ $curriculum->title }}</div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="px-2 py-0.5 text-xs rounded-md bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-semibold border border-blue-100 dark:border-blue-800">
                                    {{ $curriculum->education_body }}
                                </span>
                                @if($curriculum->academic_year)
                                    <span class="text-xs text-gray-500 font-medium">{{ $curriculum->academic_year }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $curriculum->subject->name }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                {{ $curriculum->level->title ?? ($curriculum->levelGroup->title ?? 'All Grades / Multi-grade') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 dark:text-white font-medium">
                                {{ $curriculum->strands_count }} Strands
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $curriculum->totalIndicatorsCount() }} Learning Indicators
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($curriculum->extraction_status === 'extracted')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200">
                                    <i class="fas fa-check-circle mr-1.5"></i> Extracted
                                </span>
                            @elseif($curriculum->extraction_status === 'processing')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 animate-pulse">
                                    <i class="fas fa-spinner fa-spin mr-1.5"></i> Processing AI
                                </span>
                            @elseif($curriculum->extraction_status === 'failed')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 border border-red-200" title="{{ $curriculum->extraction_error }}">
                                    <i class="fas fa-exclamation-triangle mr-1.5"></i> Failed
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($curriculum->is_approved)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200">
                                    <i class="fas fa-shield-alt mr-1.5"></i> Approved
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200">
                                    Under Review
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.curriculum.show', $curriculum) }}" 
                               class="inline-flex items-center px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors border border-blue-100">
                                <i class="fas fa-eye mr-1.5"></i> Review & Edit
                            </a>

                            <form action="{{ route('admin.curriculum.destroy', $curriculum) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this curriculum?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 transition-colors">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-14 text-center text-gray-500">
                            <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <p class="text-base font-bold text-gray-900 dark:text-white">No curricula found</p>
                            <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">Upload your first GES, NaCCA, or WAEC curriculum PDF to extract the learning backbone for students.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($curricula->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $curricula->links() }}
        </div>
        @endif
    </div>

    <!-- Upload Modal with Transparent Backdrop (Curriculum page clearly visible behind it) -->
    <div x-show="uploadModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            
            <!-- Sleek Glassmorphism Backdrop: curriculum page remains visible behind it -->
            <div x-show="uploadModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/30 backdrop-blur-[2px] transition-all"
                 @click="uploadModalOpen = false"></div>

            <!-- Modal Content Box -->
            <div x-show="uploadModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="relative bg-white dark:bg-gray-800 rounded-3xl text-left overflow-hidden shadow-2xl border border-gray-200/80 dark:border-gray-700 w-full max-w-xl z-10 my-8">
                
                <form action="{{ route('admin.curriculum.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6 sm:p-7 space-y-5">
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 flex items-center justify-center text-sm">
                                    <i class="fas fa-file-pdf"></i>
                                </span>
                                Upload Subject Curriculum PDF
                            </h3>
                            <button type="button" @click="uploadModalOpen = false" class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center transition-colors">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                                Curriculum Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" required placeholder="e.g. NaCCA Mathematics Curriculum for Basic 7-9 (JHS 1-3)"
                                class="w-full h-11 px-4 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                                    Education Body <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="education_body" list="education-bodies" required placeholder="GES, NaCCA, WAEC..."
                                    class="w-full h-11 px-4 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                                <datalist id="education-bodies">
                                    <option value="GES (Ghana Education Service)">
                                    <option value="NaCCA (National Council for Curriculum & Assessment)">
                                    <option value="WAEC / WASSCE">
                                    <option value="Cambridge International">
                                </datalist>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Academic Year</label>
                                <input type="text" name="academic_year" placeholder="e.g. 2019 or 2024"
                                    class="w-full h-11 px-4 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                                    Subject <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="subject_id" required class="w-full h-11 pl-4 pr-10 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all cursor-pointer">
                                        <option value="">Select Subject</option>
                                        @foreach($subjects as $sub)
                                            <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Level Group (Optional)</label>
                                <div class="relative">
                                    <select name="level_group_id" class="w-full h-11 pl-4 pr-10 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all cursor-pointer">
                                        <option value="">Auto-Detect / Multi-Level</option>
                                        @foreach($levelGroups as $group)
                                            <option value="{{ $group->id }}">{{ $group->title }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-400">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                                Curriculum PDF File <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="curriculum_file" accept=".pdf" required
                                class="w-full text-sm file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-xl p-2 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white transition-all cursor-pointer">
                            <p class="text-[11px] text-gray-400 mt-1">Accepts official PDF curriculum documents up to 50MB.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Notes / Guidelines</label>
                            <textarea name="notes" rows="2" placeholder="Any specific instructions for this curriculum version..."
                                class="w-full p-3.5 text-sm rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all"></textarea>
                        </div>
                    </div>

                    <div class="bg-gray-50/80 dark:bg-gray-900/50 px-6 py-4 flex justify-end gap-3 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="uploadModalOpen = false" class="h-11 px-5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="h-11 px-6 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-sm font-semibold shadow-sm transition-all flex items-center gap-2">
                            <i class="fas fa-sparkles"></i>
                            Upload & Extract
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
