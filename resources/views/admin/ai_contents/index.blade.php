@extends('layouts.admin')

@section('title', 'AI Generated Contents')
@section('page-title', 'AI Tutor Contents')
@section('page-description', 'View and manage contents generated on demand by the AI Learning Agent')

@push('styles')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        .content-table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
        }

        .content-table thead {
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .content-table th {
            padding: 14px 18px;
            text-align: left;
            font-size: 0.8rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .content-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background-color 0.15s ease;
        }

        .content-table tbody tr:hover {
            background: #f8fafc;
        }

        .content-table td {
            padding: 14px 18px;
            vertical-align: middle;
            font-size: 0.875rem;
            color: #334155;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e40af;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
            margin-top: 4px;
        }

        .filter-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
        }

        .filter-tab {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .filter-tab:hover {
            color: #1e40af;
            background: #eff6ff;
        }

        .filter-tab.active {
            color: #1e40af;
            background: #dbeafe;
            font-weight: 600;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            gap: 12px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 220px;
            max-width: 360px;
        }

        .search-box input {
            width: 100%;
            padding: 8px 12px 8px 36px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            font-size: 0.875rem;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .toolbar-select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            font-size: 0.875rem;
            background: white;
            color: #334155;
        }
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">

        <!-- AI Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ number_format($stats['total_ai_videos'] ?? 0) }}</div>
                <div class="stat-label">AI Generated Videos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ number_format($stats['total_ai_quizzes'] ?? 0) }}</div>
                <div class="stat-label">AI Generated Quizzes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ number_format($stats['total_ai_requests'] ?? 0) }}</div>
                <div class="stat-label">Total AI Queries</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-green-600">{{ number_format($stats['successful_requests'] ?? 0) }}</div>
                <div class="stat-label">Successful AI Generations</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-blue-600">{{ number_format($stats['total_ai_views'] ?? 0) }}</div>
                <div class="stat-label">Total AI Views</div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="{{ route('admin.ai-contents.index', ['q' => $query, 'sort' => $sort]) }}"
                class="filter-tab {{ $type === 'all' ? 'active' : '' }}">All AI Content</a>
            <a href="{{ route('admin.ai-contents.index', ['q' => $query, 'type' => 'videos', 'sort' => $sort]) }}"
                class="filter-tab {{ $type === 'videos' ? 'active' : '' }}">AI Videos</a>
            <a href="{{ route('admin.ai-contents.index', ['q' => $query, 'type' => 'quizzes', 'sort' => $sort]) }}"
                class="filter-tab {{ $type === 'quizzes' ? 'active' : '' }}">AI Quizzes</a>
        </div>

        <!-- Clean Table Container -->
        <div class="content-table-container">
            <!-- Toolbar -->
            <div class="toolbar">
                <form method="GET" action="{{ route('admin.ai-contents.index') }}" class="flex items-center gap-3 flex-wrap flex-1">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="q" placeholder="Search prompt or title..." value="{{ $query }}">
                    </div>

                    <select name="level_group" onchange="this.form.submit()" class="toolbar-select">
                        <option value="">All Grade Levels</option>
                        @foreach($levelGroups as $group)
                            <option value="{{ $group->slug }}" {{ request('level_group') === $group->slug ? 'selected' : '' }}>
                                {{ $group->title }}
                            </option>
                        @endforeach
                    </select>

                    <select name="sort" onchange="this.form.submit()" class="toolbar-select">
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="most_viewed" {{ $sort === 'most_viewed' ? 'selected' : '' }}>Most Viewed</option>
                    </select>
                </form>
            </div>

            <!-- Simplified Clean 4-Column Table -->
            <div class="overflow-x-auto">
                <table class="content-table">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Original Prompt / Content</th>
                            <th style="width: 25%;">User</th>
                            <th style="width: 15%;">Date Created</th>
                            <th style="width: 15%; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contents as $content)
                            <tr>
                                <td>
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 {{ $content->content_type === 'video' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' }}">
                                            <i class="fas {{ $content->content_type === 'video' ? 'fa-video' : 'fa-question-circle' }} text-base"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-semibold text-gray-900 flex items-center gap-2">
                                                <a href="{{ route('admin.ai-contents.show', ['id' => $content->id, 'type' => $content->content_type]) }}" class="hover:text-blue-600 transition-colors">
                                                    {{ $content->title }}
                                                </a>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                    <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                    </svg>
                                                    AI {{ ucfirst($content->content_type) }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1 italic">
                                                @if(isset($content->agent_query) && $content->agent_query)
                                                    Prompt: "{{ Str::limit($content->agent_query, 80) }}"
                                                @else
                                                    Prompt: "{{ Str::limit($content->title, 80) }}"
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs">
                                            {{ strtoupper(substr($content->uploader_name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 text-sm">{{ $content->uploader_name }}</div>
                                            <div class="text-xs text-gray-400">{{ $content->uploader_email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-xs font-medium text-gray-600">{{ $content->published_date }}</span>
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.ai-contents.show', ['id' => $content->id, 'type' => $content->content_type]) }}" 
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-100 transition-colors" title="View Full AI Details">
                                            <i class="fas fa-eye"></i>
                                            <span>View</span>
                                        </a>
                                        @if($content->content_type === 'video')
                                            <form action="{{ route('admin.contents.destroy', $content->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this AI-generated content?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-12 text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-robot text-4xl text-gray-300 mb-3"></i>
                                        <p class="text-base font-medium">No AI-generated content found.</p>
                                        <p class="text-xs text-gray-400 mt-1">When users interact with the AI Tutor, generated lessons and quizzes will appear here.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($contents->hasPages())
                <div class="p-4 border-t border-gray-200">
                    {{ $contents->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
