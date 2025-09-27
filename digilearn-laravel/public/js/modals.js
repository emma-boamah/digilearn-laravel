// Modal functionality
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function showPhoneUpdateModal() {
    showModal('phoneUpdateModal');
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

function showSubscriptionPlans() {
    showModal('subscribeUpgradeModal');
}

function showManageSubscriptionModal() {
    showModal('manageSubscriptionModal');
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
    document.querySelectorAll('.modal-close, .modal-close-btn').forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });

    // Bind modal triggers
    bindModalTriggers();
});

function bindModalTriggers() {
    // Phone modal triggers
    const updateNumberBtn = document.getElementById('updateNumberBtn');
    const addPhoneBtn = document.getElementById('addPhoneBtn');
    const verifyCurrentNumberBtn = document.getElementById('verifyCurrentNumberBtn');

    if (updateNumberBtn) updateNumberBtn.addEventListener('click', showPhoneUpdateModal);
    if (addPhoneBtn) addPhoneBtn.addEventListener('click', showPhoneUpdateModal);
    if (verifyCurrentNumberBtn) verifyCurrentNumberBtn.addEventListener('click', showPhoneVerificationModal);

    // Password Change
    const changePasswordBtn = document.getElementById('changePasswordBtn');
    if (changePasswordBtn) changePasswordBtn.addEventListener('click', showPasswordChangeModal);

    // Delete Account
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    if (deleteAccountBtn) deleteAccountBtn.addEventListener('click', showDeleteAccountModal);

    // Subscription buttons
    const subscribeNowBtn = document.getElementById('subscribeNowBtn');
    const manageSubscriptionBtn = document.getElementById('manageSubscriptionBtn');
    
    if (subscribeNowBtn) subscribeNowBtn.addEventListener('click', showSubscriptionPlans);
    if (manageSubscriptionBtn) manageSubscriptionBtn.addEventListener('click', showManageSubscriptionModal);

}

// Export functions to global scope
window.closeModal = closeModal;
window.showModal = showModal;
window.showPhoneUpdateModal = showPhoneUpdateModal;
window.showPhoneVerificationModal = showPhoneVerificationModal;
window.showPasswordChangeModal = showPasswordChangeModal;
window.showDeleteAccountModal = showDeleteAccountModal;
window.showSubscriptionPlans = showSubscriptionPlans;
window.showManageSubscriptionModal = showManageSubscriptionModal;
