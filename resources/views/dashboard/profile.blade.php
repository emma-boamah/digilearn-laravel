@extends('settings.layout')

@section('page_title', 'Profile Settings')
@section('breadcrumb', 'Profile')

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    /* Profile Specific Styles */
    .avatar-upload {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 1rem;
    }

    .avatar-large {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-blue, #2677B8));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
        font-weight: 700;
        overflow: hidden;
        border: 4px solid white;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }

    .avatar-image.large {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .edit-avatar-btn {
        background: white;
        border: 1px solid var(--border-color);
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .edit-avatar-btn:hover {
        background: var(--bg-body);
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .avatar-upload-input {
        display: none;
    }

    .form-section {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }

    .form-group.full-width {
        grid-column: span 2;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
        background-color: var(--bg-body);
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.1);
    }

    .optional-indicator {
        font-weight: 400;
        color: var(--text-muted);
        font-size: 0.75rem;
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

    .phone-input-container {
        display: flex;
        gap: 0.5rem;
    }

    .country-code-selector {
        position: relative;
    }

    .country-code-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        background: var(--bg-body);
        cursor: pointer;
        min-width: 100px;
    }

    .phone-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 0.75rem;
    }

    .btn {
        padding: 0.625rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: none;
    }

    .btn-sm {
        padding: 0.4rem 0.75rem;
        font-size: 0.75rem;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        opacity: 0.9;
    }

    .btn-secondary {
        background-color: var(--bg-body);
        border: 1px solid var(--border-color);
        color: var(--text-main);
    }

    .btn-outline {
        background: transparent;
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
    }

    .btn-danger {
        background-color: #fee2e2;
        color: #b91c1c;
    }

    .btn-danger:hover {
        background-color: #fecaca;
    }

    .header-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
    }

    .current-plan-details {
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1.5rem;
    }

    .current-plan-details h4 {
        margin-bottom: 0.5rem;
        color: var(--text-main);
    }

    .status-badge {
        display: inline-flex;
        padding: 0.25rem 0.625rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-active { background: #dcfce7; color: #15803d; }
    .status-trial { background: #eff6ff; color: #1d4ed8; }
    .status-expired { background: #fee2e2; color: #b91c1c; }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
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
        max-width: 500px;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .modal-header {
        padding: 1.25rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1.25rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--text-secondary);
        cursor: pointer;
        padding: 0.5rem;
        line-height: 1;
        transition: color 0.2s;
    }

    .modal-close:hover {
        color: var(--primary-color);
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        .form-group.full-width {
            grid-column: span 1;
        }
    }
    .subjects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.75rem;
        margin-top: 0.5rem;
    }
    .subject-checkbox {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: var(--bg-body);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .subject-checkbox:hover {
        border-color: var(--main-color);
        background: rgba(var(--main-color-rgb), 0.05);
    }
    .subject-checkbox input {
        accent-color: var(--main-color);
    }
    .subject-checkbox span {
        font-size: 0.875rem;
    }
</style>
@endpush

@section('content')
<div class="profile-container">
    <div class="content-header mb-6">
        <h1 class="text-2xl font-bold text-main">Profile Settings</h1>
        <p class="text-secondary">Manage your personal information, security, and subscription.</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Oops!</strong>
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
        
        <!-- Avatar Section -->
        <div class="avatar-upload">
            <div class="avatar-large" id="avatarPreview">
                @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="Profile" class="avatar-image large" id="avatarImage">
                @else
                    {{ substr($user->name ?? 'U', 0, 1) }}
                @endif
            </div>
            <button type="button" class="edit-avatar-btn" id="editAvatarBtn">
                <i class="fas fa-camera"></i>
                Change Avatar
            </button>
            <input type="file" id="avatarInput" name="avatar" class="avatar-upload-input" accept="image/*">
        </div>

        <!-- Personal Info -->
        <div class="form-section">
            <h2 class="section-title">Personal Information</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-input" value="{{ $firstName }}" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-input" value="{{ $lastName }}" required>
                </div>
                
                <div class="form-group full-width">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" value="{{ $user->email }}" required>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">
                        Phone Number 
                        <span class="optional-indicator">(Optional)</span>
                        @if($user->phone_verified_at)
                            <span class="verified-badge">✓ Verified</span>
                        @elseif($user->phone)
                            <span class="unverified-badge">⚠ Unverified</span>
                        @endif
                    </label>
                    
                    <div class="phone-input-container">
                        <input type="tel" id="display_phone" class="form-input" value="{{ $maskedPhone ?? 'Not provided' }}" readonly>
                    </div>
                    
                    <div class="phone-actions">
                        @if($user->phone)
                            @if(!$user->phone_verified_at)
                                <button type="button" class="btn btn-primary btn-sm" id="verifyPhoneBtn">Verify</button>
                            @endif
                            <button type="button" class="btn btn-secondary btn-sm" id="updatePhoneBtn">Change</button>
                        @else
                            <button type="button" class="btn btn-primary btn-sm" id="addPhoneBtn">Add Phone</button>
                        @endif
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" value="{{ $user->date_of_birth }}">
                </div>
            </div>
        </div>

        <!-- Location & Education -->
        <div class="form-section">
            <h2 class="section-title">Location & Education</h2>
            <div class="form-grid">
                @php
                    $defaultCountries = ['Ghana', 'Nigeria', 'Kenya', 'South Africa', 'United Kingdom', 'United States'];
                    if ($user->country && !in_array($user->country, $defaultCountries)) {
                        $defaultCountries[] = $user->country;
                    }
                    sort($defaultCountries);
                @endphp
                <div class="form-group">
                    <label for="country" class="form-label">Country</label>
                    <select id="country" name="country" class="form-input">
                        @foreach($defaultCountries as $c)
                            <option value="{{ $c }}" {{ $user->country === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="city" class="form-label">City
                        @if($user->city)
                            <span class="text-xs text-green-600 font-normal ml-2">(Auto-saved)</span>
                        @endif
                    </label>
                    <input type="text" id="city" name="city" class="form-input {{ $user->city ? 'bg-gray-100 cursor-not-allowed' : '' }}" 
                           value="{{ $user->city }}" 
                           {{ $user->city ? 'readonly' : '' }}
                           placeholder="Enter your city">
                </div>
                
                <div class="form-group">
                    <label for="education_level" class="form-label">Education Level</label>
                    <select id="education_level" name="education_level" class="form-input">
                        <option value="primary" {{ $user->education_level == 'primary' ? 'selected' : '' }}>Primary</option>
                        <option value="jhs" {{ $user->education_level == 'jhs' ? 'selected' : '' }}>JHS</option>
                        <option value="shs" {{ $user->education_level == 'shs' ? 'selected' : '' }}>SHS</option>
                        <option value="university" {{ $user->education_level == 'university' ? 'selected' : '' }}>University</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="grade" class="form-label">Grade</label>
                    <select id="grade" name="grade" class="form-input">
                        @foreach($allGradeLevels as $grade)
                            <option value="{{ $grade }}" {{ $user->grade == $grade ? 'selected' : '' }}>Grade {{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Learning Preferences -->
        <div class="form-section">
            <h2 class="section-title">Learning Preferences</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label for="preferred_language" class="form-label">Preferred Language</label>
                    <select id="preferred_language" name="preferred_language" class="form-input" required>
                        <option value="en" {{ $user->preferred_language == 'en' ? 'selected' : '' }}>English</option>
                        <option value="tw" {{ $user->preferred_language == 'tw' ? 'selected' : '' }}>Twi</option>
                        <option value="ga" {{ $user->preferred_language == 'ga' ? 'selected' : '' }}>Ga</option>
                        <option value="ee" {{ $user->preferred_language == 'ee' ? 'selected' : '' }}>Ewe</option>
                        <option value="fr" {{ $user->preferred_language == 'fr' ? 'selected' : '' }}>French</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="learning_style" class="form-label">Learning Style</label>
                    <select id="learning_style" name="learning_style" class="form-input">
                        <option value="visual" {{ $user->learning_style == 'visual' ? 'selected' : '' }}>Visual</option>
                        <option value="auditory" {{ $user->learning_style == 'auditory' ? 'selected' : '' }}>Auditory</option>
                        <option value="kinesthetic" {{ $user->learning_style == 'kinesthetic' ? 'selected' : '' }}>Kinesthetic</option>
                        <option value="mixed" {{ $user->learning_style == 'mixed' ? 'selected' : '' }}>Mixed</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Interests & Subjects</label>
                    <div class="subjects-grid">
                        @foreach(['mathematics', 'science', 'programming', 'english', 'history', 'geography'] as $subject)
                            <label class="subject-checkbox">
                                <input type="checkbox" name="subjects[]" value="{{ $subject }}" 
                                    {{ in_array($subject, $userSubjectPreferences ?? []) ? 'checked' : '' }}>
                                <span>{{ ucfirst($subject) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2 class="section-title">Bio</h2>
            <textarea id="bio" name="bio" class="form-input" rows="4">{{ $user->bio }}</textarea>
        </div>

        <!-- Subscription -->
        <div class="form-section">
            <h2 class="section-title">Subscription</h2>
            @if($subscriptionInfo)
                <div class="current-plan-details">
                    <h4>{{ $subscriptionInfo['plan_name'] }}</h4>
                    <p class="text-secondary text-sm mb-4">
                        Status: <span class="status-badge status-{{ strtolower($subscriptionInfo['status']) }}">{{ ucfirst($subscriptionInfo['status']) }}</span>
                    </p>
                    <p class="text-sm">Days remaining: {{ $subscriptionInfo['days_remaining'] ?? 'N/A' }}</p>
                    <button type="button" class="btn btn-secondary mt-4" id="manageSubBtn">Manage Subscription</button>
                </div>
            @else
                <div class="current-plan-details">
                    <h4>Free Plan</h4>
                    <p class="text-secondary text-sm">Upgrade for more features!</p>
                    <a href="{{ route('pricing') }}" class="btn btn-primary mt-4">Upgrade Now</a>
                </div>
            @endif
        </div>

        <!-- Account Security -->
        <div class="form-section">
            <h2 class="section-title">Security & Account</h2>
            <div class="flex gap-4">
                <button type="button" class="btn btn-secondary" id="changePassBtn">Change Password</button>
                <button type="button" class="btn btn-danger" id="deleteAccountBtn">Delete Account</button>
            </div>
        </div>

        <div class="header-actions">
            <button type="reset" class="btn btn-secondary">Reset Changes</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<!-- Modals -->
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
            <button type="button" class="btn btn-secondary close-modal">Cancel</button>
            <button type="submit" form="deleteAccountForm" class="btn btn-danger opacity-50 cursor-not-allowed" id="confirmDeleteBtn" disabled>Permanently Delete Account</button>
        </div>
    </div>
</div>

<div id="phoneModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Update Phone Number</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="phoneForm">
                <div class="form-group">
                    <label class="form-label">New Phone Number</label>
                    <input type="tel" id="new_phone" class="form-input" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary close-modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="savePhoneBtn">Save</button>
        </div>
    </div>
</div>

<div id="passwordModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Change Password</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="passwordForm">
                <div class="form-group mb-4">
                    <label class="form-label">Current Password</label>
                    <input type="password" id="current_password" class="form-input" required>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">New Password</label>
                    <input type="password" id="new_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" id="new_password_confirmation" class="form-input" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary close-modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="savePasswordBtn">Update Password</button>
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

        editAvatarBtn.addEventListener('click', () => avatarInput.click());

        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (avatarPreview) {
                        avatarPreview.src = e.target.result;
                    } else {
                        document.getElementById('avatarPreview').innerHTML = `<img src="${e.target.result}" class="avatar-image large">`;
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Simple modal implementation
        const modals = {
            phone: document.getElementById('phoneModal'),
            password: document.getElementById('passwordModal')
        };

        const openModal = (id) => modals[id].classList.add('active');
        const closeModal = (id) => modals[id].classList.remove('active');

        document.getElementById('addPhoneBtn')?.addEventListener('click', () => openModal('phone'));
        document.getElementById('updatePhoneBtn')?.addEventListener('click', () => openModal('phone'));
        document.getElementById('changePassBtn')?.addEventListener('click', () => openModal('password'));

        document.querySelectorAll('.modal-close, .close-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
            });
        });

        // Add phone verification logic
        document.getElementById('savePhoneBtn')?.addEventListener('click', async function() {
            const phone = document.getElementById('new_phone').value;
            const saveBtn = this;
            const originalText = saveBtn.innerText;

            if (!phone) {
                alert('Please enter a phone number.');
                return;
            }

            // Disable button and show loading state
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
                    // Update masked phone display if it exists
                    const displayPhone = document.getElementById('display_phone');
                    if (displayPhone) {
                        displayPhone.value = data.masked_phone || data.phone;
                    }

                    // Update badges (remove verified badge if present)
                    const verifiedBadge = document.querySelector('.verified-badge');
                    if (verifiedBadge) {
                        verifiedBadge.remove();
                    }
                    
                    // Show success message
                    alert(data.message);
                    closeModal('phone');
                    
                    // Optionally reload to update all indicators if needed, 
                    // but dynamic update is smoother.
                    // window.location.reload(); 
                } else {
                    // Handle validation errors
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

        document.getElementById('savePasswordBtn')?.addEventListener('click', async function() {
            // AJAX call to update password
            alert('Password update logic would go here.');
            closeModal('password');
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
                deleteConfirmationText.value = '';
                deletePasswordConfirm.value = '';
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
            });
        }

        function validateDeleteForm() {
            if (deleteConfirmationText.value === 'DELETE' && deletePasswordConfirm.value.length >= 8) {
                confirmDeleteBtn.disabled = false;
                confirmDeleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        if (deleteConfirmationText && deletePasswordConfirm) {
            deleteConfirmationText.addEventListener('input', validateDeleteForm);
            deletePasswordConfirm.addEventListener('input', validateDeleteForm);
        }
    });
</script>
@endpush
