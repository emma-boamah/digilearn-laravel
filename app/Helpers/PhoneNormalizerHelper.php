<?php

namespace App\Helpers;

/**
 * Normalizes phone numbers to a consistent international format.
 *
 * Handles any input format a user might type:
 *  - Local with leading zero: 0508260294
 *  - Local without zero: 508260294
 *  - Full international: 233508260294
 *  - With plus prefix: +233508260294
 *  - With spaces/dashes/parentheses: (050) 826-0294
 *
 * All normalize to: +233508260294
 */
class PhoneNormalizerHelper
{
    /**
     * Normalize a phone number by combining it with the selected country code.
     *
     * @param string|null $phone       The raw phone number input from the user
     * @param string|null $countryCode The selected country code (e.g., '+233')
     * @return string|null             The normalized phone number (e.g., '+233508260294') or null
     */
    public static function normalize(?string $phone, ?string $countryCode = '+233'): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Strip everything except digits
        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (empty($digits)) {
            return null;
        }

        // Get the country code digits (e.g., '+233' → '233')
        $codeDigits = preg_replace('/[^0-9]/', '', $countryCode ?? '+233');

        // Strip the country code digits from the front if the user typed them
        // e.g., user typed '233508260294' with +233 selected → '508260294'
        if (!empty($codeDigits) && str_starts_with($digits, $codeDigits)) {
            $stripped = substr($digits, strlen($codeDigits));
            // Only strip if what remains looks like a valid local number (at least 7 digits)
            // This prevents stripping '233' from a number like '2334567' which is only 4 digits after
            if (strlen($stripped) >= 7) {
                $digits = $stripped;
            }
        }

        // Strip leading zero (local format: 0508260294 → 508260294)
        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        // Validate reasonable length: local numbers are typically 7-12 digits
        if (strlen($digits) < 7 || strlen($digits) > 12) {
            // Return what we can — let validation rules catch truly invalid numbers
            // But still format it properly so it doesn't get double-prefixed
            return $countryCode . $digits;
        }

        return $countryCode . $digits;
    }

    /**
     * Infer the country code from an existing stored phone number.
     *
     * Used by the data cleanup command to normalize already-stored numbers
     * without knowing what country code was originally selected.
     *
     * @param string $phone The stored phone number (e.g., '+233233559500321')
     * @return array{country_code: string, local: string}
     */
    public static function inferAndNormalize(string $phone): array
    {
        // Strip everything except digits and leading +
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        // Known country codes ordered longest-first to avoid partial matches
        $knownCodes = [
            '+233' => '233', // Ghana
            '+234' => '234', // Nigeria
            '+254' => '254', // Kenya
            '+27'  => '27',  // South Africa
            '+1'   => '1',   // US/Canada
            '+44'  => '44',  // UK
            '+61'  => '61',  // Australia
            '+49'  => '49',  // Germany
            '+33'  => '33',  // France
            '+91'  => '91',  // India
            '+86'  => '86',  // China
            '+81'  => '81',  // Japan
            '+55'  => '55',  // Brazil
            '+52'  => '52',  // Mexico
        ];

        // Try to detect the country code from the stored number
        foreach ($knownCodes as $prefix => $codeDigits) {
            if (str_starts_with($cleaned, $prefix)) {
                // Extract digits after the country code prefix
                $afterPrefix = substr($cleaned, strlen($prefix));
                $digits = preg_replace('/[^0-9]/', '', $afterPrefix);

                // Re-normalize through the standard path
                $normalized = self::normalize($digits, $prefix);

                return [
                    'country_code' => $prefix,
                    'local' => $digits,
                    'normalized' => $normalized,
                ];
            }
        }

        // Fallback: return as-is if we can't detect the country code
        return [
            'country_code' => null,
            'local' => preg_replace('/[^0-9]/', '', $cleaned),
            'normalized' => $cleaned,
        ];
    }
}
