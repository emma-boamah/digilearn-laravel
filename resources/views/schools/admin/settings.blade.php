@extends('schools.admin.layout')

@section('title', 'Settings')

@section('styles')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        .settings-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 24px;
        }

        .settings-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .settings-section-desc {
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

        .form-input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.15s ease;
            background: var(--bg);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        textarea.form-input {
            min-height: 100px;
            resize: vertical;
        }

        .logo-upload-area {
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }

        .current-logo {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid var(--border);
        }

        .logo-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            background: var(--bg);
            border: 2px dashed var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
        }

        .logo-upload-input {
            flex: 1;
        }

        .file-input {
            display: block;
            width: 100%;
            padding: 10px;
            border: 1px dashed var(--border);
            border-radius: 8px;
            background: var(--bg);
            cursor: pointer;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .file-input:hover {
            border-color: var(--primary);
        }

        .subdomain-display {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg);
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid var(--border);
            font-size: 0.95rem;
            color: var(--text-muted);
        }

        .subdomain-value {
            font-weight: 600;
            color: var(--primary);
        }

        .text-danger {
            color: #dc2626;
            font-size: 0.8rem;
            margin-top: 4px;
        }
    </style>
@endsection

@section('content')
    <form method="POST" action="{{ route('school.admin.settings.update') }}" enctype="multipart/form-data">
        @csrf

        <!-- Branding -->
        <div class="settings-card">
            <h2 class="settings-section-title">Branding</h2>
            <p class="settings-section-desc">Customize how your school appears to students and teachers on the platform.</p>

            <div class="form-group">
                <label class="form-label">School Logo</label>
                <div class="logo-upload-area">
                    @if($school->logo)
                        <img src="{{ secure_asset('storage/' . $school->logo) }}" alt="{{ $school->name }}"
                            class="current-logo">
                    @else
                        <div class="logo-placeholder">
                            <i class="fas fa-image" style="font-size: 1.5rem;"></i>
                        </div>
                    @endif
                    <div class="logo-upload-input">
                        <input type="file" name="logo" class="file-input" accept="image/*">
                        <small style="color: var(--text-muted); margin-top: 6px; display: block;">
                            Recommended: 200×200px, PNG or JPG, max 2MB
                        </small>
                    </div>
                </div>
                @error('logo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- General Info -->
        <div class="settings-card">
            <h2 class="settings-section-title">General Information</h2>
            <p class="settings-section-desc">Basic details about your school.</p>

            <div class="form-group">
                <label for="name" class="form-label">School Name</label>
                <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $school->name) }}"
                    required>
                @error('name')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Subdomain</label>
                <div class="subdomain-display">
                    <span class="subdomain-value">{{ $school->subdomain }}</span>.shoutoutgh.com
                    <span style="margin-left: auto; font-size: 0.75rem;">(cannot be changed)</span>
                </div>
            </div>

            <div class="form-group">
                <label for="contact_details" class="form-label">Contact Details</label>
                <textarea id="contact_details" name="contact_details" class="form-input"
                    placeholder="Phone, address, or any contact info for your school...">{{ old('contact_details', $school->contact_details) }}</textarea>
                @error('contact_details')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="sa-btn sa-btn-primary">
            <i class="fas fa-save"></i> Save Changes
        </button>
    </form>
@endsection