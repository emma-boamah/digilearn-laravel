// Utility functions for the application

// Back button functionality
function initializeBackButton() {
    const backButton = document.getElementById('backButton');
    if (backButton) {
        backButton.addEventListener('click', function() {
            window.history.back();
        });
    }
}

// Initialize all utility functions
document.addEventListener('DOMContentLoaded', function() {
    initializeBackButton();
});
