# Payment Amount Mismatch Fix

## Problem
The payment validation was failing with a type mismatch error:
```
Payment amount mismatch {"expected_amount":"55.00","paid_amount":55}
```

## Root Cause
In `PaymentController::handleSuccessfulPayment()`, the comparison was using strict inequality:
```php
if ($paidAmount !== $payment->amount) {
    // Mark as failed
}
```

**The issue:**
- `$paidAmount` = `55` (float, from kobo conversion: 5500 / 100)
- `$payment->amount` = `"55.00"` (string, from database decimal:2 cast)

The strict `!==` operator fails because:
- Type 1: float vs string
- Value 1: 55.0 vs "55.00" (loose comparison would work, but strict doesn't)

## Solution Applied

### 1. Cast Both Values to Float
```php
$expectedAmount = (float) $payment->amount;  // "55.00" → 55.0
$paidAmountFloat = (float) $paidAmount;      // 55 → 55.0
```

### 2. Use Tolerance-Based Comparison
Added a new helper method `amountsMatch()` that:
- Compares floats with a tolerance of 0.01 (1 pesewa)
- Handles floating-point precision errors
- Allows for legitimate payment gateway rounding

```php
private function amountsMatch(float $expected, float $paid): bool
{
    $tolerance = 0.01; // 1 pesewa tolerance
    return abs($expected - $paid) < $tolerance;
}
```

### 3. Updated Validation Logic
```php
if (!$this->amountsMatch($expectedAmount, $paidAmountFloat)) {
    // Mark as failed
}
```

## Changes Made

**File:** `app/Http/Controllers/PaymentController.php`

### Line 228-235: Type Casting
```php
// Cast both to float for comparison with tolerance for floating point errors
$expectedAmount = (float) $payment->amount;
$paidAmountFloat = (float) $paidAmount;

if (!$this->amountsMatch($expectedAmount, $paidAmountFloat)) {
```

### Line 322-327: Helper Method Added
```php
/**
 * Compare two amounts with tolerance for floating point precision
 * Allows for up to 0.01 difference (1 pesewa)
 */
private function amountsMatch(float $expected, float $paid): bool
{
    $tolerance = 0.01; // 1 pesewa tolerance
    return abs($expected - $paid) < $tolerance;
}
```

## Testing
The fix now properly handles:
- ✅ String amounts from database
- ✅ Float amounts from calculations
- ✅ Floating-point precision errors
- ✅ Small rounding differences (up to 1 pesewa)

## Deployment
Run on production to apply the fix:
```bash
cd /var/www/digilearn-laravel
git pull origin upload-content-debug2
# Or manually apply the changes shown above
```

No database migration needed. This is a logic fix only.

## Related Issues Resolved
- Payment validation now accepts properly converted amounts
- Better error logging with consistent float values
- Prevents legitimate payments from being marked as failed
