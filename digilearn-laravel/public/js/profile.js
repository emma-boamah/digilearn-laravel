// Profile form submission
document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profileForm');
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
    // Add any initialization code here
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
