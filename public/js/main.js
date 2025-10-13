// Main entry point for the application
document.addEventListener('DOMContentLoaded', function() {
    console.log('Application initialized');
    
    // Import and initialize all components
    if (typeof initializeBackButton === 'function') initializeBackButton();
    if (typeof initializePhoneInput === 'function') initializePhoneInput();
    if (typeof initializeModalPhoneInput === 'function') initializeModalPhoneInput();
    
    // Any other global initializations can go here
});
