# Chunked Upload Troubleshooting Guide

## Issue: Upload Stuck at 50% with "Sending to server..."

Your upload appears stuck because the chunked upload system has field name mismatches. This has been fixed.

## What Was Wrong

### Frontend Issue (FIXED ✅)
**File**: `resources/views/admin/contents/index.blade.php`

**Problem**: Field names didn't match what the backend expects
```javascript
// BEFORE (Wrong)
chunkFormData.append('chunk_number', chunkIndex);      // ❌ Backend expects 'chunk_index'
chunkFormData.append('chunk_file', chunk);              // ❌ Backend expects 'chunk'
// Missing: filename

// AFTER (Fixed)
chunkFormData.append('chunk_index', chunkIndex);        // ✅ Correct
chunkFormData.append('chunk', chunk);                   // ✅ Correct
chunkFormData.append('filename', videoFile.name);       // ✅ Added
```

### Backend Expectations
**File**: `app/Http/Requests/ChunkedVideoUploadRequest.php`

Required validation rules:
```php
[
    'chunk' => 'required|file|max:' . $chunkMaxSize,
    'chunk_index' => 'required|integer|min:0',           // ← This is what frontend must send
    'total_chunks' => 'required|integer|min:1',
    'upload_id' => 'required|string',
    'filename' => 'required|string',
]
```

## Verification Steps

### Step 1: Clear Cache (Important!)
After code changes, clear Laravel cache:
```bash
php artisan config:cache
php artisan view:cache --force
```

### Step 2: Hard Refresh Browser
The JavaScript needs to be reloaded:
- Press **Ctrl + Shift + R** (Chrome/Firefox)
- Or press **Cmd + Shift + R** (Mac)
- Or go to DevTools → Network → Disable cache → Refresh

### Step 3: Check Console for Errors
1. Open **DevTools** (F12)
2. Go to **Console** tab
3. Look for any red errors
4. Try uploading again and watch console

### Step 4: Monitor Network Requests
1. Open **DevTools** (F12)
2. Go to **Network** tab
3. Filter by "video-chunk"
4. Start upload
5. Watch requests being sent
6. Look for:
   - **Status 200**: Good, chunk accepted
   - **Status 422**: Validation error (field names wrong)
   - **Status 500**: Server error (check logs)
   - **No response**: Network blocked or timeout

### Step 5: Check Server Logs
```bash
tail -f storage/logs/laravel.log
```

Watch for:
- ✅ "Chunk uploaded successfully"
- ❌ "Missing required field"
- ❌ "File upload failed"
- ❌ Exceptions

## How Chunked Upload Works

### Upload Flow
```
User selects video (410 MB)
        ↓
Check file size (> 500MB? Use chunks)
        ↓
Split into chunks (10 MB each)
   → 410 MB ÷ 10 MB = 41 chunks
        ↓
Upload each chunk sequentially
   → /admin/contents/upload/video-chunk
   → Chunk 1/41 → Chunk 2/41 → ... → Chunk 41/41
        ↓
Backend receives chunk
   → Validates (field names, file size, etc.)
   → Stores in storage/app/temp_chunks/{uploadId}/chunk_N
   → Checks if all chunks received
        ↓
All chunks received?
   → YES: Reassemble file from chunks
   → Delete individual chunks
   → Return success
   → Frontend sends metadata (title, subject, etc.)
   → Backend creates video record
   → Video uploaded! ✅
        ↓
   → NO: Wait for more chunks
   → Return progress
```

### Field Mapping
What frontend sends → What backend expects:

```
_token              → _token (CSRF token)
upload_id           → upload_id (unique upload session ID)
chunk_index         → chunk_index (0, 1, 2, ..., 40) ← THIS WAS WRONG
total_chunks        → total_chunks (41 for 410MB file)
chunk               → chunk (the actual file chunk) ← THIS WAS WRONG
filename            → filename (original filename) ← THIS WAS MISSING
```

## Common Issues & Solutions

### Issue 1: "Chunk upload failed" Error
**Cause**: Invalid field names or validation error

**Solution**:
1. Check browser DevTools → Network tab
2. Click failed request
3. Look at Response tab
4. If you see `"errors": {"chunk_index": "..."}` → field name is wrong
5. Verify the fix was applied (see code fix above)

### Issue 2: Chunks Upload But Then Stuck
**Cause**: Server is waiting for all chunks, but frontend stopped sending

**Solution**:
1. Check total_chunks sent matches file chunks needed
2. Calculate: `Math.ceil(fileSize / 10485760)` 
3. For 410MB: `Math.ceil(410 * 1024 * 1024 / 10485760)` = 41 chunks
4. Watch Network tab - count requests
5. If less than 41, upload incomplete

### Issue 3: Validation Error "chunk.required"
**Cause**: Field named `chunk_file` instead of `chunk`

**Solution**:
- Verify fix was applied: `chunkFormData.append('chunk', chunk);`
- Hard refresh browser (Ctrl+Shift+R)
- Clear cache: `php artisan view:cache --force`

### Issue 4: Memory Exhausted or Timeout
**Cause**: Server settings or very large file

**Solution**:
Check Nginx config:
```nginx
client_max_body_size 32G;  # ← Should be at least chunk size
```

Check PHP config:
```php
upload_max_filesize = 32G
post_max_size = 32G
max_execution_time = 300  # 5 minutes minimum
```

### Issue 5: Chunks Upload But File Not Created
**Cause**: Reassembly failing or temp directory issues

**Solution**:
1. Check `storage/app/temp_chunks/` exists and is writable
2. Check `storage/app/temp_videos/` exists and is writable
3. Check Laravel logs: `tail -f storage/logs/laravel.log`
4. Verify chunk count matches (all chunks received)

## Testing Checklist

- [ ] Code changes applied (frontend chunk field names)
- [ ] Browser cache cleared (Hard refresh Ctrl+Shift+R)
- [ ] Laravel cache cleared (`php artisan config:cache`)
- [ ] Storage directories exist: `storage/app/temp_chunks/`, `storage/app/temp_videos/`
- [ ] Directories are writable: `chmod 755 storage/app/`
- [ ] Nginx `client_max_body_size` >= 32GB
- [ ] PHP `upload_max_filesize` >= 32GB
- [ ] Logs checked for errors: `tail -f storage/logs/laravel.log`
- [ ] Network tab shows 200 OK responses
- [ ] Total chunks matches calculation
- [ ] All chunks uploaded before reassembly

## Expected Behavior After Fix

### For 410 MB Video Upload:

**Timeline:**
```
0s:     5% "Preparing video data..."
0.5s:   6% "Uploading... Chunk 1/41"
1s:     8% "Uploading... Chunk 2/41 | 1.0 GB/2.0 GB | 20 MB/s | 50s remaining"
3s:    13% "Uploading... Chunk 5/41 | 500 MB/1.0 GB | 21 MB/s | 24s remaining"
10s:   30% "Uploading... Chunk 13/41 | 130 MB/410 MB | 13 MB/s | 21s remaining"
20s:   54% "Uploading... Chunk 22/41 | 220 MB/410 MB | 11 MB/s | 17s remaining"
30s:   78% "Uploading... Chunk 32/41 | 320 MB/410 MB | 10.7 MB/s | 8s remaining"
38s:   95% "Processing video on server..."
40s:  100% "Video uploaded successfully!" ✅
```

Progress bar updates every 1-2 seconds with:
- Real percentage (5-95%, not fake 50%)
- Current chunk number
- Bytes uploaded / total bytes
- Upload speed in MB/s
- Estimated time remaining
- Realistic chunk progress

### Progress Bar:
```
Before: ████████░░░░░░░░░░░░░░░░░░░░░░░░ 50% (stuck 30+ seconds)

After:  █████░░░░░░░░░░░░░░░░░░░░░░░░░░░░ 5% (starts)
        ██████░░░░░░░░░░░░░░░░░░░░░░░░░░░░ 8%
        ████████░░░░░░░░░░░░░░░░░░░░░░░░░░ 13%
        ██████████░░░░░░░░░░░░░░░░░░░░░░░░ 20%
        ████████████░░░░░░░░░░░░░░░░░░░░░░ 30%
        ██████████████░░░░░░░░░░░░░░░░░░░░ 40%
        ████████████████░░░░░░░░░░░░░░░░░░ 50%
        ██████████████████░░░░░░░░░░░░░░░░ 60%
        ████████████████████░░░░░░░░░░░░░░ 70%
        ██████████████████████░░░░░░░░░░░░ 80%
        ████████████████████████░░░░░░░░░░ 90%
        ██████████████████████████░░░░░░░░ 95%
        ███████████████████████████████░░░ 100%
```

## Performance Notes

### Network Throughput
- Chunk size: 10 MB
- Upload speed depends on connection
- Typical speeds: 5-50 MB/s

### On 410 MB Upload:
```
5 MB/s  → ~82 seconds total
10 MB/s → ~41 seconds total
20 MB/s → ~20 seconds total
50 MB/s → ~8 seconds total
```

### Progress Updates
- Update interval: Every 1-2 seconds (when speed calculation completes)
- No updates during chunk upload (real-time tracking)
- Smooth transition from 5% → 95% → 100%

## If Still Having Issues

1. **Check Network Tab**:
   - Status should be 200, not 422 or 500
   - Response should be JSON: `{"success": true, ...}`

2. **Check Console**:
   - Any red errors?
   - What's the exact error message?

3. **Check Server Logs**:
   ```bash
   tail -100 storage/logs/laravel.log | grep -i upload
   tail -100 storage/logs/laravel.log | grep -i chunk
   ```

4. **Check Temp Directories**:
   ```bash
   ls -la storage/app/temp_chunks/
   ls -la storage/app/temp_videos/
   ```

5. **Test with Smaller File First**:
   - Upload 50 MB file to test chunks working
   - Then try 410 MB

## Files Modified

✅ **Fixed**: `/resources/views/admin/contents/index.blade.php`
- Changed `chunk_number` → `chunk_index`
- Changed `chunk_file` → `chunk`
- Added `filename` field

No backend changes needed - controller already has correct logic.

---

**Need Help?**
- Check the Network tab in DevTools (F12)
- Look at browser console for errors
- Check `storage/logs/laravel.log` for server errors
- Verify all cache cleared: `php artisan cache:clear`
