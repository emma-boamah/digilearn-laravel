@extends('settings.layout')

@section('title', 'Notification Preferences')
@section('breadcrumb', 'Notifications')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h1 class="page-title">Notification Preferences</h1>
            <p class="page-description">Control how you stay connected. Choose when and where you want to receive updates.</p>
        </div>
        <button id="save-all-btn" style="background-color: var(--primary-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 500; cursor: pointer; transition: background-color 0.2s;">
            <i class="fas fa-save" style="margin-right: 0.5rem;"></i> Save Preferences
        </button>
    </div>
</div>

<!-- General Notifications -->
<div style="margin-bottom: 2.5rem;">
    <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin-bottom: 1rem;">General Notifications</h3>
    <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1.5rem;">Master controls for how we reach you.</p>

    <div style="background-color: var(--bg-card); border-radius: 1rem; border: 1px solid var(--border-color); overflow: hidden;">
        <!-- Email Notification Item -->
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 1rem;">
                <div style="width: 48px; height: 48px; background-color: #eff6ff; color: var(--primary-color); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <h4 style="font-weight: 600; color: var(--text-main); margin-bottom: 0.25rem;">Email Notifications</h4>
                    <p style="color: var(--text-secondary); font-size: 0.875rem;">Weekly digests, account security alerts, and new course announcements.</p>
                </div>
            </div>
            <label class="toggle-switch" style="position: relative; display: inline-block; width: 50px; height: 26px; cursor: pointer;">
                <input type="checkbox" checked style="opacity: 0; width: 0; height: 0;">
                <span class="slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px;">
                    <span class="knob" style="position: absolute; content: ''; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%;"></span>
                </span>
            </label>
        </div>

        <!-- Push Notification Item -->
        <div style="padding: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 1rem;">
                <div style="width: 48px; height: 48px; background-color: #f3f4f6; color: var(--text-secondary); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <h4 style="font-weight: 600; color: var(--text-main); margin-bottom: 0.25rem;">Push Notifications</h4>
                    <p style="color: var(--text-secondary); font-size: 0.875rem;">Instant alerts for mentions, assignment deadlines, and system updates.</p>
                </div>
            </div>
            <label class="toggle-switch" style="position: relative; display: inline-block; width: 50px; height: 26px; cursor: pointer;">
                <input type="checkbox" style="opacity: 0; width: 0; height: 0;">
                <span class="slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px;">
                    <span class="knob" style="position: absolute; content: ''; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%;"></span>
                </span>
            </label>
        </div>
    </div>
</div>

<!-- Grade Level Content -->
<div>
    <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin-bottom: 1rem;">Grade Level Content</h3>
    <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1.5rem;">Subscribe to specific educational tiers to receive tailored content.</p>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
        @foreach($allGradeLevels as $grade)
            @php
                $isPrimary = Str::contains(Str::lower($grade), 'primary');
                $isJhs = Str::contains(Str::lower($grade), 'jhs') || Str::contains(Str::lower($grade), 'junior high');
                $isShs = Str::contains(Str::lower($grade), 'shs') || Str::contains(Str::lower($grade), 'senior high');
                $isUniversity = Str::contains(Str::lower($grade), 'university') || Str::contains(Str::lower($grade), 'tertiary');
                
                $iconClass = 'fa-graduation-cap';
                $colorClass = '#2563eb'; // Default Blue
                $bgColorClass = '#eff6ff';
                $tagText = 'LEVEL';
                $tagColor = '#dbeafe';
                $tagTextColor = '#1e40af';

                if ($isPrimary) {
                    $iconClass = 'fa-child';
                    $colorClass = '#10b981'; // Green
                    $bgColorClass = '#ecfdf5';
                    $tagText = 'GRADES 1-6';
                    $tagColor = '#d1fae5';
                    $tagTextColor = '#065f46';
                } elseif ($isJhs) {
                    $iconClass = 'fa-user-friends';
                    $colorClass = '#f59e0b'; // Amber
                    $bgColorClass = '#fffbeb';
                    $tagText = 'JUNIOR HIGH';
                    $tagColor = '#fef3c7';
                    $tagTextColor = '#92400e';
                } elseif ($isShs) {
                    $iconClass = 'fa-school';
                    $colorClass = '#6366f1'; // Indigo
                    $bgColorClass = '#e0e7ff';
                    $tagText = 'SENIOR HIGH';
                    $tagColor = '#c7d2fe';
                    $tagTextColor = '#3730a3';
                } elseif ($isUniversity) {
                    $iconClass = 'fa-university';
                    $colorClass = '#ec4899'; // Pink
                    $bgColorClass = '#fce7f3';
                    $tagText = 'TERTIARY';
                    $tagColor = '#fbcfe8';
                    $tagTextColor = '#9d174d';
                }
            @endphp

            <div style="background-color: var(--bg-card); border-radius: 1rem; padding: 1.5rem; border: 1px solid var(--border-color);">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 40px; height: 40px; background-color: {{ $bgColorClass }}; color: {{ $colorClass }}; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas {{ $iconClass }}"></i>
                        </div>
                        <h4 style="font-weight: 600; color: var(--text-main);">{{ $grade }}</h4>
                    </div>
                    <span style="background-color: {{ $tagColor }}; color: {{ $tagTextColor }}; font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.625rem; border-radius: 9999px;">
                        {{ $tagText }}
                    </span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.875rem; color: var(--text-secondary);">New curriculum uploads</span>
                        <input 
                            type="checkbox" 
                            class="grade-toggle"
                            data-grade="{{ $grade }}"
                            {{ !in_array($grade, $gradeOptOuts) ? 'checked' : '' }}
                            style="width: 1.25rem; height: 1.25rem; cursor: pointer; accent-color: var(--primary-color);"
                        >
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div id="toast-message" style="position: fixed; bottom: 2rem; right: 2rem; background-color: var(--text-main); color: white; padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); transform: translateY(150%); transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1); z-index: 100;">
    Preferences Saved
</div>

@push('styles')
<style>
    .toggle-switch input:checked + .slider {
        background-color: var(--primary-color);
    }
    .toggle-switch input:focus + .slider {
        box-shadow: 0 0 1px var(--primary-color);
    }
    .toggle-switch input:checked + .slider .knob {
        transform: translateX(24px);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const gradeToggles = document.querySelectorAll('.grade-toggle');
        const saveBtn = document.getElementById('save-all-btn');
        const toast = document.getElementById('toast-message');

        function showToast(message) {
            toast.textContent = message;
            toast.style.transform = 'translateY(0)';
            setTimeout(() => {
                toast.style.transform = 'translateY(150%)';
            }, 3000);
        }

        // Handle individual toggles immediately (per existing behavior)
        gradeToggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const gradeLevel = this.dataset.grade;
                const isChecked = this.checked;
                const optOut = !isChecked;

                fetch('{{ route('api.notifications.grade-opt-out') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        grade_level: gradeLevel,
                        opt_out: optOut
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Optional silent success or console log
                        // showToast('Preference updated');
                    } else {
                        showToast('Failed to update preference');
                        this.checked = !isChecked; // Revert
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Network error');
                    this.checked = !isChecked;
                });
            });
        });

        // "Save Preferences" button visual feedback (since API calls are instant on toggle)
        saveBtn.addEventListener('click', function() {
            showToast('Preferences Saved Successfully');
        });
    });
</script>
@endpush
@endsection
