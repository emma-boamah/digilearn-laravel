@extends('schools.admin.layout')

@section('title', 'Dashboard')

@section('topbar-actions')
    <a href="{{ route('school.admin.users.invite') }}" class="sa-btn sa-btn-primary sa-btn-sm">
        <i class="fas fa-plus"></i> Invite User
    </a>
@endsection

@section('styles')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: box-shadow 0.2s ease;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon.blue {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }

        .stat-icon.green {
            background: rgba(5, 150, 105, 0.1);
            color: var(--success);
        }

        .stat-icon.orange {
            background: rgba(217, 119, 6, 0.1);
            color: var(--warning);
        }

        .stat-icon.red {
            background: rgba(225, 30, 45, 0.1);
            color: var(--accent);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        .quick-action-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            text-decoration: none;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.15s ease;
        }

        .quick-action-card:hover {
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
            transform: translateY(-2px);
        }

        .quick-action-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(37, 99, 235, 0.08);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .quick-action-label {
            font-weight: 500;
            font-size: 0.9rem;
        }

        .quick-action-desc {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Recent Users */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
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

        .user-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-cell-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            flex-shrink: 0;
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

        /* Subdomain Info */
        .subdomain-card {
            background: linear-gradient(135deg, var(--primary), #1e40af);
            border-radius: 12px;
            padding: 24px;
            color: #fff;
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .subdomain-url {
            font-size: 1rem;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 16px;
            border-radius: 8px;
            font-family: monospace;
        }

        @media (max-width: 768px) {
            .stat-grid {
                grid-template-columns: 1fr 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }

            .users-table {
                overflow-x: auto;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Subdomain Banner -->
    <div class="subdomain-card">
        <div>
            <div style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 6px;">Your School Portal</div>
            <div class="subdomain-url">{{ $school->subdomain }}.shoutoutgh.com</div>
        </div>
        <a href="{{ route('school.admin.settings') }}" class="sa-btn"
            style="background: rgba(255,255,255,0.2); color: #fff; border: 1px solid rgba(255,255,255,0.3);">
            <i class="fas fa-cog"></i> Customize
        </a>
    </div>

    <!-- Stats -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-user-graduate" style="font-size: 1.2rem;"></i>
            </div>
            <div>
                <div class="stat-value">{{ $totalStudents }}</div>
                <div class="stat-label">Students</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-chalkboard-teacher" style="font-size: 1.2rem;"></i>
            </div>
            <div>
                <div class="stat-value">{{ $totalTeachers }}</div>
                <div class="stat-label">Teachers</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="fas fa-tasks" style="font-size: 1.2rem;"></i>
            </div>
            <div>
                <div class="stat-value">{{ $totalQuizzesTaken }}</div>
                <div class="stat-label">Quizzes Taken</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-clock" style="font-size: 1.2rem;"></i>
            </div>
            <div>
                <div class="stat-value">{{ $learningTimeHours }}<span
                        style="font-size: 1rem; color: var(--text-muted);">h</span></div>
                <div class="stat-label">Total Learning Time</div>
            </div>
        </div>
    </div>

    <!-- Analytics Chart -->
    <div class="section-header" style="margin-top: 40px;">
        <h2 class="section-title">Student Performance (Avg Score)</h2>
        <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary);">
            Overall: {{ round($averageScore) }}%
        </div>
    </div>
    <div
        style="background: var(--bg-card); padding: 24px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 32px;">
        @if($subjectPerformance->count() > 0)
            <canvas id="performanceChart" height="80"></canvas>
        @else
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <i class="fas fa-chart-bar" style="font-size: 2rem; margin-bottom: 12px;"></i>
                <p>Not enough data yet. Analytics will appear once students start taking quizzes.</p>
            </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="section-header">
        <h2 class="section-title">Quick Actions</h2>
    </div>
    <div class="quick-actions">
        <a href="{{ route('school.admin.users.invite') }}" class="quick-action-card">
            <div class="quick-action-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <div>
                <div class="quick-action-label">Invite Teachers</div>
                <div class="quick-action-desc">Add new teaching staff</div>
            </div>
        </a>
        <a href="{{ route('school.admin.users.invite') }}" class="quick-action-card">
            <div class="quick-action-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div>
                <div class="quick-action-label">Add Students</div>
                <div class="quick-action-desc">Enroll new students</div>
            </div>
        </a>
        <a href="{{ route('school.admin.settings') }}" class="quick-action-card">
            <div class="quick-action-icon">
                <i class="fas fa-image"></i>
            </div>
            <div>
                <div class="quick-action-label">Upload Logo</div>
                <div class="quick-action-desc">Brand your portal</div>
            </div>
        </a>
    </div>

    <!-- Recent Users -->
    <div class="section-header">
        <h2 class="section-title">Recent Users</h2>
        <a href="{{ route('school.admin.users') }}" class="sa-btn sa-btn-outline sa-btn-sm">View All</a>
    </div>
    <div class="users-table">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentUsers as $member)
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-cell-avatar">{{ substr($member->name, 0, 1) }}</div>
                                <span>{{ $member->name }}</span>
                            </div>
                        </td>
                        <td>{{ $member->email }}</td>
                        <td>
                            @php $role = $member->roles->first()?->name ?? 'N/A'; @endphp
                            <span class="role-badge {{ $role }}">{{ ucfirst(str_replace('-', ' ', $role)) }}</span>
                        </td>
                        <td style="color: var(--text-muted);">{{ $member->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            No users yet. Start by inviting teachers and students.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    @if($subjectPerformance->count() > 0)
        <script nonce="{{ request()->attributes->get('csp_nonce') }}" defer
            src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            const ctx = document.getElementById('performanceChart').getContext('2d');
            const data = {
                labels: {!! json_encode($subjectPerformance->pluck('quiz_subject')) !!},
                datasets: [{
                    label: 'Average Score (%)',
                    data: {!! json_encode($subjectPerformance->pluck('average_score')->map(fn($v) => round($v))) !!},
                    backgroundColor: 'rgba(37, 99, 235, 0.2)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 2,
                    borderRadius: 4,
                    fill: true,
                    tension: 0.3
                }]
            };

            new Chart(ctx, {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        </script>
    @endif
@endsection