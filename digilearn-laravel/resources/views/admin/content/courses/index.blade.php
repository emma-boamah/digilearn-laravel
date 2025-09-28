@extends('layouts.admin')

@section('title', 'Manage Courses - Admin Dashboard')
@section('page-title', 'Course Management')
@section('page-description', 'Create and manage complete courses with videos, documents, and quizzes')

@section('content')
<div class="course-management-container">
     Statistics Dashboard 
    <div class="stats-grid">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon-wrapper stat-icon-primary">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Courses</div>
                <div class="stat-value">{{ $totalCourses }}</div>
            </div>
            <div class="stat-trend stat-trend-up">
                <i class="fas fa-arrow-up"></i>
                <span>+12%</span>
            </div>
        </div>

        <div class="stat-card stat-card-success">
            <div class="stat-icon-wrapper stat-icon-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Published</div>
                <div class="stat-value">{{ $publishedCourses }}</div>
            </div>
            <div class="stat-trend stat-trend-up">
                <i class="fas fa-arrow-up"></i>
                <span>+8%</span>
            </div>
        </div>

        <div class="stat-card stat-card-warning">
            <div class="stat-icon-wrapper stat-icon-warning">
                <i class="fas fa-edit"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Draft</div>
                <div class="stat-value">{{ $draftCourses }}</div>
            </div>
            <div class="stat-trend stat-trend-neutral">
                <i class="fas fa-minus"></i>
                <span>0%</span>
            </div>
        </div>

        <div class="stat-card stat-card-featured">
            <div class="stat-icon-wrapper stat-icon-featured">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Featured</div>
                <div class="stat-value">{{ $featuredCourses }}</div>
            </div>
            <div class="stat-trend stat-trend-up">
                <i class="fas fa-arrow-up"></i>
                <span>+5%</span>
            </div>
        </div>
    </div>

     Main Content Card 
    <div class="main-content-card">
         Header Actions 
        <div class="content-header">
            <div class="header-actions">
                <a href="{{ route('admin.content.courses.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>Create New Course</span>
                </a>
                <button type="button" class="btn btn-secondary" onclick="exportCourses()">
                    <i class="fas fa-download"></i>
                    <span>Export</span>
                </button>
            </div>

             Advanced Filters 
            <form method="GET" action="{{ route('admin.content.courses.index') }}" class="filters-form">
                <div class="filters-row">
                    <div class="filter-group">
                        <select name="grade_level" class="form-select">
                            <option value="">All Grade Levels</option>
                            @foreach($gradeLevels as $level)
                                <option value="{{ $level }}" {{ request('grade_level') == $level ? 'selected' : '' }}>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <select name="subject" class="form-select">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject }}" {{ request('subject') == $subject ? 'selected' : '' }}>{{ $subject }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <select name="is_featured" class="form-select">
                            <option value="">All Courses</option>
                            <option value="1" {{ request('is_featured') == '1' ? 'selected' : '' }}>Featured Only</option>
                            <option value="0" {{ request('is_featured') == '0' ? 'selected' : '' }}>Non-Featured</option>
                        </select>
                    </div>
                </div>

                <div class="search-row">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search courses by title or description..." class="search-input">
                    </div>
                    <button type="submit" class="btn btn-search">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('admin.content.courses.index') }}" class="btn btn-reset">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>

         Courses Table 
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="sortable" data-sort="title">
                            <span>Course</span>
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th class="sortable" data-sort="grade_level">
                            <span>Grade & Subject</span>
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th>Content</th>
                        <th class="sortable" data-sort="status">
                            <span>Status</span>
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th class="sortable" data-sort="price">
                            <span>Price</span>
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th class="sortable" data-sort="created_at">
                            <span>Created</span>
                            <i class="fas fa-sort sort-icon"></i>
                        </th>
                        <th class="actions-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr class="table-row" data-course-id="{{ $course->id }}">
                        <td class="course-info-cell">
                            <div class="course-info">
                                <div class="course-thumbnail">
                                    @if($course->thumbnail_path)
                                        <img src="{{ $course->getThumbnailUrl() }}" alt="{{ $course->title }}" class="thumbnail-image">
                                    @else
                                        <div class="thumbnail-placeholder">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="course-details">
                                    <div class="course-title">{{ $course->title }}</div>
                                    <div class="course-description">{{ Str::limit($course->description, 60) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="grade-subject-cell">
                            <div class="grade-level">{{ $course->grade_level ?? 'N/A' }}</div>
                            <div class="subject">{{ $course->subject ?? 'N/A' }}</div>
                        </td>
                        <td class="content-cell">
                            <div class="content-badges">
                                <span class="content-badge content-badge-video">
                                    <i class="fas fa-video"></i>
                                    <span>{{ $course->videos()->count() }}</span>
                                </span>
                                <span class="content-badge content-badge-document">
                                    <i class="fas fa-file-alt"></i>
                                    <span>{{ $course->documents()->count() }}</span>
                                </span>
                                <span class="content-badge content-badge-quiz">
                                    <i class="fas fa-question-circle"></i>
                                    <span>{{ $course->quizzes()->count() }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="status-cell">
                            <div class="status-badges">
                                @if($course->status === 'published')
                                    <span class="status-badge status-published">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Published</span>
                                    </span>
                                @elseif($course->status === 'draft')
                                    <span class="status-badge status-draft">
                                        <i class="fas fa-edit"></i>
                                        <span>Draft</span>
                                    </span>
                                @else
                                    <span class="status-badge status-archived">
                                        <i class="fas fa-archive"></i>
                                        <span>Archived</span>
                                    </span>
                                @endif
                                @if($course->is_featured)
                                    <span class="status-badge status-featured">
                                        <i class="fas fa-star"></i>
                                        <span>Featured</span>
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="price-cell">
                            <div class="price-value">{{ $course->getFormattedPrice() }}</div>
                        </td>
                        <td class="date-cell">
                            <div class="date-value">{{ $course->created_at->format('M d, Y') }}</div>
                            <div class="time-value">{{ $course->created_at->format('H:i') }}</div>
                        </td>
                        <td class="actions-cell">
                            <div class="action-buttons">
                                <a href="{{ route('admin.content.courses.show', $course) }}" class="action-btn action-btn-view" title="View Course">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.content.courses.edit', $course) }}" class="action-btn action-btn-edit" title="Edit Course">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.content.courses.toggle-feature', $course) }}" class="action-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="action-btn action-btn-feature {{ $course->is_featured ? 'featured' : '' }}" title="{{ $course->is_featured ? 'Unfeature' : 'Feature' }}">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </form>
                                <button type="button" class="action-btn action-btn-delete" onclick="deleteCourse({{ $course->id }})" title="Delete Course">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            <div class="empty-state-content">
                                <div class="empty-state-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="empty-state-title">No courses found</div>
                                <div class="empty-state-description">Get started by creating your first course</div>
                                <a href="{{ route('admin.content.courses.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    <span>Create Course</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

         Pagination 
        @if($courses->hasPages())
            <div class="pagination-wrapper">
                {{ $courses->links() }}
            </div>
        @endif
    </div>
</div>

 Delete Confirmation Modal 
<div id="deleteModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Delete Course</h3>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <p>Are you sure you want to delete this course? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteForm" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete Course</button>
            </form>
        </div>
    </div>
</div>

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .course-management-container {
        padding: 1.5rem;
        max-width: 100%;
        margin: 0 auto;
    }

    /* Statistics Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #E11E2D, #2677B8);
    }

    .stat-card-primary::before { background: #2677B8; }
    .stat-card-success::before { background: #10b981; }
    .stat-card-warning::before { background: #f59e0b; }
    .stat-card-featured::before { background: #E11E2D; }

    .stat-icon-wrapper {
        width: 3rem;
        height: 3rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .stat-icon-primary { background: #dbeafe; color: #2677B8; }
    .stat-icon-success { background: #d1fae5; color: #10b981; }
    .stat-icon-warning { background: #fef3c7; color: #f59e0b; }
    .stat-icon-featured { background: #fce7e7; color: #E11E2D; }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #111827;
        line-height: 1;
    }

    .stat-trend {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
    }

    .stat-trend-up { background: #d1fae5; color: #10b981; }
    .stat-trend-neutral { background: #f3f4f6; color: #6b7280; }

    /* Main Content Card */
    .main-content-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }

    .content-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background: #2677B8;
        color: #ffffff;
    }

    .btn-primary:hover {
        background: #1e5a8a;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #ffffff;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    .btn-search {
        background: #E11E2D;
        color: #ffffff;
        padding: 0.75rem;
    }

    .btn-search:hover {
        background: #c41e2a;
    }

    .btn-reset {
        background: #6b7280;
        color: #ffffff;
        padding: 0.75rem;
    }

    .btn-reset:hover {
        background: #4b5563;
    }

    .btn-danger {
        background: #E11E2D;
        color: #ffffff;
    }

    .btn-danger:hover {
        background: #c41e2a;
    }

    /* Filters */
    .filters-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .filters-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .search-row {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .search-input-wrapper {
        position: relative;
        flex: 1;
    }

    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.875rem;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 0.75rem 0.75rem 2.5rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;
        background: #ffffff;
    }

    .search-input:focus {
        outline: none;
        border-color: #2677B8;
        box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.1);
    }

    .form-select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;
        background: #ffffff;
    }

    .form-select:focus {
        outline: none;
        border-color: #2677B8;
        box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.1);
    }

    /* Table */
    .table-container {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: #f9fafb;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
    }

    .sortable {
        cursor: pointer;
        user-select: none;
        position: relative;
    }

    .sortable:hover {
        background: #f3f4f6;
    }

    .sort-icon {
        margin-left: 0.5rem;
        opacity: 0.5;
        font-size: 0.75rem;
    }

    .data-table td {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .table-row:hover {
        background: #f9fafb;
    }

    /* Course Info Cell */
    .course-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .course-thumbnail {
        width: 3rem;
        height: 3rem;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .thumbnail-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .thumbnail-placeholder {
        width: 100%;
        height: 100%;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 1.25rem;
    }

    .course-details {
        min-width: 0;
    }

    .course-title {
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.25rem;
        line-height: 1.4;
    }

    .course-description {
        font-size: 0.875rem;
        color: #6b7280;
        line-height: 1.4;
    }

    /* Grade Subject Cell */
    .grade-level {
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.25rem;
    }

    .subject {
        font-size: 0.875rem;
        color: #6b7280;
    }

    /* Content Badges */
    .content-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .content-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .content-badge-video {
        background: #dbeafe;
        color: #2677B8;
    }

    .content-badge-document {
        background: #d1fae5;
        color: #10b981;
    }

    .content-badge-quiz {
        background: #e0e7ff;
        color: #7c3aed;
    }

    /* Status Badges */
    .status-badges {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        width: fit-content;
    }

    .status-published {
        background: #d1fae5;
        color: #10b981;
    }

    .status-draft {
        background: #fef3c7;
        color: #f59e0b;
    }

    .status-archived {
        background: #f3f4f6;
        color: #6b7280;
    }

    .status-featured {
        background: #fce7e7;
        color: #E11E2D;
    }

    /* Price and Date Cells */
    .price-value {
        font-weight: 600;
        color: #111827;
        font-size: 0.875rem;
    }

    .date-value {
        font-weight: 500;
        color: #111827;
        font-size: 0.875rem;
    }

    .time-value {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.125rem;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        width: 2rem;
        height: 2rem;
        border-radius: 6px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }

    .action-btn-view {
        background: #dbeafe;
        color: #2677B8;
    }

    .action-btn-view:hover {
        background: #bfdbfe;
    }

    .action-btn-edit {
        background: #e0e7ff;
        color: #7c3aed;
    }

    .action-btn-edit:hover {
        background: #c7d2fe;
    }

    .action-btn-feature {
        background: #f3f4f6;
        color: #9ca3af;
    }

    .action-btn-feature:hover {
        background: #e5e7eb;
    }

    .action-btn-feature.featured {
        background: #fef3c7;
        color: #f59e0b;
    }

    .action-btn-delete {
        background: #fce7e7;
        color: #E11E2D;
    }

    .action-btn-delete:hover {
        background: #fca5a5;
    }

    .action-form {
        display: inline;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }

    .empty-state-content {
        max-width: 400px;
        margin: 0 auto;
    }

    .empty-state-icon {
        width: 4rem;
        height: 4rem;
        background: #f3f4f6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
        color: #9ca3af;
    }

    .empty-state-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.5rem;
    }

    .empty-state-description {
        color: #6b7280;
        margin-bottom: 1.5rem;
    }

    /* Modal */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: #ffffff;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
    }

    .modal-close {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 4px;
    }

    .modal-close:hover {
        background: #f3f4f6;
        color: #6b7280;
    }

    .modal-body {
        padding: 1.5rem;
        text-align: center;
    }

    .warning-icon {
        width: 3rem;
        height: 3rem;
        background: #fef3c7;
        color: #f59e0b;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.25rem;
    }

    .modal-footer {
        padding: 1.5rem;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    /* Pagination */
    .pagination-wrapper {
        padding: 1.5rem;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .course-management-container {
            padding: 1rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .header-actions {
            flex-direction: column;
        }
        
        .filters-row {
            grid-template-columns: 1fr;
        }
        
        .search-row {
            flex-direction: column;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .course-info {
            flex-direction: column;
            align-items: flex-start;
            text-align: center;
        }
    }
</style>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
// Delete course functionality
function deleteCourse(courseId) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');
    form.action = `/admin/content/courses/${courseId}`;
    modal.classList.add('active');
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('active');
}

// Export functionality
function exportCourses() {
    // Implementation for exporting courses
    console.log('Exporting courses...');
}

// Table sorting
document.querySelectorAll('.sortable').forEach(header => {
    header.addEventListener('click', function() {
        const sortField = this.dataset.sort;
        const currentUrl = new URL(window.location);
        const currentSort = currentUrl.searchParams.get('sort');
        const currentDirection = currentUrl.searchParams.get('direction');
        
        let newDirection = 'asc';
        if (currentSort === sortField && currentDirection === 'asc') {
            newDirection = 'desc';
        }
        
        currentUrl.searchParams.set('sort', sortField);
        currentUrl.searchParams.set('direction', newDirection);
        
        window.location.href = currentUrl.toString();
    });
});

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endsection
