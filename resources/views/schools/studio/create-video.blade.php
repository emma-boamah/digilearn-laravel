@extends('schools.admin.layout')

@section('title', 'Upload Private Video')

@section('styles')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
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

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            background: var(--bg);
            transition: border-color 0.2s;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-hint {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .error-text {
            color: #dc2626;
            font-size: 0.85rem;
            margin-top: 6px;
            display: block;
        }
    </style>
@endsection

@section('content')
    <div class="form-card">
        <form method="POST" action="{{ route('school.studio.video.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Video Title</label>
                <input type="text" name="title" class="form-input" value="{{ old('title') }}" required
                    placeholder="e.g. Introduction to Algebra">
                @error('title') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Video URL (YouTube/Vimeo)</label>
                <input type="url" name="external_video_url" class="form-input" value="{{ old('external_video_url') }}"
                    required placeholder="https://youtube.com/watch?v=...">
                <div class="form-hint">For this version, please provide a valid YouTube or Vimeo URL.</div>
                @error('external_video_url') <span class="error-text">{{ $message }}</span> @enderror
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
                            <option value="{{ $subject->id }}" @if(old('subject_id') == $subject->id) selected @endif>
                                {{ $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subject_id') <span class="error-text">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description (Optional)</label>
                <textarea name="description" class="form-textarea"
                    placeholder="Brief description of what students will learn...">{{ old('description') }}</textarea>
                @error('description') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div style="display: flex; gap: 12px; margin-top: 32px;">
                <button type="submit" class="sa-btn sa-btn-primary">Upload Video</button>
                <a href="{{ route('school.studio.index') }}" class="sa-btn sa-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection