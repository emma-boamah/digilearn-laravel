// Avatar preview functionality
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        const preview = document.getElementById('avatarPreview');
        const avatarImage = document.getElementById('avatarImage');
        
        reader.onload = function(e) {
            if (!avatarImage) {
                // If no image element exists, create one
                const img = document.createElement('img');
                img.id = 'avatarImage';
                img.className = 'avatar-image';
                img.src = e.target.result;
                preview.innerHTML = '';
                preview.appendChild(img);
            } else {
                // Update existing image
                avatarImage.src = e.target.result;
            }
            
            // Show a success message when a new avatar is selected
            if (window.showNotification) {
                showNotification('Click "Update" to save your profile picture', 'info');
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Country flag update
function updateFlag(select) {
    const flagDisplay = select.parentElement.querySelector('.flag-display img');
    const selectedOption = select.options[select.selectedIndex];
    const countryCode = selectedOption.getAttribute('data-code');
    if (countryCode && flagDisplay) {
        flagDisplay.src = `https://flagcdn.com/w20/${countryCode.toLowerCase()}.png`;
    }
}

// Make functions available globally
window.previewAvatar = previewAvatar;
window.updateFlag = updateFlag;
