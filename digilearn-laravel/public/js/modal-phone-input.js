// Modal phone input functionality
document.addEventListener('DOMContentLoaded', function() {
    const modalPhoneInput = document.getElementById('new_phone');
    const modalCountryCodeBtn = document.getElementById('modalCountryCodeBtn');
    const modalCountryCodeDropdown = document.getElementById('modalCountryCodeDropdown');
    const modalCountryList = document.getElementById('modalCountryList');
    const modalCountrySearch = document.getElementById('modalCountrySearch');
    
    if (!modalPhoneInput || !modalCountryCodeBtn || !modalCountryCodeDropdown || !modalCountryList) return;

    // Toggle modal dropdown
    modalCountryCodeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        modalCountryCodeDropdown.classList.toggle('active');
        if (modalCountryCodeDropdown.classList.contains('active') && modalCountrySearch) {
            modalCountrySearch.focus();
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!modalCountryCodeBtn.contains(e.target) && !modalCountryCodeDropdown.contains(e.target)) {
            modalCountryCodeDropdown.classList.remove('active');
        }
    });

    // Filter countries in modal
    if (modalCountrySearch) {
        modalCountrySearch.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const items = modalCountryList.getElementsByClassName('dropdown-item');
            
            Array.from(items).forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? 'flex' : 'none';
            });
        });
    }
});
