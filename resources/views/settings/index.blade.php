@extends('settings.layout')

@section('title', 'Account Settings')
@section('breadcrumb', 'Profile')

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h1 class="page-title">Account Settings</h1>
            <p class="page-description">Manage your profile information and account security.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('profile.show') }}" style="background-color: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color); padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 500; text-decoration: none; transition: all 0.2s;">
                View Profile
            </a>
            <button type="submit" form="profile-form" style="background-color: var(--primary-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 500; cursor: pointer; transition: background-color 0.2s;">
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Profile Card -->
<div style="background-color: var(--bg-card); border-radius: 1rem; padding: 1.5rem; border: 1px solid var(--border-color); margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
    <div style="display: flex; align-items: center; gap: 1.5rem;">
        <div style="position: relative;">
            <img src="{{ $user->avatar ? (str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . $user->avatar)) : 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" 
                 alt="Profile" 
                 style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
        </div>
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">{{ $user->name }}</h2>
            <div style="color: var(--text-secondary); margin-bottom: 0.5rem;">{{ $user->email }}</div>
            <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.875rem;">
                <span style="background-color: #dbeafe; color: #1e40af; padding: 0.125rem 0.625rem; border-radius: 9999px; font-weight: 600; font-size: 0.75rem;">
                    {{ $user->currentSubscription ? $user->currentSubscription->pricingPlan->name : 'FREE STUDENT' }}
                </span>
                <span style="color: var(--text-muted);">Joined {{ $user->created_at->format('M Y') }}</span>
            </div>
        </div>
    </div>
    <button onclick="document.getElementById('avatar-input').click()" style="display: flex; align-items: center; gap: 0.5rem; background-color: var(--bg-body); color: var(--text-main); border: 1px solid var(--border-color); padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 500; cursor: pointer; transition: all 0.2s;">
        <i class="fas fa-pencil-alt" style="font-size: 0.875rem;"></i>
        Edit Avatar
    </button>
    <form id="avatar-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" style="display: none;">
        @csrf
        @method('PUT')
        <input type="file" id="avatar-input" name="avatar" accept="image/*" onchange="document.getElementById('avatar-form').submit()">
    </form>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Notification Summary -->
    <div>
        <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin-bottom: 1rem;">Notification Summary</h3>
        <div style="background-color: var(--bg-card); border-radius: 1rem; overflow: hidden; border: 1px solid var(--border-color); box-shadow: 0 1px 2px rgba(0,0,0,0.05); height: 100%;">
            <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 2rem; color: white;">
                <i class="fas fa-bell" style="font-size: 2.5rem; opacity: 0.9;"></i>
            </div>
            <div style="padding: 1.5rem;">
                <h4 style="font-weight: 700; margin-bottom: 0.5rem;">Email & Push Alerts</h4>
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1.5rem;">
                    Get real-time updates on assignments, grades, and campus news.
                </p>
                
                <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 500; color: var(--text-secondary);">Course Updates</span>
                        <div style="width: 44px; height: 24px; background-color: var(--primary-color); border-radius: 9999px; position: relative;">
                            <div style="width: 20px; height: 20px; background-color: white; border-radius: 50%; position: absolute; right: 2px; top: 2px;"></div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 500; color: var(--text-secondary);">New Messages</span>
                        <div style="width: 44px; height: 24px; background-color: var(--border-color); border-radius: 9999px; position: relative;">
                            <div style="width: 20px; height: 20px; background-color: white; border-radius: 50%; position: absolute; left: 2px; top: 2px;"></div>
                        </div>
                    </div>
                </div>
                
                <a href="{{ route('settings.notifications') }}" style="display: block; width: 100%; text-align: center; background-color: var(--primary-color); color: white; padding: 0.75rem; border-radius: 0.5rem; text-decoration: none; font-weight: 500; transition: background-color 0.2s;">
                    Manage All Settings
                </a>
            </div>
        </div>
    </div>

    <!-- Account Security -->
    <div>
        <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin-bottom: 1rem;">Account Security</h3>
        <div style="background-color: var(--bg-card); border-radius: 1rem; overflow: hidden; border: 1px solid var(--border-color); box-shadow: 0 1px 2px rgba(0,0,0,0.05); height: 100%;">
            <div style="background-color: #111827; padding: 2rem; color: white;">
                <i class="fas fa-shield-alt" style="font-size: 2.5rem; opacity: 0.9;"></i>
            </div>
            <div style="padding: 1.5rem;">
                <h4 style="font-weight: 700; margin-bottom: 0.5rem;">Privacy & Access</h4>
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1.5rem;">
                    Protect your account with advanced security features and login logs.
                </p>
                
                <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="color: var(--success);"><i class="fas fa-check-circle"></i></div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem;">2FA is Active</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Protection via Authenticator App</div>
                            </div>
                        </div>
                        <a href="#" style="font-size: 0.875rem; font-weight: 600; color: var(--primary-color); text-decoration: none;">Edit</a>
                    </div>
                    
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="color: var(--warning);"><i class="fas fa-history"></i></div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem;">Password Health</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Changed 3 months ago</div>
                            </div>
                        </div>
                        <a href="#" style="font-size: 0.875rem; font-weight: 600; color: var(--primary-color); text-decoration: none;">Update</a>
                    </div>
                </div>
                
                <button style="width: 100%; background-color: var(--bg-body); color: var(--text-main); border: 1px solid var(--border-color); padding: 0.75rem; border-radius: 0.5rem; font-weight: 500; cursor: pointer;">
                    View Login Activity
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Personal Information Form -->
<div style="margin-bottom: 2rem;">
    <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--text-main); margin-bottom: 1rem;">Personal Information</h3>
    <div style="background-color: var(--bg-card); border-radius: 1rem; padding: 2rem; border: 1px solid var(--border-color); box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
        <form id="profile-form" action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--text-secondary); margin-bottom: 0.5rem;">First Name</label>
                    <input type="text" name="first_name" value="{{ explode(' ', $user->name)[0] }}" style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background-color: var(--bg-body); color: var(--text-main); font-family: inherit;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--text-secondary); margin-bottom: 0.5rem;">Last Name</label>
                    <input type="text" name="last_name" value="{{ explode(' ', $user->name, 2)[1] ?? '' }}" style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background-color: var(--bg-body); color: var(--text-main); font-family: inherit;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--text-secondary); margin-bottom: 0.5rem;">Email Address</label>
                    <div style="position: relative;">
                        <i class="fas fa-envelope" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="email" name="email" value="{{ $user->email }}" style="width: 100%; padding: 0.75rem 0.75rem 0.75rem 2.5rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background-color: var(--bg-body); color: var(--text-main); font-family: inherit;">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--text-secondary); margin-bottom: 0.5rem;">Phone Number</label>
                    <div style="position: relative;">
                        <i class="fas fa-phone" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="tel" name="phone" value="{{ $user->phone }}" style="width: 100%; padding: 0.75rem 0.75rem 0.75rem 2.5rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background-color: var(--bg-body); color: var(--text-main); font-family: inherit;">
                    </div>
                </div>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: var(--text-secondary); margin-bottom: 0.5rem;">Bio</label>
                <textarea name="bio" rows="4" style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background-color: var(--bg-body); color: var(--text-main); font-family: inherit; resize: vertical;">{{ $user->bio ?? '' }}</textarea>
                <div style="font-size: 0.75rem; color: var(--text-muted); text-align: right; margin-top: 0.25rem;">Brief description for your profile.</div>
            </div>
        </form>
    </div>
</div>
@endsection
