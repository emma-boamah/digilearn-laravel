@extends('settings.layout')

@section('page_title', 'Profile Settings')
@section('breadcrumb', 'Profile')

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Profile Page Layout */
    .profile-container {
        width: 100%;
        max-width: 1140px;
        margin: 0 auto;
    }

    .content-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
    }

    .content-header-text {
        flex: 1;
        min-width: 250px;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .page-title {
        font-size: 1.625rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.25rem;
        letter-spacing: -0.02em;
    }

    .page-subtitle {
        font-size: 0.875rem;
        color: #64748b;
    }

    /* 2-Column Grid Layout */
    .profile-layout-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        align-items: start;
    }

    .profile-col {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* Card Styling */
    .profile-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.875rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03), 0 1px 2px rgba(0, 0, 0, 0.02);
    }

    .card-title {
        font-size: 1.0625rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 1.25rem;
        letter-spacing: -0.01em;
    }

    /* Avatar Card */
    .avatar-card {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .avatar-box {
        width: 72px;
        height: 72px;
        border-radius: 0.75rem;
        background: #e0edfc;
        color: #1e56a0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        font-weight: 700;
        overflow: hidden;
        flex-shrink: 0;
        border: 1px solid #bfdbfe;
    }

    .avatar-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-actions {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }

    .btn-change-avatar {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        width: fit-content;
    }

    .btn-change-avatar:hover {
        border-color: #2563eb;
        color: #2563eb;
        background: #f8fafc;
    }

    .avatar-hint {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .avatar-upload-input {
        display: none;
    }

    /* Form Grids & Inputs */
    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.4rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .form-input {
        width: 100%;
        padding: 0.625rem 0.875rem;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: #0f172a;
        background-color: #ffffff;
        transition: border-color 0.2s, box-shadow 0.2s;
        box-sizing: border-box;
    }

    .form-input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .form-input::placeholder {
        color: #94a3b8;
    }

    .form-textarea {
        resize: vertical;
        min-height: 90px;
        line-height: 1.5;
    }

    /* Select Dropdown Styling */
    .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.25em 1.25em;
        padding-right: 2.25rem;
        cursor: pointer;
    }

    /* Phone Input with Action Button */
    .phone-input-wrapper {
        display: flex;
        align-items: center;
        position: relative;
    }

    .phone-input-wrapper .phone-input {
        padding-right: 5.75rem;
    }

    .phone-input-wrapper .btn-phone-action {
        position: absolute;
        right: 5px;
        padding: 0.35rem 0.75rem;
        background-color: #1e56a0;
        color: white;
        border: none;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
        white-space: nowrap;
    }

    .phone-input-wrapper .btn-phone-action:hover {
        background-color: #16437e;
    }

    .phone-input-wrapper .btn-phone-verify {
        background-color: #10b981;
    }

    .phone-input-wrapper .btn-phone-verify:hover {
        background-color: #059669;
    }

    .verified-badge {
        color: #10b981;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .unverified-badge {
        color: #f59e0b;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Subject Chips */
    .subject-chips-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.25rem;
    }

    .chip-item {
        position: relative;
        cursor: pointer;
        user-select: none;
        display: inline-flex;
    }

    .chip-item input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        pointer-events: none;
    }

    .chip-label {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.95rem;
        border-radius: 9999px;
        border: 1px solid #cbd5e1;
        background-color: #ffffff;
        color: #475569;
        font-size: 0.8125rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .chip-item:hover .chip-label {
        border-color: #3b82f6;
        color: #1d4ed8;
        background-color: #f8fafc;
    }

    .chip-item input[type="checkbox"]:checked + .chip-label {
        border-color: #2563eb;
        background-color: #eff6ff;
        color: #1d4ed8;
        font-weight: 600;
        box-shadow: 0 0 0 1px #2563eb;
    }

    /* Subscription Card */
    .subscription-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .subscription-plan-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
    }

    .subscription-icon {
        color: #2563eb;
        font-size: 1.125rem;
    }

    .subscription-plan-name {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .subscription-description {
        font-size: 0.8125rem;
        color: #64748b;
        margin: 0;
    }

    .btn-subscription {
        padding: 0.6rem 1.25rem;
        background: #1e56a0;
        color: #ffffff;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        white-space: nowrap;
        transition: background-color 0.2s ease;
    }

    .btn-subscription:hover {
        background: #16437e;
        color: #ffffff;
    }

    /* Security & Account Card */
    .security-actions-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .btn-security {
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-size: 0.8125rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-change-pw {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #334155;
    }

    .btn-change-pw:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }

    .btn-delete-acc {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
    }

    .btn-delete-acc:hover {
        background: #fee2e2;
        color: #b91c1c;
    }

    /* Form Actions Footer */
    .form-actions-bar {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1.5rem;
        padding-top: 1.25rem;
        border-top: 1px solid #e2e8f0;
    }

    .btn-action-reset {
        padding: 0.6rem 1.25rem;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        color: #334155;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-action-reset:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }

    .btn-action-save {
        padding: 0.6rem 1.5rem;
        background: #1e56a0;
        border: none;
        border-radius: 0.5rem;
        color: #ffffff;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .btn-action-save:hover {
        background: #16437e;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        padding: 0.2rem 0.55rem;
        border-radius: 2rem;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .status-active { background: #dcfce7; color: #15803d; }
    .status-trial { background: #eff6ff; color: #1d4ed8; }
    .status-expired { background: #fee2e2; color: #b91c1c; }

    /* Modals */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(4px);
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-container {
        background: white;
        border-radius: 1rem;
        width: 100%;
        max-width: 480px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin: 1rem;
    }

    .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        font-size: 1.125rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        background: #f8fafc;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #64748b;
        cursor: pointer;
        padding: 0.25rem;
        line-height: 1;
        transition: color 0.2s;
    }

    .modal-close:hover {
        color: #0f172a;
    }

    /* Responsive Adjustments */
    @media (max-width: 900px) {
        .profile-layout-grid {
            grid-template-columns: 1fr;
        }

        .form-grid-2 {
            grid-template-columns: 1fr;
        }

        .subscription-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-subscription {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 640px) {
        .content-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .header-actions {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>
@endpush

@section('content')
<div class="profile-container">
    <div class="content-header">
        <div class="content-header-text">
            <h1 class="page-title">Profile Settings</h1>
            <p class="page-subtitle">Manage your personal information, security, and subscription.</p>
        </div>
        <div class="header-actions">
            <button type="reset" form="profileForm" class="btn-action-reset">Reset Changes</button>
            <button type="submit" form="profileForm" class="btn-action-save">Save Changes</button>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-5" role="alert">
            <strong class="font-bold">Please check the form for errors:</strong>
            <ul class="mt-2 text-sm list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="profileForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="profile-layout-grid">
            <!-- Left Column: Avatar, Personal Info, Bio -->
            <div class="profile-col">
                <!-- Avatar Card -->
                <div class="profile-card avatar-card">
                    <div class="avatar-box" id="avatarPreview">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="Profile" class="avatar-image" id="avatarImage">
                        @else
                            <span>{{ substr($user->name ?? 'U', 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="avatar-actions">
                        <button type="button" class="btn-change-avatar" id="editAvatarBtn">
                            <i class="fas fa-camera"></i>
                            <span>Change Avatar</span>
                        </button>
                        <span class="avatar-hint">JPG, GIF or PNG. Max size of 800K</span>
                        <input type="file" id="avatarInput" name="avatar" class="avatar-upload-input" accept="image/*">
                    </div>
                </div>

                <!-- Personal Information Card -->
                <div class="profile-card">
                    <h2 class="card-title">Personal Information</h2>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-input" value="{{ $firstName }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-input" value="{{ $lastName }}" required>
                        </div>
                    </div>
                    
                    <div class="form-group mt-4">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" value="{{ $user->email }}" required>
                    </div>
                    
                    <div class="form-grid-2 mt-4">
                        <div class="form-group">
                            <label for="display_phone" class="form-label">
                                <span>Phone Number <span class="text-muted font-normal text-xs">(Optional)</span></span>
                                @if($user->phone_verified_at)
                                    <span class="verified-badge">✓ Verified</span>
                                @elseif($user->phone)
                                    <span class="unverified-badge">⚠ Unverified</span>
                                @endif
                            </label>
                            
                            <div class="phone-input-wrapper">
                                <input type="tel" id="display_phone" class="form-input phone-input" value="{{ $maskedPhone ?? 'Not provided' }}" readonly>
                                @if($user->phone)
                                    @if(!$user->phone_verified_at)
                                        <button type="button" class="btn-phone-action btn-phone-verify" id="verifyPhoneBtn">Verify</button>
                                    @endif
                                    <button type="button" class="btn-phone-action" id="updatePhoneBtn">Change</button>
                                @else
                                    <button type="button" class="btn-phone-action" id="addPhoneBtn">Add Phone</button>
                                @endif
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" value="{{ $user->date_of_birth }}">
                        </div>
                    </div>
                </div>

                <!-- Bio Card -->
                <div class="profile-card">
                    <h2 class="card-title">Bio</h2>
                    <div class="form-group">
                        <textarea id="bio" name="bio" class="form-input form-textarea" rows="4" placeholder="Tell us about yourself...">{{ $user->bio }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right Column: Location & Education, Preferences, Subscription, Security -->
            <div class="profile-col">
                <!-- Location & Education Card -->
                <div class="profile-card">
                    <h2 class="card-title">Location &amp; Education</h2>
                    <div class="form-grid-2">
                        @php
                            $defaultCountries = ['Ghana', 'Nigeria', 'Kenya', 'South Africa', 'United Kingdom', 'United States'];
                            if ($user->country && !in_array($user->country, $defaultCountries)) {
                                $defaultCountries[] = $user->country;
                            }
                            sort($defaultCountries);
                        @endphp
                        <div class="form-group">
                            <label for="country" class="form-label">Country</label>
                            <select id="country" name="country" class="form-input form-select">
                                @foreach($defaultCountries as $c)
                                    <option value="{{ $c }}" {{ $user->country === $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="city" class="form-label">
                                <span>City</span>
                                @if($user->city)
                                    <span class="text-xs text-green-600 font-normal">(Auto-saved)</span>
                                @endif
                            </label>
                            <input type="text" id="city" name="city" class="form-input {{ $user->city ? 'bg-gray-50 cursor-not-allowed' : '' }}" 
                                   value="{{ $user->city }}" 
                                   {{ $user->city ? 'readonly' : '' }}
                                   placeholder="Enter your city">
                        </div>
                    </div>

                    <div class="form-grid-2 mt-4">
                        <div class="form-group">
                            <label for="education_level" class="form-label">Education Level</label>
                            <select id="education_level" name="education_level" class="form-input form-select">
                                <option value="primary" {{ $user->education_level == 'primary' ? 'selected' : '' }}>Primary</option>
                                <option value="jhs" {{ $user->education_level == 'jhs' ? 'selected' : '' }}>JHS</option>
                                <option value="shs" {{ $user->education_level == 'shs' ? 'selected' : '' }}>SHS</option>
                                <option value="university" {{ $user->education_level == 'university' ? 'selected' : '' }}>University</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="grade" class="form-label">Grade</label>
                            <select id="grade" name="grade" class="form-input form-select">
                                @foreach($allGradeLevels as $grade)
                                    <option value="{{ $grade }}" {{ $user->grade == $grade ? 'selected' : '' }}>Grade {{ $grade }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Learning Preferences Card -->
                <div class="profile-card">
                    <h2 class="card-title">Learning Preferences</h2>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="preferred_language" class="form-label">Preferred Language</label>
                            <select id="preferred_language" name="preferred_language" class="form-input form-select" required>
                                <option value="en" {{ $user->preferred_language == 'en' ? 'selected' : '' }}>English</option>
                                <option value="tw" {{ $user->preferred_language == 'tw' ? 'selected' : '' }}>Twi</option>
                                <option value="ga" {{ $user->preferred_language == 'ga' ? 'selected' : '' }}>Ga</option>
                                <option value="ee" {{ $user->preferred_language == 'ee' ? 'selected' : '' }}>Ewe</option>
                                <option value="fr" {{ $user->preferred_language == 'fr' ? 'selected' : '' }}>French</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="learning_style" class="form-label">Learning Style</label>
                            <select id="learning_style" name="learning_style" class="form-input form-select">
                                <option value="visual" {{ $user->learning_style == 'visual' ? 'selected' : '' }}>Visual</option>
                                <option value="auditory" {{ $user->learning_style == 'auditory' ? 'selected' : '' }}>Auditory</option>
                                <option value="kinesthetic" {{ $user->learning_style == 'kinesthetic' ? 'selected' : '' }}>Kinesthetic</option>
                                <option value="mixed" {{ $user->learning_style == 'mixed' ? 'selected' : '' }}>Mixed</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <label class="form-label">Interests &amp; Subjects</label>
                        <div class="subject-chips-container">
                            @foreach(['mathematics' => 'Mathematics', 'science' => 'Science', 'programming' => 'Programming', 'english' => 'English', 'history' => 'History', 'geography' => 'Geography'] as $subjectVal => $subjectLabel)
                                <label class="chip-item">
                                    <input type="checkbox" name="subjects[]" value="{{ $subjectVal }}" 
                                        {{ in_array($subjectVal, $userSubjectPreferences ?? []) ? 'checked' : '' }}>
                                    <span class="chip-label">{{ $subjectLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Subscription Card -->
                <div class="profile-card">
                    <h2 class="card-title">Subscription</h2>
                    <div class="subscription-content">
                        <div class="subscription-details">
                            @if($subscriptionInfo)
                                <div class="subscription-plan-header">
                                    <i class="fas fa-check-circle subscription-icon"></i>
                                    <span class="subscription-plan-name">{{ $subscriptionInfo['plan_name'] }}</span>
                                    <span class="status-badge status-{{ strtolower($subscriptionInfo['status']) }}">{{ ucfirst($subscriptionInfo['status']) }}</span>
                                </div>
                                <p class="subscription-description">
                                    {{ $subscriptionInfo['days_remaining'] ? $subscriptionInfo['days_remaining'] . ' days remaining' : 'Active subscription plan' }}
                                </p>
                            @else
                                <div class="subscription-plan-header">
                                    <i class="far fa-star subscription-icon"></i>
                                    <span class="subscription-plan-name">Free Plan</span>
                                </div>
                                <p class="subscription-description">Unlock premium content and advanced features!</p>
                            @endif
                        </div>
                        <div class="subscription-cta">
                            @if($subscriptionInfo)
                                <a href="{{ route('settings.billing') }}" class="btn-subscription" id="manageSubBtn">Manage Plan</a>
                            @else
                                <a href="{{ route('pricing') }}" class="btn-subscription">Upgrade Now <i class="fas fa-arrow-right ml-1"></i></a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Security & Account Card -->
                <div class="profile-card">
                    <h2 class="card-title">Security &amp; Account</h2>
                    <div class="security-actions-row">
                        <button type="button" class="btn-security btn-change-pw" id="changePassBtn">Change Password</button>
                        <button type="button" class="btn-security btn-delete-acc" id="deleteAccountBtn">Delete Account</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Footer -->
        <div class="form-actions-bar">
            <button type="reset" class="btn-action-reset">Reset Changes</button>
            <button type="submit" class="btn-action-save">Save Changes</button>
        </div>
    </form>
</div>

<!-- Phone Update Modal -->
<div id="phoneModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Update Phone Number</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="phoneForm">
                <div class="form-group">
                    <label class="form-label" for="new_phone">New Phone Number</label>
                    <input type="tel" id="new_phone" class="form-input" placeholder="+233..." required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-action-reset close-modal">Cancel</button>
            <button type="button" class="btn-action-save" id="savePhoneBtn">Save</button>
        </div>
    </div>
</div>

<!-- Password Change Modal -->
<div id="passwordModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Change Password</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="passwordForm">
                <div class="form-group mb-4">
                    <label class="form-label" for="current_password">Current Password</label>
                    <input type="password" id="current_password" class="form-input" required autocomplete="current-password">
                </div>
                <div class="form-group mb-4">
                    <label class="form-label" for="new_password">New Password</label>
                    <input type="password" id="new_password" class="form-input" required autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label class="form-label" for="new_password_confirmation">Confirm New Password</label>
                    <input type="password" id="new_password_confirmation" class="form-input" required autocomplete="new-password">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-action-reset close-modal">Cancel</button>
            <button type="button" class="btn-action-save" id="savePasswordBtn">Update Password</button>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div id="deleteAccountModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="text-red-600 font-bold">Delete Account</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="text-sm text-gray-600 mb-4">
                <strong>Warning:</strong> Deleting your account is permanent. This action cannot be undone.
            </p>

            <div class="impact-summary bg-red-50 p-4 rounded-lg mb-4 border border-red-100">
                <h4 class="text-red-800 text-sm font-bold mb-2">You will lose:</h4>
                <ul class="text-xs text-red-700 list-disc list-inside space-y-1">
                    @if($resourceCounts['quizzes'] > 0)
                        <li>{{ $resourceCounts['quizzes'] }} Quizzes created</li>
                    @endif
                    @if($resourceCounts['videos'] > 0)
                        <li>{{ $resourceCounts['videos'] }} Videos uploaded</li>
                    @endif
                    @if($resourceCounts['documents'] > 0)
                        <li>{{ $resourceCounts['documents'] }} Documents shared</li>
                    @endif
                    @if($resourceCounts['progress'] > 0)
                        <li>{{ $resourceCounts['progress'] }} Progress milestones</li>
                    @endif
                    <li>Access to all subscription benefits</li>
                    <li>All personal profile data</li>
                </ul>
            </div>

            <p class="text-xs text-gray-500 mb-4">
                Active plans will be cancelled immediately without refund. All your data will be wiped from our servers according to our privacy policy.
            </p>
            <form id="deleteAccountForm" action="{{ route('profile.destroy') }}" method="POST">
                @csrf
                @method('DELETE')
                
                <div class="form-group mb-4">
                    <label class="form-label" for="delete_confirmation_text">Type <strong>DELETE</strong> to confirm</label>
                    <input type="text" id="delete_confirmation_text" class="form-input" placeholder="DELETE" required autocomplete="off">
                </div>
                
                <div class="form-group mb-4">
                    <label class="form-label" for="delete_password_confirm">Verify Password</label>
                    <input type="password" id="delete_password_confirm" name="password" class="form-input" required autocomplete="current-password">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-action-reset close-modal">Cancel</button>
            <button type="submit" form="deleteAccountForm" class="btn-security btn-delete-acc opacity-50 cursor-not-allowed" id="confirmDeleteBtn" disabled>Permanently Delete Account</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function() {
        const avatarInput = document.getElementById('avatarInput');
        const editAvatarBtn = document.getElementById('editAvatarBtn');
        const avatarPreview = document.getElementById('avatarImage');

        if (editAvatarBtn && avatarInput) {
            editAvatarBtn.addEventListener('click', () => avatarInput.click());

            avatarInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (avatarPreview) {
                            avatarPreview.src = e.target.result;
                        } else {
                            const previewContainer = document.getElementById('avatarPreview');
                            if (previewContainer) {
                                previewContainer.innerHTML = `<img src="${e.target.result}" class="avatar-image" id="avatarImage">`;
                            }
                        }
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }

        // Modals management
        const modals = {
            phone: document.getElementById('phoneModal'),
            password: document.getElementById('passwordModal'),
            delete: document.getElementById('deleteAccountModal')
        };

        const openModal = (id) => {
            if (modals[id]) modals[id].classList.add('active');
        };

        const closeModal = (id) => {
            if (modals[id]) modals[id].classList.remove('active');
        };

        document.getElementById('addPhoneBtn')?.addEventListener('click', () => openModal('phone'));
        document.getElementById('updatePhoneBtn')?.addEventListener('click', () => openModal('phone'));
        document.getElementById('changePassBtn')?.addEventListener('click', () => openModal('password'));

        document.querySelectorAll('.modal-close, .close-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
            });
        });

        // Phone AJAX update logic
        document.getElementById('savePhoneBtn')?.addEventListener('click', async function() {
            const phone = document.getElementById('new_phone').value;
            const saveBtn = this;
            const originalText = saveBtn.innerText;

            if (!phone) {
                alert('Please enter a phone number.');
                return;
            }

            saveBtn.disabled = true;
            saveBtn.innerText = 'Updating...';

            try {
                const response = await fetch('{{ route("profile.phone.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ phone: phone })
                });

                const data = await response.json();

                if (data.success) {
                    const displayPhone = document.getElementById('display_phone');
                    if (displayPhone) {
                        displayPhone.value = data.masked_phone || data.phone;
                    }

                    const verifiedBadge = document.querySelector('.verified-badge');
                    if (verifiedBadge) {
                        verifiedBadge.remove();
                    }
                    
                    alert(data.message);
                    closeModal('phone');
                } else {
                    if (data.errors && data.errors.phone) {
                        alert(data.errors.phone[0]);
                    } else {
                        alert(data.message || 'Failed to update phone number.');
                    }
                }
            } catch (error) {
                console.error('Error updating phone:', error);
                alert('An error occurred while updating the phone number. Please try again.');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerText = originalText;
            }
        });

        // Password AJAX update logic
        document.getElementById('savePasswordBtn')?.addEventListener('click', async function() {
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const newPasswordConfirmation = document.getElementById('new_password_confirmation').value;
            const saveBtn = this;
            const originalText = saveBtn.innerText;

            if (!currentPassword || !newPassword || !newPasswordConfirmation) {
                alert('Please fill in all password fields.');
                return;
            }

            if (newPassword !== newPasswordConfirmation) {
                alert('The new password and confirmation do not match.');
                return;
            }

            saveBtn.disabled = true;
            saveBtn.innerText = 'Updating...';

            try {
                const response = await fetch('{{ route("profile.password.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        current_password: currentPassword,
                        password: newPassword,
                        password_confirmation: newPasswordConfirmation
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    alert(data.message || 'Password updated successfully!');
                    closeModal('password');
                    document.getElementById('passwordForm').reset();
                } else {
                    if (data.errors) {
                        const firstError = Object.values(data.errors)[0];
                        alert(Array.isArray(firstError) ? firstError[0] : firstError);
                    } else {
                        alert(data.message || 'Failed to update password.');
                    }
                }
            } catch (error) {
                console.error('Error updating password:', error);
                alert('An error occurred while updating the password.');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerText = originalText;
            }
        });
        
        // Delete Account Logic
        const deleteAccountBtn = document.getElementById('deleteAccountBtn');
        const deleteAccountModal = document.getElementById('deleteAccountModal');
        const deleteConfirmationText = document.getElementById('delete_confirmation_text');
        const deletePasswordConfirm = document.getElementById('delete_password_confirm');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

        if (deleteAccountBtn && deleteAccountModal) {
            deleteAccountBtn.addEventListener('click', () => {
                deleteAccountModal.classList.add('active');
                if (deleteConfirmationText) deleteConfirmationText.value = '';
                if (deletePasswordConfirm) deletePasswordConfirm.value = '';
                if (confirmDeleteBtn) {
                    confirmDeleteBtn.disabled = true;
                    confirmDeleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            });
        }

        function validateDeleteForm() {
            if (deleteConfirmationText && deletePasswordConfirm && confirmDeleteBtn) {
                if (deleteConfirmationText.value.trim() === 'DELETE' && deletePasswordConfirm.value.length >= 6) {
                    confirmDeleteBtn.disabled = false;
                    confirmDeleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    confirmDeleteBtn.disabled = true;
                    confirmDeleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        }

        if (deleteConfirmationText && deletePasswordConfirm) {
            deleteConfirmationText.addEventListener('input', validateDeleteForm);
            deletePasswordConfirm.addEventListener('input', validateDeleteForm);
        }
    });
</script>
@endpush
