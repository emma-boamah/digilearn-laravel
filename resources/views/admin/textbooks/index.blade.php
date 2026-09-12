@extends('layouts.admin')

@section('title', 'Textbook Management')

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-6" x-data="{ uploadModalOpen: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                <i class="fas fa-book-open text-blue-600"></i>
                Textbook Management
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Upload textbook PDFs, extract Tables of Contents with AI, and extract detailed lessons linked to curriculum indicators.
            </p>
        </div>
        <div>
            <button @click="uploadModalOpen = true"
                class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-xl shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-cloud-upload-alt mr-2"></i>
                Upload Textbook PDF
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50/80 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Book Details</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Subject & Curriculum</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">TOC Status</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Content Status</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Chapters</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($textbooks as $book)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900 dark:text-white">{{ $book->title }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                {{ $book->author ? 'By ' . $book->author : 'Author unspecified' }}
                                @if($book->publisher) • {{ $book->publisher }} @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $book->subject->name }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                @if($book->curriculum)
                                    <span class="text-blue-600 font-semibold">{{ $book->curriculum->title }}</span>
                                @else
                                    <span class="italic text-gray-400">No linked curriculum</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($book->toc_extraction_status === 'extracted')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200">
                                    <i class="fas fa-check mr-1.5"></i> TOC Extracted
                                </span>
                            @elseif($book->toc_extraction_status === 'processing')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 animate-pulse">
                                    <i class="fas fa-spinner fa-spin mr-1.5"></i> Reading TOC
                                </span>
                            @elseif($book->toc_extraction_status === 'failed')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 border border-red-200">
                                    Failed
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($book->content_extraction_status === 'extracted')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200">
                                    <i class="fas fa-check-double mr-1.5"></i> Extracted
                                </span>
                            @elseif($book->content_extraction_status === 'processing')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 animate-pulse">
                                    <i class="fas fa-spinner fa-spin mr-1.5"></i> Extracting Lessons
                                </span>
                            @else
                                <span class="text-xs text-gray-400">Not extracted</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $book->chapters_count }} Chapters
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.textbooks.show', $book) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors border border-blue-100">
                                <i class="fas fa-eye mr-1.5"></i> Review & Lessons
                            </a>

                            <form action="{{ route('admin.textbooks.destroy', $book) }}" method="POST" class="inline" onsubmit="return confirm('Delete this textbook?');">
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
                                <i class="fas fa-book-open"></i>
                            </div>
                            <p class="text-base font-bold text-gray-900 dark:text-white">No textbooks uploaded yet</p>
                            <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">Upload curriculum textbooks to extract lessons and map content directly to learning indicators.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($textbooks->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $textbooks->links() }}
        </div>
        @endif
    </div>

    <!-- Upload Modal with Transparent Backdrop (Textbook page clearly visible behind it) -->
    <div x-show="uploadModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            
            <!-- Sleek Glassmorphism Backdrop: textbook page remains visible behind it -->
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
                
                <form action="{{ route('admin.textbooks.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6 sm:p-7 space-y-5">
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center text-sm">
                                    <i class="fas fa-book"></i>
                                </span>
                                Upload Textbook PDF
                            </h3>
                            <button type="button" @click="uploadModalOpen = false" class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center transition-colors">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                                Textbook Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" required placeholder="e.g. Aki-Ola Mathematics for Junior High Schools"
                                class="w-full h-11 px-4 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Author</label>
                                <input type="text" name="author" placeholder="Author name"
                                    class="w-full h-11 px-4 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Publisher</label>
                                <input type="text" name="publisher" placeholder="Publisher name"
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
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Governing Curriculum</label>
                                <div class="relative">
                                    <select name="curriculum_id" class="w-full h-11 pl-4 pr-10 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all cursor-pointer">
                                        <option value="">Link Later</option>
                                        @foreach($curricula as $cur)
                                            <option value="{{ $cur->id }}">{{ $cur->title }} ({{ $cur->education_body }})</option>
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
                                Textbook PDF File <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="textbook_file" accept=".pdf" required
                                class="w-full text-sm file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-xl p-2 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white transition-all cursor-pointer">
                            <p class="text-[11px] text-gray-400 mt-1">Accepts textbook PDF documents up to 100MB.</p>
                        </div>
                    </div>

                    <div class="bg-gray-50/80 dark:bg-gray-900/50 px-6 py-4 flex justify-end gap-3 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="uploadModalOpen = false" class="h-11 px-5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="h-11 px-6 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl text-sm font-semibold shadow-sm transition-all flex items-center gap-2">
                            <i class="fas fa-sparkles"></i>
                            Upload & Extract TOC
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
