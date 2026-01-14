# Upload System - Production Fix Summary

## Problem Analysis

### Errors Encountered
1. **413 Payload Too Large** - Nginx rejecting request before reaching PHP
2. **500 Internal Server Error** - Controllers returning HTML error pages for AJAX requests
3. **JSON Parse Error** - Browser expecting JSON but receiving HTML error pages
4. **"The video id field is required"** - Frontend not waiting for video upload to complete before uploading documents/quiz

## Root Causes Identified

| Issue | Cause | Impact |
|-------|-------|--------|
| 413 Error | Nginx `client_max_body_size` default is 1MB | Large uploads rejected at server level |
| 500 Errors | Validation rules hardcoded to 20MB | Files exceeding 20MB fail validation |
| HTML Responses | No JSON middleware for upload routes | AJAX requests can't parse error responses |
| Missing video_id | No wait for video completion | Documents/Quiz uploaded before video ID available |

## Solutions Implemented

### 1. Configuration File
**File**: `config/uploads.php` (NEW)
- Centralized upload limits (32GB for videos/documents/quiz)
- MIME type definitions
- Environment variable overrides
- Chunk upload settings (10MB chunks, 3277 max chunks = 32GB)

### 2. Middleware for JSON Error Handling
**File**: `app/Http/Middleware/HandleJsonRequestErrors.php` (NEW)
- Forces JSON Accept header for upload routes
- Ensures validation errors return JSON instead of HTML
- Registered globally in `bootstrap/app.php`

### 3. Dynamic Validation Rules
**Files Updated**:
- `app/Http/Controllers/AdminController.php`
  - `uploadVideoComponent()` - Uses config for video/thumbnail validation
  - `uploadDocumentsComponent()` - Uses config for document validation
  - Added custom error messages for all upload types

**Changes**:
- Video validation: `max:34359738368 bytes, mimes:[mp4, mov, avi, ...]`
- Document validation: `max:34359738368 bytes, mimes:[pdf, doc, docx, ...]`
- Thumbnail validation: `max:5242880 bytes, mimes:[jpeg, png, jpg, ...]`

### 4. Chunked Upload Support (NEW)
**Files Created**:
- `app/Http/Requests/ChunkedVideoUploadRequest.php`
  - Validates individual chunks (max 10MB per chunk)
  - Prevents 413 errors for large files

- `app/Http/Controllers/AdminController.php::uploadVideoChunk()`
  - Receives chunks sequentially
  - Reassembles into final file
  - Cleans up temporary chunk files
  - Returns structured JSON responses

**Route**: `POST /contents/upload/video-chunk`
- Accepts 10MB chunk maximum
- Returns upload progress metadata
- Supports resumable uploads

### 5. Routes Added
**File**: `routes/web.php`
```php
Route::post('/contents/upload/video-chunk', [AdminController::class, 'uploadVideoChunk'])->name('contents.upload.video-chunk');
```

## Nginx Configuration Required

To be applied on **production server** before deployment:

```nginx
# /etc/nginx/nginx.conf or site-specific config

http {
    # Global setting for all uploads
    client_max_body_size 32G;
    client_body_buffer_size 128M;
    
    # Increase timeouts
    proxy_connect_timeout 600s;
    proxy_send_timeout 600s;
    proxy_read_timeout 600s;
    
    # Buffer settings
    proxy_buffering on;
    proxy_buffer_size 128M;
    proxy_buffers 4 256M;
    proxy_busy_buffers_size 512M;
    proxy_max_temp_file_size 2048M;
}
```

**Required Steps**:
1. Update Nginx config
2. Test: `sudo nginx -t`
3. Restart: `sudo systemctl restart nginx`

## PHP Configuration Required

Already verified on production:
- ✅ `post_max_size: 32G`
- ✅ `upload_max_filesize: 32G`
- ✅ `memory_limit: 512M`

No changes needed if these values are correct.

## File Structure

### New Files
```
app/Http/Middleware/HandleJsonRequestErrors.php
app/Http/Requests/ChunkedVideoUploadRequest.php
config/uploads.php
PRODUCTION_UPLOAD_GUIDE.md
```

### Modified Files
```
app/Http/Controllers/AdminController.php
  - uploadVideoComponent() - Added config-based validation
  - uploadDocumentsComponent() - Added config-based validation  
  - uploadVideoChunk() - NEW chunked upload handler

bootstrap/app.php
  - Added HandleJsonRequestErrors middleware import and registration

routes/web.php
  - Added chunked upload route
```

## Frontend Changes (Pending)

The JavaScript upload code in `resources/views/admin/contents/index.blade.php` should be updated to:

1. **For small files** (< 500MB):
   - Use existing `uploadVideoComponent()` endpoint
   - Works fine with current implementation

2. **For large files** (>= 500MB):
   - Switch to `uploadVideoChunk()` endpoint
   - Split file into 10MB chunks
   - Upload sequentially
   - Wait for completion before uploading documents/quiz

**Recommended approach**:
```javascript
if (videoSize > 500 * 1024 * 1024) {
    // Use chunked upload for large files
    await uploadVideoInChunks(videoFile);
} else {
    // Use direct upload for small files
    await uploadVideo(videoFile);
}
```

## Database Migrations

**No database changes required** - Upload system is purely file-based.

## Testing Checklist

- [ ] Deploy code to production
- [ ] Update Nginx configuration
- [ ] Restart Nginx
- [ ] Test video upload < 500MB (direct)
- [ ] Test video upload > 1GB (chunked)
- [ ] Verify document uploads work with video ID
- [ ] Verify quiz uploads work with video ID
- [ ] Check error responses are JSON format
- [ ] Monitor storage disk usage
- [ ] Check Laravel error logs

## Error Codes & Solutions

| Error | Code | Solution |
|-------|------|----------|
| Payload Too Large | 413 | Increase Nginx `client_max_body_size` to 32G |
| File Size Exceeds | 422 | Verify file size < limit in config/uploads.php |
| MIME Type Invalid | 422 | Check file extension against config MIME list |
| Video ID Missing | 422 | Ensure video upload completes before documents/quiz |
| Server Error | 500 | Check `storage/logs/laravel.log` for details |

## Rollback Instructions

If issues occur:

1. **Revert Laravel code**:
   ```bash
   git checkout main
   # or
   git revert <commit-hash>
   ```

2. **Revert Nginx config**:
   ```bash
   sudo cp /etc/nginx/nginx.conf.backup /etc/nginx/nginx.conf
   sudo systemctl restart nginx
   ```

3. **Clean temporary files**:
   ```bash
   rm -rf storage/app/temp_chunks/*
   rm -rf storage/app/temp_videos/*
   ```

## Performance Impact

- **Direct uploads** (< 500MB): No performance change
- **Chunked uploads** (> 500MB): Slight overhead from multiple requests, but prevents 413 errors
- **Reassembly**: Server-side reassembly is fast (sequential file writes)
- **Network**: Chunked approach is more reliable on unstable connections

## Security Notes

- ✅ All endpoints require authentication
- ✅ CSRF token validation enforced
- ✅ MIME type validation on all files
- ✅ File size limits enforced
- ✅ Temporary chunks cleaned after reassembly
- ⚠️ Requires adequate disk space (32GB * concurrent uploads)

## Migration Timeline

1. **Deploy Phase**: Push code to production
2. **Config Phase**: Update Nginx configuration
3. **Restart Phase**: Restart Nginx and PHP-FPM
4. **Test Phase**: Validate with test uploads
5. **Monitor Phase**: Watch logs for 24-48 hours
6. **Full Deploy**: Release to all users

## Support Documentation

See `PRODUCTION_UPLOAD_GUIDE.md` for:
- Detailed Nginx configuration
- PHP configuration requirements
- Troubleshooting steps
- Performance monitoring
- Backup strategies

---

**Version**: 1.0
**Date**: January 2026
**Environment**: Nginx + PHP-FPM + Laravel 11
**Status**: Ready for Production Deployment
