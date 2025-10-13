# Cookie System Implementation Guide

## Overview

This Laravel application includes a comprehensive cookie consent management system that complies with GDPR and other privacy regulations. The system provides users with granular control over different types of cookies while ensuring essential functionality remains intact.

## Components

### 1. CookieController (`app/Http/Controllers/CookieController.php`)
Handles all cookie-related HTTP requests:
- `status()` - Returns current consent status
- `setConsent()` - Updates user cookie preferences
- `acceptAll()` - Accepts all cookie types
- `rejectAll()` - Rejects non-essential cookies
- `deleteAll()` - Removes all user cookies
- `policy()` - Displays cookie policy page
- `settings()` - Displays cookie settings page
- `stats()` - Admin endpoint for cookie statistics

### 2. CookieConsent Model (`app/Models/CookieConsent.php`)
Database model for storing consent records:
- Tracks IP address, user agent, and consent data
- Provides statistics methods
- Includes scopes for filtering records

### 3. CookieConsentMiddleware (`app/Http/Middleware/CookieConsentMiddleware.php`)
Middleware that:
- Checks for existing consent on each request
- Logs consent data for compliance
- Manages non-essential cookies based on user preferences
- Shares cookie manager with views

### 4. CookieManager Service (`app/Services/CookieManager.php`)
Core service for cookie management:
- Defines cookie categories (preference, analytics, consent)
- Handles consent setting and retrieval
- Provides methods for setting different types of cookies
- Manages cookie deletion based on preferences

## Cookie Categories

### 1. Preference Cookies (Required)
- **Purpose**: Essential website functionality
- **Always enabled**: Cannot be disabled
- **Examples**: Session management, security tokens

### 2. Analytics Cookies (Optional)
- **Purpose**: Website usage analytics and performance monitoring
- **User choice**: Can be enabled/disabled
- **Examples**: Google Analytics, performance tracking

### 3. Consent Cookies (Required)
- **Purpose**: Store user's cookie preferences
- **Always enabled**: Cannot be disabled
- **Examples**: Cookie consent settings

## Frontend Components

### Cookie Consent Banner (`resources/views/cookie-consent-banner.blade.php`)
- Displays when user hasn't given consent
- Uses Alpine.js for interactivity
- Allows granular cookie selection
- Includes quick action buttons (Accept All, Reject, Settings)
- **GPS Location Collection**: Automatically requests user's GPS coordinates and location data when banner loads
- Uses browser Geolocation API with fallback handling
- Performs reverse geocoding to get country, city, and region information
- Sends location data along with consent preferences to server

### Cookie Settings Page (`resources/views/cookies/settings.blade.php`)
- Detailed cookie preference management
- Visual status indicators
- Individual toggle controls
- JavaScript-powered updates

### Cookie Policy Page (`resources/views/cookies/policy.blade.php`)
- Comprehensive cookie information
- Category descriptions and purposes
- Links to external resources
- Contact information

## Routes

```php
// Public cookie routes
Route::prefix('cookies')->name('cookies.')->group(function () {
    Route::get('/status', [CookieController::class, 'status'])->name('status');
    Route::post('/consent', [CookieController::class, 'setConsent'])->name('consent');
    Route::post('/accept-all', [CookieController::class, 'acceptAll'])->name('accept-all');
    Route::post('/reject-all', [CookieController::class, 'rejectAll'])->name('reject-all');
    Route::post('/delete', [CookieController::class, 'deleteAll'])->name('delete');
    Route::get('/policy', [CookieController::class, 'policy'])->name('policy');
    Route::get('/settings', [CookieController::class, 'settings'])->name('settings');

    // Admin routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/stats', [CookieController::class, 'stats'])->name('stats');
    });
});
```

## Usage Examples

### Setting Cookies in Code

```php
use App\Services\CookieManager;

$cookieManager = app(CookieManager::class);

// Set a preference cookie (always allowed)
$cookieManager->setPreference('theme', 'dark', 525600); // 1 year

// Set an analytics cookie (only if allowed)
if ($cookieManager->isAllowed(CookieManager::TYPE_ANALYTICS)) {
    $cookieManager->setAnalytics('page_view', 'home', 525600);
}

// Check if analytics are allowed
$analyticsAllowed = $cookieManager->isAllowed(CookieManager::TYPE_ANALYTICS);
```

### Checking Consent Status

```php
$cookieManager = app(CookieManager::class);

// Check if user has given consent
$hasConsent = $cookieManager->hasConsent();

// Get current consent settings
$consent = $cookieManager->getConsent();

// Check specific cookie type
$analyticsAllowed = $cookieManager->isAllowed(CookieManager::TYPE_ANALYTICS);
```

### Admin Statistics

```php
// Get consent statistics (admin only)
$stats = CookieConsent::getConsentStats();

// Returns:
// [
//     'total_consents' => 150,
//     'recent_consents' => 45,
//     'unique_ips' => 120,
//     'consent_types' => [
//         'preference' => 150,
//         'analytics' => 98,
//         'consent' => 150
//     ]
// ]
```

## Database Schema

The `cookie_consents` table stores:
- `ip_address` - User's IP address
- `user_agent` - Browser user agent string
- `latitude` - User's latitude coordinate (decimal, 10,8 precision)
- `longitude` - User's longitude coordinate (decimal, 11,8 precision)
- `country` - User's country name
- `city` - User's city name
- `region` - User's region/state name
- `consent_data` - JSON object of consent preferences
- `consent_hash` - MD5 hash for tracking changes
- `consented_at` - Timestamp of consent

## JavaScript Integration

The system uses Alpine.js for frontend interactivity:

```javascript
// In cookie banner
function cookieConsentBanner() {
    return {
        showBanner: true,
        selectedCookies: {
            preference: true,
            analytics: false,
            consent: true
        },

        acceptAll() {
            // Set all cookies to true
            this.submitConsent();
        },

        submitConsent() {
            // Send consent to server
            fetch('/cookies/consent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(this.selectedCookies)
            });
        }
    }
}
```

## Security Considerations

1. **CSRF Protection**: All POST requests include CSRF tokens
2. **Input Validation**: Consent data is validated server-side
3. **IP/User Agent Logging**: For compliance and audit trails
4. **Essential Cookies Only**: Non-essential cookies are deleted when not allowed
5. **Secure Cookies**: All cookies use secure flags in production

## Testing

### Manual Testing Checklist

1. **First Visit**: Cookie banner should appear
2. **Accept All**: All cookies should be set, banner hidden
3. **Reject All**: Only essential cookies remain
4. **Custom Settings**: Individual preferences should be saved
5. **Settings Page**: Should reflect current consent status
6. **Policy Page**: Should display correctly
7. **Admin Stats**: Should show accurate statistics

### Automated Testing

```php
// Feature test example
public function test_cookie_consent_can_be_set()
{
    $response = $this->post('/cookies/consent', [
        'preference' => true,
        'analytics' => false,
        'consent' => true
    ]);

    $response->assertStatus(200)
             ->assertJson(['success' => true]);
}
```

## Compliance

This implementation helps comply with:
- **GDPR** (Article 7 - Conditions for consent)
- **CCPA** (California Consumer Privacy Act)
- **ePrivacy Directive** (Cookie regulations)

## Maintenance

### Regular Tasks

1. **Review Consent Logs**: Periodically audit consent records
2. **Update Cookie List**: Add new cookies as features are added
3. **Monitor Analytics**: Ensure analytics cookies are properly managed
4. **Update Policy**: Keep cookie policy current with changes

### Adding New Cookie Types

1. Add constant to `CookieManager`
2. Update `CATEGORIES` array
3. Update `DEFAULT_SETTINGS`
4. Modify frontend components
5. Update policy page

## Troubleshooting

### Common Issues

1. **Banner not showing**: Check if user has existing consent cookie
2. **Cookies not setting**: Verify CSRF token and validation
3. **Analytics not working**: Check if analytics cookies are allowed
4. **Middleware errors**: Ensure middleware is properly registered

### Debug Commands

```bash
# Check migration status
php artisan migrate:status

# Clear cookies in browser dev tools
# Application > Cookies > Delete all

# Check consent in tinker
php artisan tinker
>>> App\Services\CookieManager::hasConsent()
```

## GPS Location Data Collection

The cookie system now includes GPS location data collection for enhanced analytics and compliance tracking:

### Features
- **Automatic Location Detection**: Uses browser Geolocation API to get user's coordinates
- **Reverse Geocoding**: Converts coordinates to readable location data (country, city, region)
- **Fallback Handling**: Gracefully handles location access denial or unavailability
- **Privacy Compliant**: Location data is collected only during consent process
- **Database Storage**: GPS data is stored alongside consent records for audit trails

### Technical Implementation
- **Frontend**: Alpine.js component requests location permission and performs geocoding
- **Backend**: Laravel validation ensures proper data format and storage
- **Database**: Decimal precision fields for accurate coordinate storage
- **API**: Uses free geocoding service (BigDataCloud) for location resolution

### Privacy Considerations
- Location data is collected only when user interacts with cookie consent banner
- Users can deny location access without affecting cookie consent functionality
- Data is stored for compliance and analytics purposes only
- No location tracking occurs after initial consent

## Future Enhancements

1. **Cookie Scanning**: Automatically detect cookies set by third parties
2. **Consent Withdrawal**: Allow users to withdraw consent
3. **Cookie Inventory**: Detailed list of all cookies used
4. **Multi-language**: Support for multiple languages
5. **Integration APIs**: Connect with consent management platforms
6. **Advanced Location Analytics**: Geographic consent pattern analysis