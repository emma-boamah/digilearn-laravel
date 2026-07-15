@extends('schools.admin.layout')

@section('title', 'Create Private Quiz')

@section('styles')
<style>
    .form-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 32px;
        max-width: 600px;
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

    .form-input, .form-select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 0.95rem;
        font-family: inherit;
        background: var(--bg);
        transition: border-color 0.2s;
    }

    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .error-text {
        color: #dc2626;
        font-size: 0.85rem;
        margin-top: 6px;
        display: block;
    }

    .form-hint {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 6px;
    }
</style>
@endsection

@section('content')
    <div class="form-card">
        <form method="POST" action="{{ route('school.studio.quiz.store') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Quiz Title</label>
                <input type="text" name="title" class="form-input" value="{{ old('title') }}" required placeholder="e.g. Mid-Term Science Test">
                @error('title') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Grade Level</label>
                    <select name="grade_level" class="form-select" required>
                        <option value="">Select Level</option>
                        <option value="Primary 1" @if(old('grade_level') == 'Primary 1') selected @endif>Primary 1</option>
                        <option value="Primary 2" @if(old('grade_level') == 'Primary 2') selected @endif>Primary 2</option>
                        <option value="Primary 3" @if(old('grade_level') == 'Primary 3') selected @endif>Primary 3</option>
                        <option value="Primary 4" @if(old('grade_level') == 'Primary 4') selected @endif>Primary 4</option>
                        <option value="Primary 5" @if(old('grade_level') == 'Primary 5') selected @endif>Primary 5</option>
                        <option value="Primary 6" @if(old('grade_level') == 'Primary 6') selected @endif>Primary 6</option>
                        <option value="JHS 1" @if(old('grade_level') == 'JHS 1') selected @endif>JHS 1</option>
                        <option value="JHS 2" @if(old('grade_level') == 'JHS 2') selected @endif>JHS 2</option>
                        <option value="JHS 3" @if(old('grade_level') == 'JHS 3') selected @endif>JHS 3</option>
                        <option value="SHS 1" @if(old('grade_level') == 'SHS 1') selected @endif>SHS 1</option>
                        <option value="SHS 2" @if(old('grade_level') == 'SHS 2') selected @endif>SHS 2</option>
                        <option value="SHS 3" @if(old('grade_level') == 'SHS 3') selected @endif>SHS 3</option>
                    </select>
                    @error('grade_level') <span class="error-text">{{ $message }}</span> @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @if(old('subject_id') == $subject->id) selected @endif>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subject_id') <span class="error-text">{{ $message }}</span> @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Time Limit (Minutes)</label>
                    <input type="number" name="time_limit_minutes" class="form-input" value="{{ old('time_limit_minutes', 30) }}" min="1" required>
                    @error('time_limit_minutes') <span class="error-text">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Difficulty Level</label>
                    <select name="difficulty_level" class="form-select" required>
                        <option value="beginner" @if(old('difficulty_level') == 'beginner') selected @endif>Beginner</option>
                        <option value="intermediate" @if(old('difficulty_level') == 'intermediate') selected @endif>Intermediate</option>
                        <option value="advanced" @if(old('difficulty_level') == 'advanced') selected @endif>Advanced</option>
                    </select>
                    @error('difficulty_level') <span class="error-text">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-hint" style="margin-bottom: 24px; padding: 12px; background: rgba(37,99,235,0.05); border-radius: 8px; border: 1px dashed rgba(37,99,235,0.3);">
                <i class="fas fa-info-circle" style="color: var(--primary);"></i>
                After creating this quiz shell, you will be able to manage and add questions to it from the Studio dashboard.
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="sa-btn sa-btn-primary">Create Quiz Shell</button>
                <a href="{{ route('school.studio.index') }}" class="sa-btn sa-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
