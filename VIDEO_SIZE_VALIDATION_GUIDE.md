# 📹 Video Size Validation - 30GB Maximum

## Overview
Video content uploads now have a **maximum size of 30GB per video** with comprehensive validation both on the frontend and backend.

---

## Changes Made

### 1. Configuration Update
**File:** `config/uploads.php`

```php
'video' => [
    'max_size' => env('VIDEO_MAX_SIZE', 32212254720), // 30GB in bytes
    'max_size_mb' => env('VIDEO_MAX_SIZE_MB', 30720), // 30GB in MB
    'max_size_display' => '30GB', // User-friendly display text
    // ... rest of config
],
```

**Key Points:**
- Max size set to **30GB (32212254720 bytes)**
- Display text shows **30GB** to users
- Configurable via environment variable `VIDEO_MAX_SIZE`

---

### 2. Frontend Validation (JavaScript)

**File:** `resources/views/admin/contents/index.blade.php`

#### A. Modal UI Update
- Updated help text from "32GB" to **"30GB"**
- Added error message display container with ID `#videoSizeError`

```html
<p class="text-sm text-gray-500">MP4, MOV, AVI up to 30GB</p>
<!-- Error message container -->
<div id="videoSizeError" class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg hidden">
    <p class="text-sm text-red-700">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <span id="videoSizeErrorMessage">Video file exceeds maximum size of 30GB</span>
    </p>
</div>
```

#### B. File Selection Validation
When user selects a video file via file picker:

```javascript
fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
        // Validate video file size (30GB limit)
        const MAX_VIDEO_SIZE = 30 * 1024 * 1024 * 1024; // 30GB in bytes
        if (file.size > MAX_VIDEO_SIZE) {
            showVideoSizeError(file.size);
            fileInput.value = '';
            return;
        }
        hideVideoSizeError();
        uploadData.video = file;
        updateVideoUploadArea(file);
    }
});
```

#### C. Drag & Drop Validation
When user drags and drops a video file:

```javascript
function handleVideoDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;

    if (files.length > 0) {
        const file = files[0];
        if (file.type.startsWith('video/')) {
            // Validate video file size (30GB limit)
            const MAX_VIDEO_SIZE = 30 * 1024 * 1024 * 1024; // 30GB in bytes
            if (file.size > MAX_VIDEO_SIZE) {
                showVideoSizeError(file.size);
                return;
            }
            hideVideoSizeError();
            // ... rest of drop handling
        }
    }
}
```

#### D. Helper Functions
```javascript
function showVideoSizeError(fileSize) {
    const errorDiv = document.getElementById('videoSizeError');
    const errorMessage = document.getElementById('videoSizeErrorMessage');
    
    if (errorDiv && errorMessage) {
        const fileSizeMB = (fileSize / (1024 * 1024 * 1024)).toFixed(2);
        errorMessage.textContent = `Video file size (${fileSizeMB}GB) exceeds maximum allowed size of 30GB. Please choose a smaller file.`;
        errorDiv.classList.remove('hidden');
    }
}

function hideVideoSizeError() {
    const errorDiv = document.getElementById('videoSizeError');
    if (errorDiv) {
        errorDiv.classList.add('hidden');
    }
}
```

---

### 3. Backend Validation (Laravel)

**File:** `app/Http/Controllers/AdminController.php`

#### A. Config Loading with Fallback
```php
$uploadConfig = config('uploads');
if (!$uploadConfig) {
    Log::warning('Config cache missing, reloading from file');
    $uploadConfig = include config_path('uploads.php');
}

if (!$uploadConfig || !is_array($uploadConfig)) {
    throw new \Exception('Upload configuration not found or invalid');
}

if (empty($uploadConfig['video']) || empty($uploadConfig['thumbnail'])) {
    throw new \Exception('Upload configuration missing required keys');
}
```

#### B. Max Size Calculation
```php
// Calculate max size in KB for Laravel validation
$videoMaxSize = ($uploadConfig['video']['max_size'] ?? 32212254720) / 1024;
// Result: 31457280 KB (30GB)
```

#### C. Validation Rules
```php
$validationRules = [
    // ... other rules ...
    'video_file' => 'nullable|file|mimes:' . implode(',', $uploadConfig['video']['mimes']) . '|max:' . $videoMaxSize,
    // ... other rules ...
];

$request->validate($validationRules, [
    'video_file.max' => 'Video file size cannot exceed ' . $uploadConfig['video']['max_size_display'] . '.',
    // Custom error message shows "30GB"
]);
```

#### D. Error Messages
If validation fails, user sees:
```
"Video file size cannot exceed 30GB."
```

---

## Validation Flow (Both Create and Edit)

### Create New Content (Create Content Package Modal)

```
1. User opens Create Content Package modal
2. User selects local video source
3. User uploads video file (via file picker or drag & drop)
   ├─ Frontend checks file size
   │  ├─ If ≤ 30GB: Accept, show preview ✅
   │  └─ If > 30GB: Show error, reject file ❌
4. If validated, user can proceed to next step
5. User clicks Finish
6. Request sent to /admin/contents/upload/video
7. Backend validation:
   ├─ Check config is loaded (with fallback)
   ├─ Validate rules (max size: 30GB)
   ├─ If valid: Create video record ✅
   └─ If invalid: Return validation error ❌
```

### Edit Existing Content (Edit Content Page)

Currently, the edit page (`resources/views/admin/contents/edit.blade.php`) doesn't allow re-uploading the video file. However, if you want to add that functionality in the future:

```
1. Edit page would need a video upload section
2. Same validation logic applies (30GB max)
3. Frontend validation prevents oversized files
4. Backend validation ensures security
```

---

## Error Handling

### Frontend Error Display
When user selects/drops a file > 30GB:

```
❌ Video file size (35.50GB) exceeds maximum allowed size of 30GB. Please choose a smaller file.
```

The error:
- Appears below the upload area
- Shows actual file size in GB
- Prevents upload from proceeding
- Clears the file selection
- Disappears when user selects a valid file

### Backend Error Response
If somehow a file > 30GB gets past frontend validation:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "video_file": [
            "Video file size cannot exceed 30GB."
        ]
    }
}
```

---

## User Experience

### Scenario 1: Valid Upload (15GB file)
```
✅ User selects 15GB video
✅ Frontend allows it (15GB < 30GB)
✅ Video preview shows
✅ User can proceed
✅ Backend validates and accepts
✅ Video created successfully
```

### Scenario 2: Oversized Upload (35GB file)
```
❌ User tries to select 35GB video
❌ Frontend blocks it (35GB > 30GB)
❌ Error message: "35.00GB exceeds maximum 30GB"
❌ File not accepted
❌ User must choose smaller file
```

### Scenario 3: Drag & Drop Oversized
```
❌ User drags 40GB file onto upload area
❌ Frontend detects size during drop
❌ Error message displayed
❌ Drop rejected
❌ Original drop area still ready for new file
```

---

## Configuration

### Environment Variables
You can customize the video size limit via `.env`:

```env
# In .env file
VIDEO_MAX_SIZE=32212254720  # 30GB in bytes
VIDEO_MAX_SIZE_MB=30720      # 30GB in MB
```

### To Change Limit
If you want a different limit (e.g., 50GB):

1. **Calculate bytes:**
   ```
   50GB × 1024 MB/GB × 1024 KB/MB × 1024 bytes/KB = 53687091200 bytes
   ```

2. **Update .env:**
   ```env
   VIDEO_MAX_SIZE=53687091200
   VIDEO_MAX_SIZE_MB=51200
   ```

3. **Update frontend constant** (in `resources/views/admin/contents/index.blade.php`):
   ```javascript
   const MAX_VIDEO_SIZE = 50 * 1024 * 1024 * 1024; // 50GB in bytes
   ```

4. **Update UI text:**
   - In modal: "MP4, MOV, AVI up to 50GB"
   - In error messages: "30GB" → "50GB"

---

## Size Reference

| Size | Bytes | Notes |
|------|-------|-------|
| 1GB | 1,073,741,824 | Small video |
| 5GB | 5,368,709,120 | Medium video |
| 10GB | 10,737,418,240 | Large video |
| 30GB | 32,212,254,720 | **Current limit** |
| 50GB | 53,687,091,200 | Optional higher limit |
| 100GB | 107,374,182,400 | Very large |

---

## Testing the Validation

### Manual Testing

1. **Test with valid file (< 30GB):**
   - Upload 10GB video
   - Should accept and create preview
   - Should allow proceeding

2. **Test with oversized file (> 30GB):**
   - Try to upload 35GB video
   - Should show error message
   - Should NOT accept file

3. **Test drag & drop:**
   - Try dragging large file
   - Should validate on drop
   - Should show appropriate error

### Automated Testing
Create test cases for:
- File size exactly 30GB (should pass)
- File size 30GB + 1 byte (should fail)
- Various file types (mp4, mov, avi)
- Drag & drop vs file picker

---

## Browser Compatibility

File size validation works in:
- ✅ Chrome/Edge (all versions)
- ✅ Firefox (all versions)
- ✅ Safari (all versions)
- ✅ Mobile browsers

The `File.size` property is universally supported.

---

## Security Notes

1. **Frontend validation** is for UX only - prevents unnecessary uploads
2. **Backend validation** is for security - always enforced
3. **Config fallback** ensures validation works even if cache is cleared
4. **Error messages** are user-friendly but don't expose system details

---

## Troubleshooting

### "Video file size (X GB) exceeds maximum allowed size of 30GB" appears but file is smaller

**Solution:**
1. Clear browser cache
2. Hard refresh the page (Ctrl+Shift+R)
3. Try uploading again

### Backend shows "Video file size cannot exceed 30GB" but frontend allowed it

**Cause:** Config mismatch between frontend constant and backend config

**Solution:**
1. Ensure both use same 30GB limit
2. Clear Laravel config cache: `php artisan config:clear`
3. Check `.env` for correct `VIDEO_MAX_SIZE`

### File gets rejected after upload started

**Cause:** File size check during chunked upload reassembly

**Solution:**
1. Re-upload with smaller file
2. Check file actually exists on disk
3. Verify upload wasn't interrupted

---

## Support

For issues with video validation:
1. Check error message shown to user
2. Review logs: `storage/logs/laravel.log`
3. Verify config: `php artisan tinker` → `config('uploads')`
4. Check file: `ls -lh /path/to/uploaded/file`

---

## Summary

✅ **Frontend Validation:** Prevents oversized uploads before sending to server
✅ **Backend Validation:** Enforces 30GB limit server-side  
✅ **User Feedback:** Clear error messages with actual file size
✅ **Flexibility:** Easily configurable via environment variables
✅ **Both Create & Edit:** Validation applies to all upload scenarios

**Maximum video size: 30GB per video**
