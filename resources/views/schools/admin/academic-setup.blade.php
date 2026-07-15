@extends('schools.admin.layout')

@section('title', 'Academic Setup')

@section('styles')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        .setup-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            align-items: start;
        }

        @media (max-width: 900px) {
            .setup-grid {
                grid-template-columns: 1fr;
            }
        }

        .setup-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
        }

        .setup-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .setup-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.9rem;
            font-family: inherit;
            transition: border-color 0.15s ease;
            background: var(--bg);
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .setup-list {
            margin-top: 24px;
            border-top: 1px solid var(--border);
            padding-top: 20px;
        }

        .setup-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: var(--bg);
            border-radius: 8px;
            margin-bottom: 8px;
            border: 1px solid var(--border);
        }

        .setup-item-title {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 2px;
        }

        .setup-item-meta {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .terms-list {
            margin-top: 10px;
            padding-left: 14px;
            border-left: 2px solid var(--border);
        }

        .term-item {
            font-size: 0.85rem;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
        }
    </style>
@endsection

@section('content')
    <div class="setup-grid">
        <!-- Years & Terms -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="setup-card">
                <div class="setup-title">Academic Years</div>
                <p class="setup-desc">Define the academic years for your school (e.g., 2026/2027).</p>

                <form method="POST" action="{{ route('school.admin.academic.year.store') }}"
                    style="background: rgba(37,99,235,0.03); padding: 16px; border-radius: 8px; border: 1px dashed rgba(37,99,235,0.2);">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Year Name</label>
                        <input type="text" name="year_name" class="form-input" placeholder="e.g. 2026/2027" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-group">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-input" required>
                        </div>
                    </div>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; margin-bottom: 16px;">
                        <input type="checkbox" name="is_active" value="1" checked> Set as Active Year
                    </label>
                    <button type="submit" class="sa-btn sa-btn-primary sa-btn-sm"
                        style="width: 100%; justify-content: center;">Add Academic Year</button>
                </form>

                <div class="setup-list">
                    @forelse($academicYears as $year)
                        <div class="setup-item" style="flex-direction: column; align-items: stretch; gap: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div class="setup-item-title">{{ $year->year_name }}</div>
                                    <div class="setup-item-meta">
                                        {{ \Carbon\Carbon::parse($year->start_date)->format('M d, Y') }} -
                                        {{ \Carbon\Carbon::parse($year->end_date)->format('M d, Y') }}</div>
                                </div>
                                @if($year->is_active)
                                    <span class="badge-active">Active</span>
                                @endif
                            </div>

                            @if($year->terms->count() > 0)
                                <div class="terms-list">
                                    @foreach($year->terms as $term)
                                        <div class="term-item">
                                            <span><i class="fas fa-calendar-alt" style="color: var(--primary); margin-right: 6px;"></i>
                                                {{ $term->term_name }}</span>
                                            <span style="color: var(--text-muted); font-size: 0.75rem;">
                                                {{ \Carbon\Carbon::parse($term->start_date)->format('M d') }} -
                                                {{ \Carbon\Carbon::parse($term->end_date)->format('M d') }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div style="font-size: 0.8rem; color: var(--warning); margin-top: 4px;">No terms defined for this
                                    year.</div>
                            @endif
                        </div>
                    @empty
                        <div style="text-align: center; font-size: 0.85rem; color: var(--text-muted);">No academic years defined
                            yet.</div>
                    @endforelse
                </div>
            </div>

            @if($academicYears->count() > 0)
                <div class="setup-card">
                    <div class="setup-title">Terms / Semesters</div>
                    <p class="setup-desc">Add terms (e.g. Term 1, Term 2) to an academic year.</p>

                    <form method="POST" action="{{ route('school.admin.academic.term.store') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year_id" class="form-select" required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->year_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Term Name</label>
                            <input type="text" name="term_name" class="form-input" placeholder="e.g. First Term" required>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div class="form-group">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-input" required>
                            </div>
                        </div>
                        <button type="submit" class="sa-btn sa-btn-outline sa-btn-sm"
                            style="width: 100%; justify-content: center;">Add Term</button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Classes -->
        <div class="setup-card">
            <div class="setup-title">School Classes</div>
            <p class="setup-desc">Create your school's classes and map them to standard educational levels.</p>

            <form method="POST" action="{{ route('school.admin.academic.class.store') }}"
                style="background: rgba(16, 185, 129, 0.03); padding: 16px; border-radius: 8px; border: 1px dashed rgba(16, 185, 129, 0.2);">
                @csrf
                <div class="form-group">
                    <label class="form-label">Class Name</label>
                    <input type="text" name="name" class="form-input" placeholder="e.g. JHS 1A" required>
                    <small style="color: var(--text-muted); font-size: 0.75rem;">Your internal name for the class.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Map to Global Level</label>
                    <select name="level_id" class="form-select" required>
                        <option value="">Select a level...</option>
                        @foreach($levels->groupBy('level_group_id') as $groupId => $groupLevels)
                            <optgroup label="Group {{ $groupId }}">
                                @foreach($groupLevels as $level)
                                    <option value="{{ $level->id }}">{{ $level->title }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <small style="color: var(--text-muted); font-size: 0.75rem;">This helps the system recommend appropriate
                        global content.</small>
                </div>
                <button type="submit" class="sa-btn sa-btn-sm"
                    style="width: 100%; justify-content: center; background: var(--success); color: white; border: none;">Add
                    Class</button>
            </form>

            <div class="setup-list">
                @forelse($schoolClasses as $class)
                    <div class="setup-item">
                        <div>
                            <div class="setup-item-title">{{ $class->name }}</div>
                            <div class="setup-item-meta">Mapped to: {{ $class->level->title ?? 'Unknown' }}</div>
                        </div>
                        <div
                            style="background: var(--bg-card); padding: 6px; border-radius: 6px; border: 1px solid var(--border);">
                            <i class="fas fa-chalkboard"></i>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; font-size: 0.85rem; color: var(--text-muted);">No classes defined yet.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection