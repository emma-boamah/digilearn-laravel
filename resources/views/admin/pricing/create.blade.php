@extends('layouts.admin')

@section('title', 'Create Pricing Plan')
@section('page-title', 'Create New Pricing Plan')
@section('page-description', 'Set up a new subscription pricing plan for your platform.')

@section('content')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .pricing-form-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem;
        background-color: var(--white);
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        border: 1px solid var(--gray-200);
    }

    .tabs {
        display: flex;
        border-bottom: 1px solid var(--gray-200);
        margin-bottom: 2rem;
    }

    .tab-button {
        padding: 0.75rem 1.5rem;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        color: var(--gray-600);
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .tab-button.active {
        color: var(--primary-blue);
        border-bottom-color: var(--primary-blue);
    }

    .tab-button:hover {
        color: var(--primary-blue);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid var(--gray-200);
    }

    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 1rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .form-label.required::after {
        content: ' *';
        color: var(--accent-red);
    }

    .form-input,
    .form-select,
    .form-textarea {
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid var(--gray-300);
        background-color: var(--white);
        color: var(--gray-900);
        transition: all 0.2s;
        font-size: 0.875rem;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px var(--primary-blue-light);
    }

    .form-textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-error {
        color: var(--accent-red);
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .checkbox-group {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-top: 0.5rem;
    }

    .form-checkbox {
        width: 1.25rem;
        height: 1.25rem;
        margin-top: 0.125rem;
        flex-shrink: 0;
    }

    .checkbox-label {
        display: flex;
        flex-direction: column;
        cursor: pointer;
    }

    .checkbox-text {
        font-weight: 500;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }

    .checkbox-description {
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    .features-section {
        margin-top: 1rem;
    }

    .features-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background-color: #f8fafc;
        border-radius: 0.5rem;
        border: 1px solid var(--gray-200);
    }

    .feature-input {
        flex: 1;
        border: none;
        background: transparent;
        font-size: 0.875rem;
        color: var(--gray-900);
    }

    .feature-input:focus {
        outline: none;
    }

    .feature-remove {
        background: none;
        border: none;
        color: var(--gray-400);
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 0.25rem;
        transition: all 0.2s;
    }

    .feature-remove:hover {
        background-color: var(--gray-200);
        color: var(--accent-red);
    }

    .add-feature-btn {
        background-color: var(--primary-blue-light);
        color: var(--primary-blue);
        border: 1px solid var(--primary-blue);
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .add-feature-btn:hover {
        background-color: var(--primary-blue);
        color: var(--white);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding-top: 2rem;
        border-top: 1px solid var(--gray-200);
    }

    .btn-primary {
        background-color: var(--primary-blue);
        color: var(--white);
        padding: 0.75rem 2rem;
        border-radius: 0.5rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary:hover {
        background-color: var(--primary-blue-hover);
    }

    .btn-secondary {
        background-color: var(--gray-100);
        color: var(--gray-700);
        padding: 0.75rem 2rem;
        border-radius: 0.5rem;
        font-weight: 500;
        border: 1px solid var(--gray-300);
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-secondary:hover {
        background-color: var(--gray-200);
    }

    @media (max-width: 768px) {
        .pricing-form-container {
            padding: 1rem;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }
    }
</style>

<div class="pricing-form-container">
    <form method="POST" action="{{ route('admin.pricing.store') }}">
        @csrf

        <!-- Tabs Navigation -->
        <div class="tabs">
            <button type="button" class="tab-button active" onclick="switchTab('basic')">Basic Information</button>
            <button type="button" class="tab-button" onclick="switchTab('discounts')">Discount Tiers</button>
        </div>

        <!-- Basic Information -->
                <div id="basic-tab" class="tab-content active">
                <div class="form-section">
            <h3 class="section-title">Basic Information</h3>

            <div class="form-grid">
                <div class="form-group">
                    <label for="name" class="form-label required">Plan Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="form-input" placeholder="e.g., Basic Plan, Premium Plan">
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="slug" class="form-label required">Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required
                           class="form-input" placeholder="basic-plan">
                    @error('slug')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="price" class="form-label required">Price</label>
                    <input type="number" name="price" id="price" value="{{ old('price', 0) }}" required
                           min="0" step="0.01" class="form-input" placeholder="0.00">
                    @error('price')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="currency" class="form-label required">Currency</label>
                    <select name="currency" id="currency" required class="form-select">
                        <option value="GHS" {{ old('currency', 'GHS') == 'GHS' ? 'selected' : '' }}>GHS (Ghana Cedi)</option>
                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                    </select>
                    @error('currency')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="period" class="form-label required">Billing Period</label>
                    <select name="period" id="period" required class="form-select">
                        <option value="monthly" {{ old('period', 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ old('period') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                        <option value="lifetime" {{ old('period') == 'lifetime' ? 'selected' : '' }}>Lifetime</option>
                        <option value="one-time" {{ old('period') == 'one-time' ? 'selected' : '' }}>One-time</option>
                    </select>
                    @error('period')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="duration_days" class="form-label">Duration (Days)</label>
                    <input type="number" name="duration_days" id="duration_days" value="{{ old('duration_days') }}"
                           min="1" class="form-input" placeholder="e.g., 30 for monthly">
                    <small style="color: var(--gray-500); font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                        Optional: Number of days this plan is valid for
                    </small>
                    @error('duration_days')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" rows="3" class="form-textarea"
                          placeholder="Describe what this plan includes...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

        </div>

        <!-- Features -->
        <div class="form-section">
            <h3 class="section-title">Plan Features</h3>
            <p style="color: var(--gray-600); font-size: 0.875rem; margin-bottom: 1rem;">
                List the features and benefits included in this pricing plan.
            </p>

            <div class="features-section">
                <div id="features-list" class="features-list">
                    @if(old('features'))
                        @foreach(old('features') as $index => $feature)
                        <div class="feature-item">
                            <svg style="width: 1rem; height: 1rem; color: var(--primary-blue); flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <input type="text" name="features[]" value="{{ $feature }}" class="feature-input" placeholder="Enter a feature..." required>
                            <button type="button" class="feature-remove" onclick="removeFeature(this)">
                                <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        @endforeach
                    @else
                        <div class="feature-item">
                            <svg style="width: 1rem; height: 1rem; color: var(--primary-blue); flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <input type="text" name="features[]" class="feature-input" placeholder="Enter a feature..." required>
                            <button type="button" class="feature-remove" onclick="removeFeature(this)">
                                <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>

                <button type="button" class="add-feature-btn" onclick="addFeature()">
                    <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Feature
                </button>
            </div>

            @error('features')
                <div class="form-error">{{ $message }}</div>
            @enderror
            @error('features.*')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <!-- Settings -->
        <div class="form-section">
            <h3 class="section-title">Plan Settings</h3>

            <div class="form-grid">
                <div class="form-group">
                    <label for="sort_order" class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}"
                           min="0" class="form-input" placeholder="0">
                    <small style="color: var(--gray-500); font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                        Lower numbers appear first in the list
                    </small>
                    @error('sort_order')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="form-checkbox">
                <label for="is_active" class="checkbox-label">
                    <span class="checkbox-text">Active Plan</span>
                    <span class="checkbox-description">Make this plan available for purchase</span>
                </label>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="form-checkbox">
                <label for="is_featured" class="checkbox-label">
                    <span class="checkbox-text">Featured Plan</span>
                    <span class="checkbox-description">Highlight this plan as a recommended option</span>
                </label>
            </div>
        </div>
                </div>
                
                        <div id="discounts-tab" class="tab-content">
                        <!-- Discount Tiers -->
                        <div class="form-section">
                            <h3 class="section-title">Discount Tiers</h3>
                            <p style="color: var(--gray-600); font-size: 0.875rem; margin-bottom: 1rem;">
                                Define discount pricing for different subscription durations.
                            </p>
                            <div class="features-section">
                                <div id="discount-tiers-list" class="features-list">
                                    <div class="feature-item">
                                        <div style="display: flex; gap: 1rem; align-items: center; flex: 1;">
                                            <div style="flex: 1;">
                                                <label style="font-size: 0.75rem; color: var(--gray-600);">Duration (Months)</label>
                                                <input type="number" name="discount_tiers[0][duration_months]" class="form-input" placeholder="3" min="1" required>
                                            </div>
                                            <div style="flex: 1;">
                                                <label style="font-size: 0.75rem; color: var(--gray-600);">Discount Percentage (%)</label>
                                                <input type="number" name="discount_tiers[0][discount_percentage]" class="form-input" placeholder="10" step="0.01" min="0" max="100" required>
                                            </div>
                                        </div>
                                        <button type="button" class="feature-remove" onclick="removeDiscountTier(this)">
                                            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="add-feature-btn" onclick="addDiscountTier()">
                                    <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add Discount Tier
                                </button>
                            </div>
                            @error('discount_tiers')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            @error('discount_tiers.*')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        </div>
                
                        <!-- Form Actions -->
        <div class="form-actions">
            <a href="{{ route('admin.pricing.index') }}" class="btn-secondary">
                <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Cancel
            </a>
            <button type="submit" class="btn-primary">
                <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Create Pricing Plan
            </button>
        </div>
    </form>
</div>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
function addFeature() {
    const featuresList = document.getElementById('features-list');
    const featureItem = document.createElement('div');
    featureItem.className = 'feature-item';
    featureItem.innerHTML = `
        <svg style="width: 1rem; height: 1rem; color: var(--primary-blue); flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <input type="text" name="features[]" class="feature-input" placeholder="Enter a feature..." required>
        <button type="button" class="feature-remove" onclick="removeFeature(this)">
            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    featuresList.appendChild(featureItem);
}

function removeFeature(button) {
    const featureItem = button.closest('.feature-item');
    const featuresList = document.getElementById('features-list');
    const featureItems = featuresList.querySelectorAll('.feature-item');

    // Keep at least one feature
    if (featureItems.length > 1) {
        featureItem.remove();
    } else {
        // Clear the input instead of removing the last item
        const input = featureItem.querySelector('.feature-input');
        input.value = '';
        input.focus();
    }
}

// Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const slug = name.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
        .replace(/\s+/g, '-') // Replace spaces with hyphens
        .replace(/-+/g, '-') // Replace multiple hyphens with single hyphen
        .trim(); // Trim whitespace

    document.getElementById('slug').value = slug;
});

function switchTab(tab) {
    const tabs = document.querySelectorAll('.tab-button');
    const contents = document.querySelectorAll('.tab-content');
    tabs.forEach(t => t.classList.remove('active'));
    contents.forEach(c => c.classList.remove('active'));
    document.querySelector(`[onclick="switchTab('${tab}')"]`).classList.add('active');
    document.getElementById(`${tab}-tab`).classList.add('active');
}

function addDiscountTier() {
    const list = document.getElementById('discount-tiers-list');
    const index = list.children.length;
    const item = document.createElement('div');
    item.className = 'feature-item';
    item.innerHTML = `
        <div style="display: flex; gap: 1rem; align-items: center; flex: 1;">
            <div style="flex: 1;">
                <label style="font-size: 0.75rem; color: var(--gray-600);">Duration (Months)</label>
                <input type="number" name="discount_tiers[${index}][duration_months]" class="form-input" placeholder="3" min="1" required>
            </div>
            <div style="flex: 1;">
                <label style="font-size: 0.75rem; color: var(--gray-600);">Discount Percentage (%)</label>
                <input type="number" name="discount_tiers[${index}][discount_percentage]" class="form-input" placeholder="10" step="0.01" min="0" max="100" required>
            </div>
        </div>
        <button type="button" class="feature-remove" onclick="removeDiscountTier(this)">
            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    list.appendChild(item);
}

function removeDiscountTier(button) {
    const item = button.closest('.feature-item');
    const list = document.getElementById('discount-tiers-list');
    const items = list.querySelectorAll('.feature-item');
    if (items.length > 1) {
        item.remove();
    } else {
        const inputs = item.querySelectorAll('input');
        inputs.forEach(input => input.value = '');
        inputs[0].focus();
    }
}
</script>
@endsection