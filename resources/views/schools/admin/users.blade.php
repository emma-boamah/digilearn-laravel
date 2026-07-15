@extends('schools.admin.layout')

@section('title', 'Manage Users')

@section('topbar-actions')
    <a href="{{ route('school.admin.users.import') }}" class="sa-btn sa-btn-outline sa-btn-sm" style="background: var(--bg-card);">
        <i class="fas fa-file-csv"></i> Bulk Import
    </a>
    <a href="{{ route('school.admin.users.invite') }}" class="sa-btn sa-btn-primary sa-btn-sm">
        <i class="fas fa-plus"></i> Invite User
    </a>
@endsection

@section('styles')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        .filter-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .filter-input {
            padding: 8px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.9rem;
            background: var(--bg-card);
            font-family: inherit;
        }

        .filter-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .filter-select {
            padding: 8px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.9rem;
            background: var(--bg-card);
            cursor: pointer;
            font-family: inherit;
        }

        .users-table {
            width: 100%;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .users-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th {
            text-align: left;
            padding: 12px 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border);
            background: var(--bg);
        }

        .users-table td {
            padding: 14px 20px;
            font-size: 0.9rem;
            border-bottom: 1px solid var(--border);
        }

        .users-table tr:last-child td {
            border-bottom: none;
        }

        .users-table tr:hover td {
            background: rgba(37, 99, 235, 0.02);
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-cell-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            flex-shrink: 0;
        }

        .user-cell-name {
            font-weight: 500;
        }

        .user-cell-email {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .role-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .role-badge.teacher {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }

        .role-badge.student {
            background: rgba(5, 150, 105, 0.1);
            color: var(--success);
        }

        .role-badge.school-admin {
            background: rgba(217, 119, 6, 0.1);
            color: var(--warning);
        }

        .action-cell {
            display: flex;
            gap: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 16px;
            display: block;
        }

        /* Pagination */
        .pagination-wrapper {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        .pagination-wrapper nav span,
        .pagination-wrapper nav a {
            padding: 6px 12px;
            margin: 0 2px;
            border-radius: 6px;
            font-size: 0.85rem;
            text-decoration: none;
            border: 1px solid var(--border);
            color: var(--text);
        }

        .pagination-wrapper nav span[aria-current] {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }
    </style>
@endsection

@section('content')
    <!-- Filters -->
    <form method="GET" action="{{ route('school.admin.users') }}" class="filter-bar">
        <input type="text" name="search" class="filter-input" placeholder="Search by name or email..."
            value="{{ request('search') }}" style="flex: 1; min-width: 200px;">
        <select name="role" class="filter-select" onchange="this.form.submit()">
            <option value="">All Roles</option>
            <option value="school-admin" {{ request('role') === 'school-admin' ? 'selected' : '' }}>School Admin</option>
            <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
            <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Student</option>
        </select>
        <button type="submit" class="sa-btn sa-btn-outline sa-btn-sm">
            <i class="fas fa-search"></i> Search
        </button>
    </form>

    <!-- Users Table -->
    <div class="users-table">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $member)
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-cell-avatar">{{ substr($member->name, 0, 1) }}</div>
                                <div>
                                    <div class="user-cell-name">{{ $member->name }}</div>
                                    <div class="user-cell-email">{{ $member->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php $role = $member->roles->first()?->name ?? 'N/A'; @endphp
                            <span class="role-badge {{ $role }}">{{ ucfirst(str_replace('-', ' ', $role)) }}</span>
                        </td>
                        <td style="color: var(--text-muted);">{{ $member->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="action-cell" style="justify-content: flex-end;">
                                @if($member->id !== auth()->id())
                                    <form method="POST" action="{{ route('school.admin.users.remove', $member) }}"
                                        onsubmit="return confirm('Remove {{ $member->name }} from this school?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm">
                                            <i class="fas fa-user-minus"></i> Remove
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">You</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <p>No users found. Start by inviting teachers and students!</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="pagination-wrapper">
            {{ $users->links() }}
        </div>
    @endif
@endsection