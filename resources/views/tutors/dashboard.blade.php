@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
    <div style="flex: 1; display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main);">Tutor Control Center</h2>
        <div>
            @if($tutorProfile->is_approved)
                <span style="background-color: #d1fae5; color: #065f46; padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-circle-check"></i> Approved Tutor Profile
                </span>
            @else
                <span style="background-color: #fef3c7; color: #92400e; padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-solid fa-clock"></i> Pending Admin Approval
                </span>
            @endif
        </div>
    </div>
@endsection

@section('content')
    <div style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
        @if(session('success'))
            <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; border: 1px solid #a7f3d0;">
                <i class="fa-solid fa-circle-check" style="margin-right: 0.5rem;"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div style="background-color: #dbeafe; color: #1e40af; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; border: 1px solid #bfdbfe;">
                <i class="fa-solid fa-circle-info" style="margin-right: 0.5rem;"></i> {{ session('info') }}
            </div>
        @endif

        @if(!$tutorProfile->is_approved)
            <div style="background: var(--bg-surface); border-radius: 16px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; padding: 2rem; text-align: center; margin-bottom: 2rem;">
                <div style="width: 64px; height: 64px; background: rgba(234, 179, 8, 0.1); color: #d97706; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1.25rem auto;">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <h3 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-main);">Application Under Review</h3>
                <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto 1.5rem auto; line-height: 1.6;">
                    Thank you for applying to be a DigiLearn tutor! Our academic administration team is reviewing your profile and credentials. You will receive access to student bookings as soon as your account is approved.
                </p>
                <a href="{{ route('tutors.profile.settings') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; background: var(--secondary-blue); color: white; padding: 0.65rem 1.25rem; border-radius: 8px; font-weight: 600; text-decoration: none;">
                    <i class="fa-solid fa-pen-to-square"></i> Review Application Profile
                </a>
            </div>
        @else

            <!-- Udemy x italki Stat Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
                <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem; box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total Earnings</span>
                        <h3 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-top: 0.25rem;">GHS {{ number_format($totalEarnings ?? 0, 2) }}</h3>
                    </div>
                    <div style="width: 50px; height: 50px; background: rgba(38, 119, 184, 0.1); color: var(--secondary-blue); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>

                <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem; box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Active Students</span>
                        <h3 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-top: 0.25rem;">{{ $activeStudentsCount ?? 0 }}</h3>
                    </div>
                    <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                </div>

                <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem; box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Pending Requests</span>
                        <h3 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-top: 0.25rem;">{{ $pendingRequestsCount ?? 0 }}</h3>
                    </div>
                    <div style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                </div>

                <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem; box-shadow: var(--shadow-sm); display: flex; align-items: justify-content: space-between;">
                    <div>
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Profile Quality</span>
                        <h3 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-top: 0.25rem;">{{ $completenessPercentage ?? 100 }}%</h3>
                    </div>
                    <div style="width: 50px; height: 50px; background: rgba(225, 30, 45, 0.1); color: var(--primary-red); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                </div>
            </div>

            <!-- Profile Completeness Bar -->
            @if(($completenessPercentage ?? 100) < 100)
                <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem 1.5rem; margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span style="font-weight: 600; font-size: 0.95rem; color: var(--text-main);">Optimize Your Profile to Attract More Student Bookings</span>
                        <span style="font-weight: 700; color: var(--secondary-blue); font-size: 0.95rem;">{{ $completenessPercentage }}% Complete</span>
                    </div>
                    <div style="width: 100%; height: 8px; background: var(--gray-100); border-radius: 9999px; overflow: hidden; margin-bottom: 0.75rem;">
                        <div style="width: {{ $completenessPercentage }}%; height: 100%; background: linear-gradient(90deg, var(--secondary-blue), var(--primary-red)); border-radius: 9999px;"></div>
                    </div>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="{{ route('tutors.profile.settings') }}" style="font-size: 0.85rem; color: var(--secondary-blue); font-weight: 600; text-decoration: none;">
                            <i class="fa-solid fa-circle-plus"></i> Add Intro Video / Profile Details
                        </a>
                    </div>
                </div>
            @endif

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                <!-- Upcoming Sessions Widget -->
                <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden;">
                    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-calendar-day" style="color: var(--secondary-blue);"></i> Upcoming Tuition Sessions
                        </h3>
                        <a href="{{ route('tutors.show', auth()->user()->id) }}" target="_blank" style="font-size: 0.85rem; font-weight: 600; color: var(--secondary-blue); text-decoration: none;">View Public Profile</a>
                    </div>
                    <div style="padding: 1.25rem;">
                        @if(isset($upcomingSessions) && count($upcomingSessions) > 0)
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                @foreach($upcomingSessions as $session)
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-main);">
                                        <div style="display: flex; gap: 1rem; align-items: center;">
                                            <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--gray-200); display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--text-main);">
                                                {{ strtoupper(substr($session->student->name ?? 'S', 0, 1)) }}
                                            </div>
                                            <div>
                                                <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; color: var(--text-main);">{{ $session->student->name ?? 'Student' }}</h4>
                                                <span style="font-size: 0.85rem; color: var(--text-muted);">Subject: {{ $session->subject->name ?? 'General' }}</span>
                                            </div>
                                        </div>
                                        <div style="text-align: right;">
                                            <span style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main);">
                                                {{ $session->start_time ? $session->start_time->format('M d, Y @ h:i A') : 'Pending Scheduling' }}
                                            </span>
                                            @if($tutorProfile->scheduling_preference === 'external' && $tutorProfile->scheduling_link)
                                                <a href="{{ $tutorProfile->scheduling_link }}" target="_blank" style="display: inline-block; margin-top: 0.25rem; font-size: 0.8rem; font-weight: 600; color: white; background: var(--secondary-blue); padding: 0.3rem 0.75rem; border-radius: 6px; text-decoration: none;">
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> External Scheduler
                                                </a>
                                            @elseif($session->meeting_link)
                                                <a href="{{ $session->meeting_link }}" target="_blank" style="display: inline-block; margin-top: 0.25rem; font-size: 0.8rem; font-weight: 600; color: white; background: var(--secondary-blue); padding: 0.3rem 0.75rem; border-radius: 6px; text-decoration: none;">
                                                    <i class="fa-solid fa-video"></i> Open Meeting Link
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="text-align: center; padding: 2rem 1rem; color: var(--text-muted);">
                                <i class="fa-regular fa-calendar-xmark" style="font-size: 2rem; margin-bottom: 0.75rem; opacity: 0.5;"></i>
                                <p style="margin: 0; font-size: 0.95rem;">No upcoming tuition sessions scheduled.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions & Profile Summary -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem; box-shadow: var(--shadow-sm);">
                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem;">Quick Actions</h3>
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <a href="{{ route('tutors.earnings.index') }}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border-color); text-decoration: none; color: var(--text-main); font-weight: 600; font-size: 0.9rem; transition: background 0.2s;">
                                <i class="fa-solid fa-wallet" style="color: var(--secondary-blue);"></i> Request Earnings Withdrawal
                            </a>
                            <a href="{{ route('tutors.profile.settings') }}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border-color); text-decoration: none; color: var(--text-main); font-weight: 600; font-size: 0.9rem; transition: background 0.2s;">
                                <i class="fa-solid fa-gear" style="color: var(--gray-600);"></i> Update Rates & Subject Bio
                            </a>
                            <a href="{{ route('tutors.show', auth()->user()->id) }}" target="_blank" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border-color); text-decoration: none; color: var(--text-main); font-weight: 600; font-size: 0.9rem; transition: background 0.2s;">
                                <i class="fa-solid fa-arrow-up-right-from-square" style="color: var(--primary-red);"></i> Preview Public Profile
                            </a>
                        </div>
                    </div>

                    <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); padding: 1.25rem; box-shadow: var(--shadow-sm);">
                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.75rem;">Payout Details</h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                            <strong>Method:</strong> {{ strtoupper($tutorProfile->payout_method ?? 'Not set') }}
                        </p>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
                            <strong>Account:</strong> 
                            @if($tutorProfile->payout_method === 'momo')
                                {{ $tutorProfile->payout_momo_network }} ({{ $tutorProfile->payout_momo_number }})
                            @elseif($tutorProfile->payout_method === 'bank')
                                {{ $tutorProfile->payout_bank_name }} - {{ $tutorProfile->payout_bank_account_number }}
                            @else
                                None configured
                            @endif
                        </p>
                    </div>
                </div>
            </div>

        @endif
    </div>
@endsection
