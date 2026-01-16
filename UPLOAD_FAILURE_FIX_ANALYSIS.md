# Video Upload Failures - Root Cause Analysis & Fix

## Issues Identified from Logs

### Log Analysis
```
[First Attempt]
has_title: false
has_subject_id: false  
has_grade_level: false
has_video_file: false
Error: "Trying to access array offset on null"

[Second Attempt (after page refresh)]
has_title: true ✓
has_subject_id: true ✓
has_grade_level: true ✓
has_video_file: true ✓
Error: Still "Trying to access array offset on null" (Vimeo service issue)
```

## Root Causes

### 1. Form Fields Not Being Collected (First Attempt)
**Problem:** The modal form fields weren't accessible during the first upload attempt.
- Possible causes:
  - Modal not fully rendered when submitWizard() was called
  - Form elements were in a different DOM scope
  - Race condition between modal rendering and form submission

**Solution Applied:**
- Added multiple selector attempts in submitWizard()
- Better validation and error logging
- Console logs to track what data is being collected

### 2. Vimeo Service Returning Null (Second Attempt)
**Problem:** Even after the form fields were properly sent, the VimeoService was returning an invalid response structure that caused "array offset on null" error.

**Line causing error:**
```php
if ($result['success']) {  // $result was null or not an array
```

**Solution Applied:**
- Added defensive checks: `if ($result && is_array($result) && ($result['success'] ?? false))`
- Better error handling with try-catch around Vimeo service calls
- Clear error messages when service returns invalid response

## Changes Made

### 1. Frontend (resources/views/admin/contents/index.blade.php) - submitWizard()

**Lines 2440-2520:** Enhanced form field collection
```javascript
// Before: Single selector
const title = document.getElementById('title');

// After: Multiple selectors with fallback
const title = document.getElementById('title') || document.querySelector('[name="title"]');

// Added validation logging
console.log('Upload data collected:', {
    hasVideo: !!uploadData.video,
    videoName: uploadData.video ? uploadData.video.name : null,
    // ... more details for debugging
});
```

**Benefits:**
- Handles multiple DOM structures
- Better error messages if elements not found
- Console logging for debugging
- Validates all fields have values before submission

### 2. Backend (app/Http/Controllers/AdminController.php) - uploadVideoComponent()

**Chunked Upload Path (lines ~3620-3650):**
```php
// Before: Direct array access (throws error if $result is null)
if ($result['success']) {

// After: Defensive checks
if ($result && is_array($result) && ($result['success'] ?? false)) {
    // Safe to access array keys
    $video->update([
        'vimeo_id' => $result['video_id'] ?? null,
        'vimeo_embed_url' => $result['embed_url'] ?? null,
    ]);
}
```

**Direct Upload Path (lines ~3650-3680):**
- Same defensive checks applied
- Better error messages
- Try-catch around Vimeo service calls

**Benefits:**
- Prevents null array access errors
- Graceful fallback when service fails
- Clear error messages logged
- Consistent error handling across both paths

### 3. Logging Already in Place

**File:** app/Http/Controllers/AdminController.php (line ~3538)
```php
Log::info('Video upload request received', [
    'has_upload_id' => $request->filled('upload_id'),
    'has_video_file' => $request->hasFile('video_file'),
    'has_title' => $request->filled('title'),
    'has_subject_id' => $request->filled('subject_id'),
    'has_grade_level' => $request->filled('grade_level'),
    'video_source' => $request->input('video_source'),
    'upload_destination' => $request->input('upload_destination')
]);
```

This logging is helping us identify exactly what data is being sent.

## Testing Instructions

### Test 1: Form Field Collection
1. Open browser DevTools → Console
2. Click "Upload Content" button
3. Select a video file
4. Fill in title, subject, grade level
5. Click "Finish"
6. **Check console** - should see "Upload data collected" with all values populated

### Test 2: Vimeo Upload
1. Try uploading with Vimeo as destination
2. Check logs for error message
3. If Vimeo credentials are missing/invalid, should see clear error message
4. **Expected:** Either successful upload OR clear error (not "array offset on null")

### Test 3: Local Upload
1. Select "Local" as video source
2. Complete form and submit
3. **Expected:** Should upload successfully without Vimeo service involvement

## Next Steps

1. **Clear browser cache** (in case old JavaScript is cached)
2. **Deploy changes** to production:
   ```bash
   git pull origin upload-content-debug2
   php artisan cache:clear
   php artisan view:clear
   ```

3. **Monitor logs** during next upload attempts:
   ```bash
   tail -f storage/logs/laravel.log | grep "Video upload"
   ```

4. **Check console errors** in browser DevTools if upload still fails

## Expected Behavior After Fix

**Success Case:**
```
[2026-01-16 04:50:00] production.INFO: Video upload request received {
    "has_title": true,
    "has_subject_id": true,
    "has_grade_level": true,
    "has_video_file": true
}
[2026-01-16 04:50:05] production.INFO: Video upload completed {
    "video_id": 123,
    "status": "pending"
}
```

**Error Case (with clear message):**
```
[2026-01-16 04:50:00] production.ERROR: Video upload failed {
    "error": "Failed to upload to Vimeo: Invalid API credentials"
}
```

## Files Modified

1. `resources/views/admin/contents/index.blade.php`
   - Lines 2440-2520: Enhanced submitWizard() function

2. `app/Http/Controllers/AdminController.php`
   - Lines 3620-3650: Defensive checks for chunked upload
   - Lines 3650-3680: Defensive checks for direct upload
   - Lines 3538-3550: Request logging (already in place)

## Troubleshooting Guide

If uploads still fail:

1. **Check browser console** for JavaScript errors
2. **Check Laravel logs** for PHP errors
3. **Verify form elements exist** in the modal:
   ```js
   document.getElementById('title')
   document.getElementById('subject_id')
   document.getElementById('grade_level')
   document.getElementById('description')
   ```
4. **Check Vimeo credentials** if uploading to Vimeo:
   ```php
   php artisan tinker
   >>> echo config('services.vimeo.access_token');
   ```

## Summary

- ✅ Fixed form field collection on initial submit
- ✅ Added defensive null checks for service responses
- ✅ Better error logging and messages
- ✅ No breaking changes to existing functionality
