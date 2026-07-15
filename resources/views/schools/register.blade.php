@extends('layouts.app')

@section('content')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        .auth-container {
            max-width: 800px;
            margin: 130px auto 60px;
            background: var(--bg-surface, #ffffff);
            border: 1px solid var(--border-color, #e5e7eb);
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .auth-subtitle {
            color: var(--text-muted, #6b7280);
        }

        /* Step Indicators */
        .step-indicator-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            position: relative;
        }

        .step-indicator-container::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 30px;
            right: 30px;
            height: 2px;
            background: var(--border-color, #e5e7eb);
            z-index: 1;
        }

        .step {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: var(--bg-surface, #ffffff);
            padding: 0 10px;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--bg-main, #f3f4f6);
            border: 2px solid var(--border-color, #e5e7eb);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--text-muted, #6b7280);
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }

        .step.active .step-circle {
            background: var(--secondary-blue, #1d9bf0);
            border-color: var(--secondary-blue, #1d9bf0);
            color: white;
        }

        .step.completed .step-circle {
            background: #10b981;
            border-color: #10b981;
            color: white;
        }

        .step-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted, #6b7280);
        }

        .step.active .step-label {
            color: var(--secondary-blue, #1d9bf0);
        }

        .form-step {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .form-step.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-main, #f9fafb);
            border: 1px solid var(--border-color, #d1d5db);
            border-radius: 8px;
            color: var(--text-main, #111827);
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--secondary-blue, #1d9bf0);
            box-shadow: 0 0 0 3px rgba(29, 155, 240, 0.1);
        }

        .input-group {
            display: flex;
            align-items: center;
        }

        .input-group-text {
            padding: 12px 16px;
            background: var(--bg-main, #f3f4f6);
            border: 1px solid var(--border-color, #d1d5db);
            border-left: none;
            border-radius: 0 8px 8px 0;
            color: var(--text-muted, #6b7280);
            font-size: 1rem;
        }

        .form-control.with-addon {
            border-radius: 8px 0 0 8px;
        }

        /* File Upload */
        .file-upload-zone {
            border: 2px dashed var(--border-color, #d1d5db);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background: var(--bg-main, #f9fafb);
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .file-upload-zone:hover {
            border-color: var(--secondary-blue, #1d9bf0);
            background: rgba(29, 155, 240, 0.02);
        }

        .file-upload-zone input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            color: var(--text-muted, #6b7280);
        }

        .file-upload-label i {
            font-size: 2rem;
            color: var(--secondary-blue, #1d9bf0);
        }

        .file-name {
            margin-top: 10px;
            font-size: 0.9rem;
            color: #10b981;
            font-weight: 600;
        }

        /* Buttons */
        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color, #e5e7eb);
        }

        .btn-prev {
            padding: 12px 24px;
            background: var(--bg-main, #f3f4f6);
            border: 1px solid var(--border-color, #d1d5db);
            border-radius: 8px;
            color: var(--text-main, #374151);
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-prev:hover {
            background: #e5e7eb;
        }

        .btn-next, .btn-submit {
            padding: 12px 24px;
            background: var(--secondary-blue, #1d9bf0);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-next:hover, .btn-submit:hover {
            background: #1a8cd8;
        }

        .text-danger {
            color: var(--primary-red, #ef4444);
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }

        .plan-summary {
            background: rgba(29, 155, 240, 0.05);
            border: 1px solid rgba(29, 155, 240, 0.2);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .plan-name-summary {
            font-weight: 600;
            color: var(--secondary-blue, #1d9bf0);
        }

        .plan-price-summary {
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .section-title {
            font-size: 1.2rem; 
            margin-bottom: 15px; 
            padding-bottom: 10px; 
            border-bottom: 1px solid var(--border-color, #e5e7eb);
            color: var(--text-main, #111827);
        }

        @media (max-width: 600px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>

    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h1 class="auth-title">Register Your School</h1>
                <p class="auth-subtitle">Create your admin account and secure your subdomain.</p>
            </div>

            <div class="plan-summary">
                <div>
                    <span style="font-size: 0.85rem; color: var(--text-muted, #6b7280)">Selected Plan:</span><br>
                    <span class="plan-name-summary">{{ $pricingPlan->name }}</span>
                </div>
                <div class="text-right">
                    <span class="plan-price-summary">{{ $pricingPlan->currency }}
                        {{ number_format($pricingPlan->price, 2) }}</span><br>
                    <span style="font-size: 0.85rem; color: var(--text-muted, #6b7280)">/ {{ $pricingPlan->billing_cycle }}</span>
                </div>
            </div>

            <!-- Step Indicators -->
            <div class="step-indicator-container" style="justify-content: center; gap: 60px;">
                <div class="step active" id="step-indicator-1">
                    <div class="step-circle">1</div>
                    <div class="step-label">School Profile</div>
                </div>
                <div class="step" id="step-indicator-2">
                    <div class="step-circle">2</div>
                    <div class="step-label">Location & Docs</div>
                </div>
            </div>

            @if ($errors->any())
                <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('school.register.submit') }}" id="registrationForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="plan_slug" value="{{ $pricingPlan->slug }}">

                <!-- STEP 1: School Profile -->
                <div class="form-step active" id="step-1">
                    <h3 class="section-title">School Details</h3>

                    <div class="form-group">
                        <label for="school_name" class="form-label">School / Organization Name *</label>
                        <input id="school_name" type="text" class="form-control" name="school_name" value="{{ old('school_name', $draft['school_name'] ?? '') }}" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="school_type" class="form-label">School Type *</label>
                            <select id="school_type" name="school_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="public" {{ old('school_type', $draft['school_type'] ?? '') == 'public' ? 'selected' : '' }}>Public School</option>
                                <option value="private" {{ old('school_type', $draft['school_type'] ?? '') == 'private' ? 'selected' : '' }}>Private School</option>
                                <option value="international" {{ old('school_type', $draft['school_type'] ?? '') == 'international' ? 'selected' : '' }}>International School</option>
                                <option value="faith_based" {{ old('school_type', $draft['school_type'] ?? '') == 'faith_based' ? 'selected' : '' }}>Faith-based School</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ges_registration_number" class="form-label">GES Registration Number *</label>
                            <input id="ges_registration_number" type="text" class="form-control" name="ges_registration_number" value="{{ old('ges_registration_number', $draft['ges_registration_number'] ?? '') }}" placeholder="e.g. GES/XX/XXXX" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="school_phone" class="form-label">School Phone Number *</label>
                            <input id="school_phone" type="tel" class="form-control" name="school_phone" value="{{ old('school_phone', $draft['school_phone'] ?? '') }}" placeholder="024XXXXXXX" required>
                        </div>
                        <div class="form-group">
                            <label for="school_email" class="form-label">Institutional Email *</label>
                            <input id="school_email" type="email" class="form-control" name="school_email" value="{{ old('school_email', $draft['school_email'] ?? '') }}" placeholder="info@school.edu.gh" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subdomain" class="form-label">Desired Subdomain *</label>
                        <div class="input-group">
                            <input id="subdomain" type="text" class="form-control with-addon" name="subdomain" value="{{ old('subdomain', $draft['subdomain'] ?? '') }}" required placeholder="my-school" style="text-transform: lowercase;">
                            <span class="input-group-text">.shoutoutgh.com</span>
                        </div>
                        <small style="color: var(--text-muted, #6b7280); font-size: 0.8rem; margin-top: 5px; display: block;">Letters, numbers, and dashes only.</small>
                    </div>

                    <div class="btn-container" style="justify-content: flex-end;">
                        <button type="button" class="btn-next" onclick="nextStep(1)">Next Step: Location & Docs <i class="fas fa-arrow-right" style="margin-left: 8px;"></i></button>
                    </div>
                </div>

                <!-- STEP 2: Location & Documents -->
                <div class="form-step" id="step-2">
                    <h3 class="section-title">Location Information</h3>
                    <p style="font-size: 0.9rem; color: var(--text-muted, #6b7280); margin-bottom: 15px;">Enter your Ghana Post GPS code to auto-fill your region.</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gps_address" class="form-label">Ghana Post GPS *</label>
                            <input id="gps_address" type="text" class="form-control" name="gps_address" value="{{ old('gps_address', $draft['gps_address'] ?? '') }}" placeholder="GA-123-4567" style="text-transform: uppercase;" required>
                        </div>
                        <div class="form-group">
                            <label for="region" class="form-label">Region *</label>
                            <select id="region" name="region" class="form-select" required>
                                <option value="">Select Region</option>
                                <option value="Greater Accra Region" {{ old('region', $draft['region'] ?? '') == 'Greater Accra Region' ? 'selected' : '' }}>Greater Accra Region</option>
                                <option value="Ashanti Region" {{ old('region', $draft['region'] ?? '') == 'Ashanti Region' ? 'selected' : '' }}>Ashanti Region</option>
                                <option value="Brong-Ahafo Region" {{ old('region', $draft['region'] ?? '') == 'Brong-Ahafo Region' ? 'selected' : '' }}>Brong-Ahafo Region</option>
                                <option value="Western Region" {{ old('region', $draft['region'] ?? '') == 'Western Region' ? 'selected' : '' }}>Western Region</option>
                                <option value="Northern Region" {{ old('region', $draft['region'] ?? '') == 'Northern Region' ? 'selected' : '' }}>Northern Region</option>
                                <option value="Upper East Region" {{ old('region', $draft['region'] ?? '') == 'Upper East Region' ? 'selected' : '' }}>Upper East Region</option>
                                <option value="Upper West Region" {{ old('region', $draft['region'] ?? '') == 'Upper West Region' ? 'selected' : '' }}>Upper West Region</option>
                                <option value="Volta Region" {{ old('region', $draft['region'] ?? '') == 'Volta Region' ? 'selected' : '' }}>Volta Region</option>
                                <option value="Central Region" {{ old('region', $draft['region'] ?? '') == 'Central Region' ? 'selected' : '' }}>Central Region</option>
                                <option value="Eastern Region" {{ old('region', $draft['region'] ?? '') == 'Eastern Region' ? 'selected' : '' }}>Eastern Region</option>
                                <option value="Ahafo Region" {{ old('region', $draft['region'] ?? '') == 'Ahafo Region' ? 'selected' : '' }}>Ahafo Region</option>
                                <option value="Bono East Region" {{ old('region', $draft['region'] ?? '') == 'Bono East Region' ? 'selected' : '' }}>Bono East Region</option>
                                <option value="North East Region" {{ old('region', $draft['region'] ?? '') == 'North East Region' ? 'selected' : '' }}>North East Region</option>
                                <option value="Oti Region" {{ old('region', $draft['region'] ?? '') == 'Oti Region' ? 'selected' : '' }}>Oti Region</option>
                                <option value="Savannah Region" {{ old('region', $draft['region'] ?? '') == 'Savannah Region' ? 'selected' : '' }}>Savannah Region</option>
                                <option value="Western North Region" {{ old('region', $draft['region'] ?? '') == 'Western North Region' ? 'selected' : '' }}>Western North Region</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="city" class="form-label">City/Town *</label>
                            <input id="city" type="text" class="form-control" name="city" value="{{ old('city', $draft['city'] ?? '') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="district" class="form-label">District/Municipality</label>
                            <input id="district" type="text" class="form-control" name="district" value="{{ old('district', $draft['district'] ?? '') }}">
                        </div>
                    </div>

                    <h3 class="section-title" style="margin-top: 30px;">Legal Documents</h3>
                    <p style="font-size: 0.9rem; color: var(--text-muted, #6b7280); margin-bottom: 15px;">Upload official documents for verification (PDF, JPG, PNG up to 5MB).</p>

                    <div class="form-group">
                        <label class="form-label">GES Registration Certificate *</label>
                        <div class="file-upload-zone">
                            <input type="file" name="ges_certificate" id="ges_certificate" accept=".pdf,.jpg,.jpeg,.png" required onchange="updateFileName(this, 'ges_name')">
                            <div class="file-upload-label">
                                <i class="fas fa-file-upload"></i>
                                <span>Click to upload or drag and drop</span>
                                <span style="font-size: 0.8rem;">GES Certificate</span>
                            </div>
                            <div id="ges_name" class="file-name"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Business Registration Certificate *</label>
                        <div class="file-upload-zone">
                            <input type="file" name="business_certificate" id="business_certificate" accept=".pdf,.jpg,.jpeg,.png" required onchange="updateFileName(this, 'biz_name')">
                            <div class="file-upload-label">
                                <i class="fas fa-file-invoice"></i>
                                <span>Click to upload or drag and drop</span>
                                <span style="font-size: 0.8rem;">Registrar General's Dept</span>
                            </div>
                            <div id="biz_name" class="file-name"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tin_number" class="form-label">Tax Identification Number (TIN) *</label>
                        <input id="tin_number" type="text" class="form-control" name="tin_number" value="{{ old('tin_number', $draft['tin_number'] ?? '') }}" placeholder="CXXXXXXXX or PXXXXXXXX" style="text-transform: uppercase;" required>
                    </div>

                    <div class="btn-container">
                        <button type="button" class="btn-prev" onclick="prevStep(2)"><i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Back</button>
                        <button type="submit" class="btn-submit"><i class="fas fa-check-circle" style="margin-right: 8px;"></i> Submit Registration</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts for Wizard and GPS -->
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        // Step Navigation
        function nextStep(currentStep) {
            // Very basic client side validation before proceeding
            const currentDiv = document.getElementById(`step-${currentStep}`);
            const inputs = currentDiv.querySelectorAll('input[required], select[required]');
            let valid = true;
            
            inputs.forEach(input => {
                if (!input.value) {
                    valid = false;
                    input.style.borderColor = 'red';
                } else {
                    input.style.borderColor = 'var(--border-color, #d1d5db)';
                }
            });
            
            if (!valid) {
                alert('Please fill in all required fields.');
                return;
            }

            document.getElementById(`step-${currentStep}`).classList.remove('active');
            document.getElementById(`step-${currentStep + 1}`).classList.add('active');
            
            document.getElementById(`step-indicator-${currentStep}`).classList.add('completed');
            document.getElementById(`step-indicator-${currentStep}`).classList.remove('active');
            document.getElementById(`step-indicator-${currentStep + 1}`).classList.add('active');
            
            window.scrollTo(0, 0);
        }

        function prevStep(currentStep) {
            document.getElementById(`step-${currentStep}`).classList.remove('active');
            document.getElementById(`step-${currentStep - 1}`).classList.add('active');
            
            document.getElementById(`step-indicator-${currentStep}`).classList.remove('active');
            document.getElementById(`step-indicator-${currentStep - 1}`).classList.add('active');
            document.getElementById(`step-indicator-${currentStep - 1}`).classList.remove('completed');
            
            window.scrollTo(0, 0);
        }

        // Subdomain formatting
        document.getElementById('subdomain').addEventListener('input', function (e) {
            let val = e.target.value.toLowerCase();
            val = val.replace(/\s+/g, '-');
            val = val.replace(/[^a-z0-9\-]/g, '');
            e.target.value = val;
        });

        // GPS to Region mapping
        const regionMap = {
            'GA': 'Greater Accra Region',
            'AK': 'Ashanti Region',
            'BA': 'Brong-Ahafo Region',
            'WR': 'Western Region',
            'NR': 'Northern Region',
            'UE': 'Upper East Region',
            'UW': 'Upper West Region',
            'VR': 'Volta Region',
            'CR': 'Central Region',
            'ER': 'Eastern Region',
            'AF': 'Ahafo Region',
            'BE': 'Bono East Region',
            'NE': 'North East Region',
            'OT': 'Oti Region',
            'SV': 'Savannah Region',
            'WN': 'Western North Region'
        };

        document.getElementById('gps_address').addEventListener('input', function(e) {
            let val = e.target.value.toUpperCase();
            
            // Auto-format GPS (e.g. GA1234567 -> GA-123-4567)
            val = val.replace(/[^A-Z0-9]/g, '');
            if (val.length > 2) val = val.substring(0, 2) + '-' + val.substring(2);
            if (val.length > 6) val = val.substring(0, 6) + '-' + val.substring(6);
            if (val.length > 11) val = val.substring(0, 11);
            
            e.target.value = val;

            // Resolve Region
            if (val.length >= 2) {
                const prefix = val.substring(0, 2);
                if (regionMap[prefix]) {
                    const select = document.getElementById('region');
                    for (let i = 0; i < select.options.length; i++) {
                        if (select.options[i].value === regionMap[prefix]) {
                            select.selectedIndex = i;
                            break;
                        }
                    }
                }
            }
        });

        // File upload text update
        function updateFileName(input, textId) {
            const fileName = input.files[0] ? input.files[0].name : '';
            document.getElementById(textId).innerText = fileName ? `Selected: ${fileName}` : '';
        }

        // AJAX Auto-Save Draft
        function saveDraft() {
            const form = document.getElementById('registrationForm');
            const formData = new FormData(form);
            
            // Remove files from draft
            formData.delete('ges_certificate');
            formData.delete('business_certificate');

            fetch('{{ route("school.register.draft") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            }).catch(console.error);
        }

        // Save draft when changing inputs
        document.querySelectorAll('#registrationForm input:not([type="file"]), #registrationForm select').forEach(input => {
            input.addEventListener('change', saveDraft);
        });
    </script>
@endsection