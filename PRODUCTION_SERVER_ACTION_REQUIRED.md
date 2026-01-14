# PRODUCTION SERVER - ACTION REQUIRED

## Current Status
✅ Code changes implemented and pushed to `increase-max-file-upload` branch
⚠️  **REQUIRES** Nginx configuration update before deployment

## What Was Fixed in Code

### 1. Configuration System (`config/uploads.php`)
- Centralized 32GB upload limits
- Environment variable overrides
- MIME type definitions
- Chunk upload settings (10MB chunks)

### 2. Validation Rules (Controllers)
**Before**: Hardcoded `max:20480` (20MB) - causing 500 errors
**After**: Dynamic from config `max:34359738368` (32GB)

**Files Updated**:
- `app/Http/Controllers/AdminController.php`
  - `uploadVideoComponent()` - Uses config for validation
  - `uploadDocumentsComponent()` - Uses config for validation
  - `uploadVideoChunk()` - NEW chunked upload handler

### 3. JSON Error Responses (Middleware)
**Problem**: Validation errors returned HTML, not JSON
**Solution**: `HandleJsonRequestErrors` middleware forces JSON responses

### 4. Chunked Upload Support
**New Endpoint**: `POST /contents/upload/video-chunk`
**Feature**: Allows uploading large files in 10MB chunks
**Benefit**: Bypasses 413 errors for files > 1GB

---

## What Still Needs Configuration on Production Server

### ⚠️ CRITICAL: Nginx Configuration

Your production server's Nginx **currently rejects** large uploads with **413 errors**.

**Current state** (incorrect):
```
client_max_body_size 1M;  // Default - too small!
```

**Required state** (to enable 32GB uploads):
```
client_max_body_size 32G;
client_body_buffer_size 128M;
proxy_connect_timeout 600s;
proxy_send_timeout 600s;
proxy_read_timeout 600s;
proxy_buffering on;
proxy_buffer_size 128M;
proxy_buffers 4 256M;
proxy_busy_buffers_size 512M;
proxy_max_temp_file_size 2048M;
```

**Steps to apply**:
1. SSH to production server
2. Edit `/etc/nginx/nginx.conf` or site-specific config
3. Add above settings to `http` block
4. Test: `sudo nginx -t`
5. Restart: `sudo systemctl restart nginx`

### ✅ PHP Configuration (Already Correct)

Your PHP on production is already correctly configured:
- `post_max_size: 32G` ✅
- `upload_max_filesize: 32G` ✅
- `memory_limit: 512M` ✅

**No PHP changes needed!**

### 📁 Create Directories

```bash
mkdir -p storage/app/temp_chunks
mkdir -p storage/app/temp_videos
chmod 755 storage/app/temp_chunks
chmod 755 storage/app/temp_videos
```

---

## Why This Was Necessary

### Error 1: 413 Payload Too Large
```
Failed to load resource: the server responded with a status of 413 ()
```
**Cause**: Nginx rejecting request (even before reaching PHP)
**Fix**: Increase `client_max_body_size` in Nginx config

### Error 2: 500 Server Error with JSON Parse Failure
```
Video upload failed: Unexpected token '<', "<html> <h"... is not valid JSON
```
**Cause 1**: Validation failed because max size was 20MB (config was not used)
**Fix 1**: Updated controllers to use config values (now 32GB)

**Cause 2**: Error response was HTML, not JSON
**Fix 2**: Added middleware to force JSON responses for upload endpoints

### Error 3: "The video id field is required" for Documents/Quiz
**Cause**: Documents/quiz uploaded before video upload completed
**Fix**: Frontend must wait for video upload response before uploading documents

---

## Files Changed/Created

### New Files (on `increase-max-file-upload` branch)
```
✨ config/uploads.php
✨ app/Http/Middleware/HandleJsonRequestErrors.php
✨ app/Http/Requests/ChunkedVideoUploadRequest.php
📄 PRODUCTION_UPLOAD_GUIDE.md
📄 UPLOAD_FIXES_SUMMARY.md
📄 DEPLOYMENT_CHECKLIST.md
```

### Modified Files (on `increase-max-file-upload` branch)
```
✏️  app/Http/Controllers/AdminController.php
   - uploadVideoComponent() - Updated validation rules
   - uploadDocumentsComponent() - Updated validation rules
   - uploadVideoChunk() - NEW method for chunked uploads

✏️  bootstrap/app.php
   - Added HandleJsonRequestErrors middleware

✏️  routes/web.php
   - Added POST /contents/upload/video-chunk route
```

---

## Deployment Flow

### On Your Development Server (Already Done)
1. ✅ Code changes implemented
2. ✅ Config file created
3. ✅ Middleware added
4. ✅ Routes registered
5. ✅ Tests passing

### On Production Server (Still Needed)
1. ⏳ Update Nginx configuration
2. ⏳ Create temp directories
3. ⏳ Pull latest code from git
4. ⏳ Run Laravel cache clear
5. ⏳ Test endpoints

---

## Testing Checklist (Production)

After applying Nginx config and deploying code:

- [ ] Nginx restart successful: `sudo systemctl restart nginx`
- [ ] Temp directories created: `ls -la storage/app/temp_*`
- [ ] Code deployed: `git pull origin increase-max-file-upload`
- [ ] Cache cleared: `php artisan config:cache`
- [ ] Small video uploads work (< 500MB)
- [ ] Large video uploads work (> 1GB)
- [ ] Documents upload after video completes
- [ ] Quiz uploads after video completes
- [ ] Error responses are JSON (check console)
- [ ] No 413 errors in nginx error log
- [ ] Storage shows expected files

---

## Quick Reference Commands

### Nginx Configuration
```bash
# SSH to server and edit
sudo nano /etc/nginx/nginx.conf

# After editing - test and restart
sudo nginx -t
sudo systemctl restart nginx

# Verify it's running
sudo systemctl status nginx
```

### Create Directories
```bash
cd /var/www/learn_Laravel/digilearn-laravel
mkdir -p storage/app/temp_chunks storage/app/temp_videos
chmod 755 storage/app/temp_*
```

### Deploy Code
```bash
git checkout increase-max-file-upload
git pull
php artisan config:cache
php artisan route:cache
```

### Monitor
```bash
# Laravel errors
tail -f storage/logs/laravel.log

# Nginx errors
sudo tail -f /var/log/nginx/error.log
```

---

## What Will Work After Deployment

### Small Uploads (< 500MB)
- Video upload directly
- All current functionality preserved
- No performance change

### Large Uploads (>= 500MB)
- Video upload in 10MB chunks
- Chunks reassembled on server
- No 413 errors
- Progress tracking per chunk

### All Upload Types
- Dynamic validation from config
- JSON error responses
- Proper error messages
- Sequential processing (video → documents → quiz)

---

## Important Notes

⚠️ **DO NOT proceed with deployment until Nginx is configured**
- Without `client_max_body_size 32G`, uploads will still fail with 413 errors
- Code alone cannot fix Nginx-level limits

✅ **After Nginx config, everything is automatic**
- Controllers handle validation from config
- Middleware ensures JSON responses
- Chunked uploads work seamlessly
- Temp files cleaned up automatically

📊 **Monitor disk space**
- 32GB * concurrent users possible in temp directory
- Clean up regularly: `rm -rf storage/app/temp_chunks/* storage/app/temp_videos/*`

---

## Need Help?

See these documents for detailed information:
- `PRODUCTION_UPLOAD_GUIDE.md` - Full Nginx configuration & troubleshooting
- `UPLOAD_FIXES_SUMMARY.md` - Technical details of all changes
- `DEPLOYMENT_CHECKLIST.md` - Step-by-step deployment guide

---

**Status**: Ready for Production Deployment (after Nginx config)
**Risk Level**: Low (no database changes, code-only)
**Estimated Config Time**: 5-10 minutes
**Estimated Deployment Time**: 10-15 minutes
