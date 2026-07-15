@extends('schools.admin.layout')

@section('title', 'Invite User')

@section('styles')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        .invite-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 32px;
            max-width: 600px;
        }

        .invite-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .invite-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.15s ease;
            background: var(--bg);
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .text-danger {
            color: #dc2626;
            font-size: 0.8rem;
            margin-top: 4px;
        }

        .role-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .role-card {
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.15s ease;
            text-align: center;
        }

        .role-card:hover {
            border-color: var(--primary);
        }

        .role-card.selected {
            border-color: var(--primary);
            background: rgba(37, 99, 235, 0.05);
        }

        .role-card input[type="radio"] {
            display: none;
        }

        .role-card-icon {
            font-size: 1.5rem;
            margin-bottom: 8px;
            color: var(--primary);
        }

        .role-card-label {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 4px;
        }

        .role-card-desc {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
    </style>
@endsection

@section('content')
    <div class="invite-card">
        <h2 class="invite-title">Add a New User</h2>
        <p class="invite-desc">Create an account for a teacher or student at {{ $school->name }}.</p>

        <form method="POST" action="{{ route('school.admin.users.invite.submit') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Role</label>
                <div class="role-cards">
                    <label class="role-card" id="card-teacher" onclick="selectRole('teacher')">
                        <input type="radio" name="role" value="teacher" {{ old('role') === 'teacher' ? 'checked' : '' }}>
                        <div class="role-card-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <div class="role-card-label">Teacher</div>
                        <div class="role-card-desc">Can create quizzes & grade</div>
                    </label>
                    <label class="role-card" id="card-student" onclick="selectRole('student')">
                        <input type="radio" name="role" value="student" {{ old('role', 'student') === 'student' ? 'checked' : '' }}>
                        <div class="role-card-icon"><i class="fas fa-user-graduate"></i></div>
                        <div class="role-card-label">Student</div>
                        <div class="role-card-desc">Can view content & take exams</div>
                    </label>
                </div>
                @error('role')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}" required
                    placeholder="e.g. John Mensah">
                @error('name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required
                    placeholder="e.g. john@school.edu.gh">
                @error('email')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Temporary Password</label>
                <input type="password" id="password" name="password" class="form-input" required
                    placeholder="They can change this later">
                @error('password')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="sa-btn sa-btn-primary">
                    <i class="fas fa-user-plus"></i> Add User
                </button>
                <a href="{{ route('school.admin.users') }}" class="sa-btn sa-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        function selectRole(role) {
            document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
            document.getElementById('card-' + role).classList.add('selected');
            document.querySelector(`input[value="${role}"]`).checked = true;
        }

        // Init selection on load
        document.addEventListener('DOMContentLoaded', function () {
            const checked = document.querySelector('input[name="role"]:checked');
            if (checked) selectRole(checked.value);
        });
    </script>
@endsection