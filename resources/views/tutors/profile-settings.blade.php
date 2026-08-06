@extends('layouts.tutors-layout')

@section('sidebar')
    @include('components.tutors-sidebar')
@endsection

@section('filter-bar')
    <div style="flex: 1; display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-main);">Tutor Profile & Payout Settings</h2>
    </div>
@endsection

@section('content')
    <div style="padding: 2rem; max-width: 1000px; margin: 0 auto;">
        @if(session('success'))
            <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; border: 1px solid #a7f3d0;">
                <i class="fa-solid fa-circle-check" style="margin-right: 0.5rem;"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; border: 1px solid #fca5a5;">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('tutors.profile.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Profile Summary Card -->
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 2rem; padding: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                    <i class="fa-solid fa-user-gear" style="color: var(--secondary-blue); margin-right: 0.35rem;"></i> Public Profile Information
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Full Legal Name</label>
                        <input type="text" name="legal_name" value="{{ old('legal_name', $tutorProfile->legal_name) }}" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Professional Tagline</label>
                        <input type="text" name="tagline" value="{{ old('tagline', $tutorProfile->tagline) }}" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Bio & Teaching Philosophy</label>
                    <textarea name="bio" rows="4" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-family: inherit;">{{ old('bio', $tutorProfile->bio) }}</textarea>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Qualifications & Academic Background</label>
                    <textarea name="qualifications" rows="3" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-family: inherit;">{{ old('qualifications', $tutorProfile->qualifications) }}</textarea>
                </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Intro Video URL (YouTube / Vimeo)</label>
                        <input type="url" name="intro_video_url" value="{{ old('intro_video_url', $tutorProfile->intro_video_url) }}" placeholder="https://www.youtube.com/watch?v=..." style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Scheduling Preference</label>
                        <select name="scheduling_preference" id="schedPrefSelect" onchange="toggleSchedPref()" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-weight: 600;">
                            <option value="in_app" @selected(old('scheduling_preference', $tutorProfile->scheduling_preference) === 'in_app')>DigiLearn In-App Calendar (Recommended)</option>
                            <option value="external" @selected(old('scheduling_preference', $tutorProfile->scheduling_preference) === 'external')>External Link (Calendly etc.)</option>
                        </select>
                    </div>
                </div>

                <div id="externalLinkSettings" style="margin-top: 1.25rem; {{ old('scheduling_preference', $tutorProfile->scheduling_preference) === 'external' ? '' : 'display: none;' }}">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">External Scheduling URL</label>
                    <input type="url" name="scheduling_link" id="schedLinkInput" value="{{ old('scheduling_link', $tutorProfile->scheduling_link) }}" placeholder="https://calendly.com/your-username" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                </div>

                <div style="margin-top: 1.25rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Communication Handle (Google Meet / Zoom / Skype)</label>
                    <input type="text" name="communication_handle" value="{{ old('communication_handle', $tutorProfile->communication_handle) }}" required style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                </div>
            </div>

            <!-- Payout Configuration Card -->
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 2rem; padding: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                    <i class="fa-solid fa-credit-card" style="color: #10b981; margin-right: 0.35rem;"></i> Paystack Settlement & Payout Details
                </h3>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Preferred Payout Method</label>
                    <select name="payout_method" id="payoutMethodSelect" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main); font-weight: 600;">
                        <option value="momo" @selected(old('payout_method', $tutorProfile->payout_method) === 'momo')>Mobile Money (MTN, Telecel, AT)</option>
                        <option value="bank" @selected(old('payout_method', $tutorProfile->payout_method) === 'bank')>Bank Account Transfer</option>
                    </select>
                </div>

                <!-- MoMo Fields -->
                <div id="momoFields" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Network Provider</label>
                        <select name="payout_momo_network" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                            <option value="MTN" @selected(old('payout_momo_network', $tutorProfile->payout_momo_network) === 'MTN')>MTN Mobile Money</option>
                            <option value="Telecel" @selected(old('payout_momo_network', $tutorProfile->payout_momo_network) === 'Telecel')>Telecel Cash</option>
                            <option value="AT" @selected(old('payout_momo_network', $tutorProfile->payout_momo_network) === 'AT')>AT Money</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Mobile Money Phone Number</label>
                        <input type="text" name="payout_momo_number" value="{{ old('payout_momo_number', $tutorProfile->payout_momo_number) }}" placeholder="e.g. 0244123456" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                    </div>
                </div>

                <!-- Bank Fields -->
                <div id="bankFields" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Bank Name</label>
                        <input type="text" name="payout_bank_name" value="{{ old('payout_bank_name', $tutorProfile->payout_bank_name) }}" placeholder="e.g. GCB Bank" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Account Name</label>
                        <input type="text" name="payout_bank_account_name" value="{{ old('payout_bank_account_name', $tutorProfile->payout_bank_account_name) }}" placeholder="Name on bank account" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Account Number</label>
                        <input type="text" name="payout_bank_account_number" value="{{ old('payout_bank_account_number', $tutorProfile->payout_bank_account_number) }}" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.35rem;">Branch</label>
                        <input type="text" name="payout_bank_branch" value="{{ old('payout_bank_branch', $tutorProfile->payout_bank_branch) }}" style="width: 100%; padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-main);">
                    </div>
                </div>
            </div>

            <!-- Subjects & Hourly Rates Card -->
            <div style="background: var(--bg-surface); border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 2rem; padding: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                    <i class="fa-solid fa-book-open" style="color: var(--primary-red); margin-right: 0.35rem;"></i> Teaching Subjects & Hourly Rates (GHS)
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    @foreach($allSubjects as $subject)
                        @php
                            $isCheck = isset($tutorSubjects[$subject->id]);
                            $rate = $tutorSubjects[$subject->id] ?? 50.00;
                        @endphp
                        <div style="border: 1px solid var(--border-color); border-radius: 10px; padding: 0.85rem; background: var(--bg-main);">
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--text-main); cursor: pointer; margin-bottom: 0.5rem;">
                                <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" @checked($isCheck)>
                                {{ $subject->name }}
                            </label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Rate (GHS/hr):</span>
                                <input type="number" step="0.50" min="0" name="rates[{{ $subject->id }}]" value="{{ $rate }}" style="width: 100px; padding: 0.35rem 0.5rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-main); font-weight: 600;">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div style="text-align: right; margin-bottom: 3rem;">
                <button type="submit" style="background: var(--secondary-blue); color: white; border: none; padding: 0.85rem 2rem; border-radius: 10px; font-weight: 700; font-size: 1rem; cursor: pointer; box-shadow: var(--shadow-sm);">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 0.5rem;"></i> Save Profile & Payout Settings
                </button>
            </div>
        </form>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            const methodSelect = document.getElementById('payoutMethodSelect');
            const momoFields = document.getElementById('momoFields');
            const bankFields = document.getElementById('bankFields');

            function togglePayoutFields() {
                if (methodSelect.value === 'momo') {
                    momoFields.style.display = 'grid';
                    bankFields.style.display = 'none';
                } else {
                    momoFields.style.display = 'none';
                    bankFields.style.display = 'grid';
                }
            }

            methodSelect.addEventListener('change', togglePayoutFields);
            togglePayoutFields();
        });

        function toggleSchedPref() {
            const sel = document.getElementById('schedPrefSelect');
            const container = document.getElementById('externalLinkSettings');
            const input = document.getElementById('schedLinkInput');
            if (sel.value === 'external') {
                container.style.display = '';
                input.required = true;
            } else {
                container.style.display = 'none';
                input.required = false;
                input.value = '';
            }
        }
    </script>
@endsection
