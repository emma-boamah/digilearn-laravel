# 🎬 Video Size Validation - Quick Reference

## Maximum Video Size
**30GB per video** ← What user requested

## Where It's Enforced

### ✅ Frontend (Instant Feedback)
- **File Picker:** When user selects video
- **Drag & Drop:** When user drops video
- **Location:** Create Content Package modal

### ✅ Backend (Server Security)
- **Upload Endpoint:** `/admin/contents/upload/video`
- **Controller:** `AdminController@uploadVideoComponent`
- **Validation:** Laravel `max` rule

---

## Error Message

### User Sees (If File > 30GB)
```
❌ Video file size (35.50GB) exceeds maximum allowed 
   size of 30GB. Please choose a smaller file.
```

---

## Implementation Details

### Files Changed
1. ✅ `config/uploads.php` - Set max_size to 30GB
2. ✅ `resources/views/admin/contents/index.blade.php` - Frontend validation
3. ✅ `app/Http/Controllers/AdminController.php` - Backend validation

### Key Constants

**In Config:**
```
30GB = 32,212,254,720 bytes
30GB = 30,720 MB
```

**In JavaScript:**
```javascript
const MAX_VIDEO_SIZE = 30 * 1024 * 1024 * 1024; // bytes
```

**In Laravel:**
```php
'max_size' => 32212254720, // bytes
```

---

## User Experience

### Scenario 1: Valid File (15GB) ✅
```
User: Select 15GB video
System: ✅ Accept (15GB < 30GB)
Result: Video preview shows, can proceed
```

### Scenario 2: Oversized File (35GB) ❌
```
User: Select 35GB video
System: ❌ Reject (35GB > 30GB)
Result: Error shown, file not accepted
```

### Scenario 3: Drag & Drop (40GB) ❌
```
User: Drag 40GB onto upload area
System: ❌ Reject on drop
Result: Error shown, drop area still ready
```

---

## How to Change the Limit

### Currently: 30GB
### To Change to 50GB:

**Step 1:** Calculate bytes
```
50GB × 1024 × 1024 × 1024 = 53,687,091,200 bytes
```

**Step 2:** Update `.env`
```env
VIDEO_MAX_SIZE=53687091200
VIDEO_MAX_SIZE_MB=51200
```

**Step 3:** Update JavaScript (in index.blade.php)
```javascript
const MAX_VIDEO_SIZE = 50 * 1024 * 1024 * 1024;
```

**Step 4:** Update UI text
- Change "30GB" to "50GB" in modal help text

**Step 5:** Clear cache
```bash
php artisan config:clear
```

---

## Testing Checklist

- [ ] Test uploading 10GB file → Should ✅ Accept
- [ ] Test uploading 30GB file → Should ✅ Accept (exactly at limit)
- [ ] Test uploading 35GB file → Should ❌ Reject
- [ ] Test drag & drop 40GB → Should ❌ Reject
- [ ] Test file picker 25GB → Should ✅ Accept
- [ ] Verify error message displays actual file size
- [ ] Verify file is removed from input after error
- [ ] Verify error clears when valid file selected

---

## Validation Flow

```
Frontend                 Backend
─────────               ──────────
User uploads ──→ Check size
file            < 30GB?
                  │
             Yes ↓      No ↓
                ✅        ❌
             Show        Show
             preview     error
                │
             Click ──→ Validate
             Finish    again
                       < 30GB?
                          │
                      Yes ↓   No ↓
                         ✅    ❌
                      Create  Return
                      video   error
```

---

## Common Issues

| Issue | Solution |
|-------|----------|
| Error still shows but file is smaller | Hard refresh (Ctrl+Shift+R) |
| Backend validation fails after frontend passed | Clear config cache: `php artisan config:clear` |
| Want different size limit | Follow "How to Change the Limit" above |
| File rejected but seems smaller than 30GB | Check actual file size on disk: `ls -lh file.mp4` |

---

## Key Code Snippets

### JavaScript Validation
```javascript
const MAX_VIDEO_SIZE = 30 * 1024 * 1024 * 1024; // 30GB

if (file.size > MAX_VIDEO_SIZE) {
    showVideoSizeError(file.size);
    return; // Stop upload
}
```

### Laravel Validation
```php
$videoMaxSize = ($uploadConfig['video']['max_size'] ?? 32212254720) / 1024;
// = 31457280 KB = 30GB

'video_file' => 'nullable|file|max:' . $videoMaxSize
```

### Error Display Function
```javascript
function showVideoSizeError(fileSize) {
    const fileSizeGB = (fileSize / (1024 * 1024 * 1024)).toFixed(2);
    const message = `Video file size (${fileSizeGB}GB) exceeds maximum allowed size of 30GB. Please choose a smaller file.`;
    document.getElementById('videoSizeErrorMessage').textContent = message;
    document.getElementById('videoSizeError').classList.remove('hidden');
}
```

---

## Deployment

```bash
# 1. Pull code
git pull origin enhanced-diagnosis

# 2. Clear cache
php artisan config:clear
php artisan route:clear

# 3. Test
# - Upload 10GB file (should work)
# - Upload 35GB file (should fail with error)

# 4. Done!
```

---

## Size Reference

| Size | Bytes | MB |
|------|-------|-----|
| 1GB | 1,073,741,824 | 1,024 |
| 10GB | 10,737,418,240 | 10,240 |
| **30GB** | **32,212,254,720** | **30,720** |
| 50GB | 53,687,091,200 | 51,200 |
| 100GB | 107,374,182,400 | 102,400 |

---

## Summary

✅ **Maximum video size enforced:** 30GB  
✅ **Frontend validation:** Instant user feedback  
✅ **Backend validation:** Security enforcement  
✅ **Error messages:** Clear and helpful  
✅ **Easy to customize:** Change one config value  
✅ **Applies to:** Create Content Package modal  

---

**Status:** ✅ Implemented and Ready  
**Maximum video size:** 30GB per video  
**Validation:** Both frontend + backend  
