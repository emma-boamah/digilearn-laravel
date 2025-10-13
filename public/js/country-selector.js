// Country Selector JavaScript
// Handles country selection dropdowns for phone number inputs

class CountrySelector {
    constructor(options = {}) {
        this.countries = options.countries || this.getDefaultCountries();
        this.currentCountry = options.currentCountry || 'gh'; // Default to Ghana
        this.onCountryChange = options.onCountryChange || null;
    }

    // Default list of countries with their codes and flags
    getDefaultCountries() {
        return [
            { name: 'Afghanistan', code: '+93', flag: 'af' },
            { name: 'Albania', code: '+355', flag: 'al' },
            { name: 'Algeria', code: '+213', flag: 'dz' },
            { name: 'Argentina', code: '+54', flag: 'ar' },
            { name: 'Australia', code: '+61', flag: 'au' },
            { name: 'Austria', code: '+43', flag: 'at' },
            { name: 'Bangladesh', code: '+880', flag: 'bd' },
            { name: 'Belgium', code: '+32', flag: 'be' },
            { name: 'Brazil', code: '+55', flag: 'br' },
            { name: 'Canada', code: '+1', flag: 'ca' },
            { name: 'China', code: '+86', flag: 'cn' },
            { name: 'Colombia', code: '+57', flag: 'co' },
            { name: 'Czech Republic', code: '+420', flag: 'cz' },
            { name: 'Denmark', code: '+45', flag: 'dk' },
            { name: 'Egypt', code: '+20', flag: 'eg' },
            { name: 'Finland', code: '+358', flag: 'fi' },
            { name: 'France', code: '+33', flag: 'fr' },
            { name: 'Germany', code: '+49', flag: 'de' },
            { name: 'Ghana', code: '+233', flag: 'gh' },
            { name: 'Greece', code: '+30', flag: 'gr' },
            { name: 'Hong Kong', code: '+852', flag: 'hk' },
            { name: 'Hungary', code: '+36', flag: 'hu' },
            { name: 'Iceland', code: '+354', flag: 'is' },
            { name: 'India', code: '+91', flag: 'in' },
            { name: 'Indonesia', code: '+62', flag: 'id' },
            { name: 'Ireland', code: '+353', flag: 'ie' },
            { name: 'Israel', code: '+972', flag: 'il' },
            { name: 'Italy', code: '+39', flag: 'it' },
            { name: 'Japan', code: '+81', flag: 'jp' },
            { name: 'Jordan', code: '+962', flag: 'jo' },
            { name: 'Kenya', code: '+254', flag: 'ke' },
            { name: 'South Korea', code: '+82', flag: 'kr' },
            { name: 'Kuwait', code: '+965', flag: 'kw' },
            { name: 'Lebanon', code: '+961', flag: 'lb' },
            { name: 'Malaysia', code: '+60', flag: 'my' },
            { name: 'Mexico', code: '+52', flag: 'mx' },
            { name: 'Morocco', code: '+212', flag: 'ma' },
            { name: 'Netherlands', code: '+31', flag: 'nl' },
            { name: 'New Zealand', code: '+64', flag: 'nz' },
            { name: 'Nigeria', code: '+234', flag: 'ng' },
            { name: 'Norway', code: '+47', flag: 'no' },
            { name: 'Pakistan', code: '+92', flag: 'pk' },
            { name: 'Peru', code: '+51', flag: 'pe' },
            { name: 'Philippines', code: '+63', flag: 'ph' },
            { name: 'Poland', code: '+48', flag: 'pl' },
            { name: 'Portugal', code: '+351', flag: 'pt' },
            { name: 'Romania', code: '+40', flag: 'ro' },
            { name: 'Russia', code: '+7', flag: 'ru' },
            { name: 'Saudi Arabia', code: '+966', flag: 'sa' },
            { name: 'Singapore', code: '+65', flag: 'sg' },
            { name: 'South Africa', code: '+27', flag: 'za' },
            { name: 'Spain', code: '+34', flag: 'es' },
            { name: 'Sweden', code: '+46', flag: 'se' },
            { name: 'Switzerland', code: '+41', flag: 'ch' },
            { name: 'Thailand', code: '+66', flag: 'th' },
            { name: 'Turkey', code: '+90', flag: 'tr' },
            { name: 'Ukraine', code: '+380', flag: 'ua' },
            { name: 'United Arab Emirates', code: '+971', flag: 'ae' },
            { name: 'United Kingdom', code: '+44', flag: 'gb' },
            { name: 'United States', code: '+1', flag: 'us' },
            { name: 'Vietnam', code: '+84', flag: 'vn' }
        ];
    }

    // Initialize country selector for a specific container
    init(containerId, options = {}) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const countryBtn = container.querySelector('.country-code-btn');
        const countryDropdown = container.querySelector('.country-code-dropdown');
        const countryList = container.querySelector('.country-list');
        const countrySearch = container.querySelector('.country-search-input');
        const selectedFlag = container.querySelector('.country-flag');
        const selectedCode = container.querySelector('.country-code');
        const countryCodeInput = container.querySelector('input[name="country_code"]');

        if (!countryBtn || !countryDropdown || !countryList) return;

        // Set initial country
        const initialCountry = this.countries.find(c => c.flag === (options.initialCountry || this.currentCountry));
        if (initialCountry) {
            this.setSelectedCountry(initialCountry, selectedFlag, selectedCode, countryCodeInput);
        }

        // Populate country list
        this.populateCountryList(countryList, this.countries);

        // Toggle dropdown
        countryBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            countryDropdown.classList.toggle('active');
            if (countryDropdown.classList.contains('active') && countrySearch) {
                countrySearch.focus();
            }
        });

        // Search functionality
        if (countrySearch) {
            countrySearch.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                const filtered = this.countries.filter(country =>
                    country.name.toLowerCase().includes(searchTerm) ||
                    country.code.includes(searchTerm)
                );
                this.populateCountryList(countryList, filtered);
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                countryDropdown.classList.remove('active');
            }
        });

        // Handle escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && countryDropdown.classList.contains('active')) {
                countryDropdown.classList.remove('active');
            }
        });
    }

    // Populate the country list with options
    populateCountryList(countryList, countries) {
        countryList.innerHTML = '';

        countries.forEach(country => {
            const option = document.createElement('div');
            option.className = 'country-option';
            option.innerHTML = `
                <img src="https://flagcdn.com/w20/${country.flag}.png" alt="${country.name}" class="country-flag">
                <span class="country-name">${country.name}</span>
                <span class="country-code">${country.code}</span>
            `;

            option.addEventListener('click', () => {
                this.selectCountry(country, countryList.closest('.country-code-dropdown'));
            });

            countryList.appendChild(option);
        });
    }

    // Select a country
    selectCountry(country, dropdown) {
        const container = dropdown.closest('.country-input-container, .phone-input-container');
        const selectedFlag = container.querySelector('.country-flag');
        const selectedCode = container.querySelector('.country-code');
        const countryCodeInput = container.querySelector('input[name="country_code"]');

        this.setSelectedCountry(country, selectedFlag, selectedCode, countryCodeInput);

        // Close dropdown
        dropdown.classList.remove('active');

        // Clear search
        const searchInput = dropdown.querySelector('.country-search-input');
        if (searchInput) {
            searchInput.value = '';
        }

        // Repopulate full list
        const countryList = dropdown.querySelector('.country-list');
        if (countryList) {
            this.populateCountryList(countryList, this.countries);
        }

        // Trigger change callback
        if (this.onCountryChange) {
            this.onCountryChange(country);
        }
    }

    // Set the selected country display
    setSelectedCountry(country, flagElement, codeElement, inputElement) {
        if (flagElement) {
            flagElement.src = `https://flagcdn.com/w20/${country.flag}.png`;
            flagElement.alt = country.name;
        }

        if (codeElement) {
            codeElement.textContent = country.code;
        }

        if (inputElement) {
            inputElement.value = country.code;
        }

        this.currentCountry = country.flag;
    }

    // Get country by code
    getCountryByCode(code) {
        return this.countries.find(country => country.code === code);
    }

    // Get country by flag code
    getCountryByFlag(flag) {
        return this.countries.find(country => country.flag === flag);
    }
}

// Global country selector instance
let countrySelector;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize main country selector
    countrySelector = new CountrySelector();

    // Initialize main phone input country selector
    const mainContainer = document.querySelector('.phone-input-container, .country-input-container');
    if (mainContainer) {
        countrySelector.init('countryCodeBtn', {
            initialCountry: 'gh'
        });
    }

    // Initialize modal country selector if it exists
    const modalContainer = document.getElementById('modalCountryCodeBtn');
    if (modalContainer) {
        countrySelector.init('modalCountryCodeBtn', {
            initialCountry: 'gh'
        });
    }

    // Update flag function for backward compatibility
    window.updateFlag = function(select) {
        const selectedOption = select.options[select.selectedIndex];
        const countryCode = selectedOption.getAttribute('data-code');
        if (countryCode) {
            const flagDisplay = select.parentElement.querySelector('.flag-display img');
            if (flagDisplay) {
                flagDisplay.src = `https://flagcdn.com/w20/${countryCode.toLowerCase()}.png`;
                flagDisplay.alt = selectedOption.text;
            }
        }
    };
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CountrySelector;
}