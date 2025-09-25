// Modal functionality
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
    }
}

function showPhoneVerificationModal() {
    closeModal('phoneUpdateModal');
    showModal('phoneVerificationModal');
}

function showPasswordChangeModal() {
    showModal('passwordChangeModal');
}

function showDeleteAccountModal() {
    showModal('deleteAccountModal');
}

// Initialize modals
document.addEventListener('DOMContentLoaded', function() {
    // Close modals when clicking outside
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });

    // Close buttons
    document.querySelectorAll('.modal-close').forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
});

// Export functions to global scope
window.closeModal = closeModal;
window.showModal = showModal;
window.showPhoneVerificationModal = showPhoneVerificationModal;
window.showPasswordChangeModal = showPasswordChangeModal;
window.showDeleteAccountModal = showDeleteAccountModal;
