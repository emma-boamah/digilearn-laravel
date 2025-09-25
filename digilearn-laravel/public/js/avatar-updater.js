/**
 * Avatar Updater - Ensures avatar updates reflect across the entire application
 */
class AvatarUpdater {
    constructor() {
        this.init();
    }

    init() {
        // Listen for avatar update events
        document.addEventListener('avatar-updated', (event) => {
            this.updateAllAvatars(event.detail.avatarUrl, event.detail.userName);
        });

        // Listen for profile form submissions
        this.attachProfileFormListener();
    }

    attachProfileFormListener() {
        const profileForm = document.querySelector('#profileForm') || document.querySelector('#profile-form');
        if (profileForm) {
            profileForm.addEventListener('submit', (e) => {
                // After successful form submission, check for new avatar
                setTimeout(() => {
                    this.refreshAvatarsFromServer();
                }, 1000);
            });
        }
    }

    updateAllAvatars(newAvatarUrl, userName) {
        // Update all avatar images on the page
        const avatarSelectors = [
            '.user-avatar img',
            '[data-user-avatar]',
            '.avatar-img',
            '.profile-avatar',
            '.header-avatar img'
        ];

        avatarSelectors.forEach(selector => {
            const avatars = document.querySelectorAll(selector);
            avatars.forEach(avatar => {
                if (avatar.tagName === 'IMG') {
                    avatar.src = newAvatarUrl;
                    avatar.alt = userName;
                }
            });
        });

        // Update text-based avatars (initials)
        const textAvatars = document.querySelectorAll('.user-avatar:not(:has(img))');
        textAvatars.forEach(avatar => {
            if (newAvatarUrl) {
                // Replace text avatar with image
                avatar.innerHTML = `<img src="${newAvatarUrl}" alt="${userName}" class="w-full h-full rounded-full object-cover" />`;
            }
        });

        // Trigger custom event for other components
        document.dispatchEvent(new CustomEvent('avatars-refreshed', {
            detail: { avatarUrl: newAvatarUrl, userName: userName }
        }));
    }

    async refreshAvatarsFromServer() {
        try {
            const response = await fetch('/api/user/avatar-info', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateAllAvatars(data.avatar_url, data.name);
            }
        } catch (error) {
            console.log('Could not refresh avatars:', error);
        }
    }

    // Method to manually trigger avatar update
    static triggerUpdate(avatarUrl, userName) {
        document.dispatchEvent(new CustomEvent('avatar-updated', {
            detail: { avatarUrl: avatarUrl, userName: userName }
        }));
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.avatarUpdater = new AvatarUpdater();
});

// Export for manual use
window.AvatarUpdater = AvatarUpdater;
