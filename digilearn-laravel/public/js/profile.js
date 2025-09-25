// Profile form submission
document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profileForm');
    const input = document.getElementById('avatarInput');

    // Preview avatar image when selected
    if(!input) return;

    // Avoid double-binding in case of hot-reloads or partial mounts
    if (input.dataset.previewBound === '1') return;
    input.dataset.previewBound = '1';

    input.addEventListener('change', function(){
        if(this.files && this.files[0]){
            const reader = new FileReader();
            const preview = document.getElementById('avatarPreview');
            let avatarImage = document.getElementById('avatarImage');

            reader.onload = (e) => {
                if (!avatarImage) {
                    avatarImage = document.createElement('img');
                    avatarImage.id = 'avatarImage';
                    avatarImage.className = 'avatar-image';
                    preview.innerHTML = '';
                    preview.appendChild(avatarImage);
                }
                avatarImage.src = e.target.result;
            };

            reader.readAsDataURL(this.files[0]);

            // Use existing toast helper if available
            if (typeof showNotification === 'function') {
                showNotification('Click "Update" to update your profile picture', 'info');
            }
            
        }
    });

    if (!profileForm) return;

    profileForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;
        
        try {
            const formData = new FormData(this);
            
            // Log form data for debugging
            console.log('Form data being submitted:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}:`, value);
            }
            
            const response = await fetch(profileForm.getAttribute('action'), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                // Handle validation errors
                if (response.status === 422 && data.errors) {
                    let errorMessage = 'Please correct the following issues:';
                    for (const [field, errors] of Object.entries(data.errors)) {
                        // Format field name to be more readable
                        const fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        errorMessage += `\n- ${fieldName}: ${errors[0]}`;
                    }
                    showNotification(errorMessage, 'error');
                    return;
                }
                throw new Error(data.message || 'Failed to update profile');
            }
            
            // Success case
            if (data.avatar_url) {
                const avatarImage = document.getElementById('avatarImage');
                if (avatarImage) {
                    avatarImage.src = data.avatar_url + '?v=' + new Date().getTime();
                } else {
                    const preview = document.getElementById('avatarPreview');
                    if (preview) {
                        preview.innerHTML = `<img src="${data.avatar_url}?v=${new Date().getTime()}" alt="Profile" class="avatar-image" id="avatarImage">`;
                    }
                }
            }
            
            showNotification('Profile updated successfully!', 'success');
            
        } catch (error) {
            console.error('Error updating profile:', error);
            showNotification(error.message || 'An error occurred while updating your profile', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });

    // Initialize other profile-related functionality here
    initializeProfilePage();
});

function initializeProfilePage() {
    // Harden autocomplete/autofill across the profile page
    const setAttr = (id, name, value) => {
        const el = document.getElementById(id);
        if (el) el.setAttribute(name, value);
    };

    // Personal information
    setAttr('first_name', 'autocomplete', 'given-name');
    setAttr('last_name', 'autocomplete', 'family-name');
    setAttr('email', 'autocomplete', 'email');
    setAttr('phone', 'autocomplete', 'tel');
    const phone = document.getElementById('phone');
    if (phone) phone.setAttribute('inputmode', 'tel');
    setAttr('date_of_birth', 'autocomplete', 'bday');

    // Location & Education
    setAttr('country', 'autocomplete', 'country-name');
    setAttr('city', 'autocomplete', 'address-level2');
    setAttr('education_level', 'autocomplete', 'off');
    setAttr('grade', 'autocomplete', 'off');

    // Preferences
    setAttr('preferred_language', 'autocomplete', 'off');
    setAttr('learning_style', 'autocomplete', 'off');
    const bio = document.getElementById('bio');
    if (bio) bio.setAttribute('autocomplete', 'off');

    // Country dropdown searches
    setAttr('countrySearch', 'autocomplete', 'off');
    setAttr('modalCountrySearch', 'autocomplete', 'off');

    // Modal phone & passwords
    setAttr('modal_phone', 'autocomplete', 'tel');
    const modalPhone = document.getElementById('modal_phone');
    if (modalPhone) modalPhone.setAttribute('inputmode', 'tel');
    setAttr('modal_current_password', 'autocomplete', 'current-password');

    // Verification code
    const v = document.getElementById('verification_code');
    if (v) {
        v.setAttribute('autocomplete', 'one-time-code');
        v.setAttribute('inputmode', 'numeric');
        v.setAttribute('pattern', '\\d*');
    }

    // Password change modal
    setAttr('current_password_change', 'autocomplete', 'current-password');
    setAttr('new_password', 'autocomplete', 'new-password');
    setAttr('new_password_confirmation', 'autocomplete', 'new-password');

    // Delete account modal
    setAttr('delete_password', 'autocomplete', 'current-password');

    // Country hidden inputs and codes shouldn't be autocompleted
    const countryCode = document.getElementById('country_code');
    if (countryCode) countryCode.setAttribute('autocomplete', 'off');
    const modalCountryCode = document.getElementById('modal_country_code');
    if (modalCountryCode) modalCountryCode.setAttribute('autocomplete', 'off');

    // Ensure avatar preview also works (safety net if other handlers failed)
    const input = document.getElementById('avatarInput');
    if (input && input.dataset.previewBound !== '1') {
        input.dataset.previewBound = '1';
        input.addEventListener('change', function(){
            if (this.files && this.files[0]){
                const reader = new FileReader();
                const preview = document.getElementById('avatarPreview');
                let avatarImage = document.getElementById('avatarImage');
                reader.onload = (e) => {
                    if (!avatarImage) {
                        avatarImage = document.createElement('img');
                        avatarImage.id = 'avatarImage';
                        avatarImage.className = 'avatar-image';
                        if (preview) { preview.innerHTML = ''; preview.appendChild(avatarImage); }
                    }
                    avatarImage.src = e.target.result;
                    if (typeof window.showNotification === 'function') {
                        showNotification('Click "Update" to update your profile picture', 'info');
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    console.log('Profile page initialized');
}

// Make showNotification available globally
window.showNotification = function(message, type = 'info') {
    const toast = document.getElementById('notificationToast');
    const icon = document.getElementById('toastIcon');
    const messageEl = document.getElementById('toastMessage');
    
    if (!toast || !icon || !messageEl) return;
    
    // Set icon based on type
    let iconHtml = '';
    switch(type) {
        case 'success':
            iconHtml = '<i class="fas fa-check-circle"></i>';
            break;
        case 'error':
            iconHtml = '<i class="fas fa-exclamation-circle"></i>';
            break;
        case 'warning':
            iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
            break;
        default:
            iconHtml = '<i class="fas fa-info-circle"></i>';
    }
    
    // Set message and show
    icon.innerHTML = iconHtml;
    messageEl.textContent = message;
    toast.classList.add('show');
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        toast.classList.remove('show');
    }, 5000);
};
