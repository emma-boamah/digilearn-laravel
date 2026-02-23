@extends('settings.layout')

@section('title', 'Notification Preferences')
@section('breadcrumb', 'Notifications')

@section('content')
<div class="page-header">
    <div class="flex justify-between items-start flex-wrap gap-4">
        <div>
            <h1 class="page-title">Notification Preferences</h1>
            <p class="page-description text-wrap">Control how you stay connected. Choose when and where you want to receive updates.</p>
        </div>
        <button id="save-all-btn" class="bg-primary text-white border-none p-2-5 rounded-md font-medium cursor-pointer transition-all hover-primary-dark shadow-sm">
            <i class="fas fa-save mr-2"></i> Save Preferences
        </button>
    </div>
</div>

<!-- General Notifications -->
<div class="mb-10">
    <h3 class="text-lg font-semibold text-main mb-4">General Notifications</h3>
    <p class="text-secondary text-sm mb-6">Master controls for how we reach you.</p>
    <div class="bg-card rounded-2xl border overflow-hidden shadow-sm">
        <!-- Email Notification Item -->
        <div class="p-6 border-bottom flex items-center justify-between flex-wrap gap-4">
            <div class="flex gap-4">
                <div class="w-12 h-12 bg-blue-50 text-primary rounded-xl flex items-center justify-center text-xl">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-main mb-1">Email Notifications</h4>
                    <p class="text-secondary text-sm text-wrap">New content announcements.</p>
                </div>
            </div>
            <label class="toggle-switch-ui">
                <input type="checkbox" checked class="hidden-input">
                <span class="toggle-slider">
                    <span class="toggle-knob"></span>
                </span>
            </label>
        </div>

        <!-- Push Notification Item -->
        <div class="p-6 flex items-center justify-between flex-wrap gap-4">
            <div class="flex gap-4">
                <div class="w-12 h-12 bg-gray-100 text-secondary rounded-xl flex items-center justify-center text-xl">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-main mb-1">Push Notifications</h4>
                    <p class="text-secondary text-sm text-wrap">In-app alerts for new content.</p>
                </div>
            </div>
            <label class="toggle-switch-ui">
                <input type="checkbox" class="hidden-input">
                <span class="toggle-slider">
                    <span class="toggle-knob"></span>
                </span>
            </label>
        </div>
    </div>
</div>


<!-- Grade Level Content -->
<div>
    <h3 class="text-lg font-semibold text-main mb-4">Grade Level Content</h3>
    <p class="text-secondary text-sm mb-6">Subscribe to specific educational tiers to receive tailored content.</p>

    <div class="grade-grid grid gap-6">
        @foreach($allGradeLevels as $grade)
            @php
                $isPrimary = Str::contains(Str::lower($grade), 'primary');
                $isJhs = Str::contains(Str::lower($grade), 'jhs') || Str::contains(Str::lower($grade), 'junior high');
                $isShs = Str::contains(Str::lower($grade), 'shs') || Str::contains(Str::lower($grade), 'senior high');
                $isUniversity = Str::contains(Str::lower($grade), 'university') || Str::contains(Str::lower($grade), 'tertiary');
                
                $themeClass = 'theme-blue';
                $iconClass = 'fa-graduation-cap';
                $tagText = 'LEVEL';

                if ($isPrimary) {
                    $themeClass = 'theme-green';
                    $iconClass = 'fa-child';
                    $tagText = 'GRADES 1-6';
                } elseif ($isJhs) {
                    $themeClass = 'theme-amber';
                    $iconClass = 'fa-user-friends';
                    $tagText = 'JUNIOR HIGH';
                } elseif ($isShs) {
                    $themeClass = 'theme-indigo';
                    $iconClass = 'fa-school';
                    $tagText = 'SENIOR HIGH';
                } elseif ($isUniversity) {
                    $themeClass = 'theme-pink';
                    $iconClass = 'fa-university';
                    $tagText = 'TERTIARY';
                }
            @endphp

            <div class="bg-card rounded-2xl p-6 border shadow-sm {{ $themeClass }}">
                <div class="flex justify-between items-start mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center theme-icon">
                            <i class="fas {{ $iconClass }}"></i>
                        </div>
                        <h4 class="font-semibold text-main">{{ $grade }}</h4>
                    </div>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full theme-tag">
                        {{ $tagText }}
                    </span>
                </div>

                <div class="flex flex-col gap-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-secondary">New content uploads</span>
                        <input 
                            type="checkbox" 
                            class="grade-toggle"
                            data-grade="{{ $grade }}"
                            {{ !in_array($grade, $gradeOptOuts) ? 'checked' : '' }}
                        >
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div id="toast-message" class="toast-popup">
    Preferences Saved
</div>

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .mb-10 { margin-bottom: 2.5rem; }
    .mr-2 { margin-right: 0.5rem; }
    .bg-gray-100 { background-color: #f3f4f6; }
    .grade-grid { grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
    
    /* Toggle Switch Styles */
    .toggle-switch-ui { position: relative; display: inline-block; width: 50px; height: 26px; cursor: pointer; }
    .hidden-input { opacity: 0; width: 0; height: 0; }
    .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
    .toggle-knob { position: absolute; content: ''; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    
    .hidden-input:checked + .toggle-slider { background-color: var(--primary-color); }
    .hidden-input:focus + .toggle-slider { box-shadow: 0 0 1px var(--primary-color); }
    .hidden-input:checked + .toggle-slider .toggle-knob { transform: translateX(24px); }
    
    .grade-toggle { width: 1.25rem; height: 1.25rem; cursor: pointer; accent-color: var(--primary-color); }
    
    /* Toast Styles */
    .toast-popup { position: fixed; bottom: 2rem; right: 2rem; background-color: var(--text-main); color: white; padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); transform: translateY(150%); transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1); z-index: 100; }
    
    /* Theme variants for grade content */
    .theme-blue .theme-icon { background-color: #eff6ff; color: #2563eb; }
    .theme-blue .theme-tag { background-color: #dbeafe; color: #1e40af; }
    .theme-green .theme-icon { background-color: #ecfdf5; color: #10b981; }
    .theme-green .theme-tag { background-color: #d1fae5; color: #065f46; }
    .theme-amber .theme-icon { background-color: #fffbeb; color: #f59e0b; }
    .theme-amber .theme-tag { background-color: #fef3c7; color: #92400e; }
    .theme-indigo .theme-icon { background-color: #e0e7ff; color: #6366f1; }
    .theme-indigo .theme-tag { background-color: #c7d2fe; color: #3730a3; }
    .theme-pink .theme-icon { background-color: #fce7f3; color: #ec4899; }
    .theme-pink .theme-tag { background-color: #fbcfe8; color: #9d174d; }
</style>
@endpush

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
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
