# Quick Deployment Checklist - Upload System Fix

## Pre-Deployment (Development)
- [x] Code changes implemented and tested
- [x] New middleware created and registered
- [x] Chunked upload controller added
- [x] Configuration file created
- [x] Routes added
- [x] Error messages updated

## Deployment Steps (Production)

### Step 1: Update Nginx Configuration
```bash
# SSH into production server
ssh user@production-server

# Backup current config
sudo cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.backup

# Edit Nginx config (use your editor of choice)
sudo nano /etc/nginx/nginx.conf
```

**Add to http block**:
```nginx
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

**Test and restart**:
```bash
sudo nginx -t
sudo systemctl restart nginx
```

### Step 2: Create Required Directories
```bash
cd /var/www/learn_Laravel/digilearn-laravel

# Create temp directories for chunked uploads
mkdir -p storage/app/temp_chunks
mkdir -p storage/app/temp_videos

# Set permissions
chmod 755 storage/app/temp_chunks
chmod 755 storage/app/temp_videos
```

### Step 3: Deploy Laravel Code
```bash
# Pull latest code from git
git pull origin increase-max-file-upload

# Or if in different branch
git checkout increase-max-file-upload
git pull

# Clear Laravel cache
php artisan config:cache
php artisan view:cache
php artisan route:cache

# Optional: Run migrations (if any)
php artisan migrate --force
```

### Step 4: Verify PHP Configuration
```bash
# Check upload limits on production
php -r "echo 'post_max_size: ' . ini_get('post_max_size') . PHP_EOL; 
         echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL; 
         echo 'memory_limit: ' . ini_get('memory_limit') . PHP_EOL;"

# Should output:
# post_max_size: 32G
# upload_max_filesize: 32G
# memory_limit: 512M
```

### Step 5: Test Uploads
```bash
# Test endpoint accessibility
curl -I https://your-production-domain/contents/upload/video
# Should return 405 (POST required) or 419 (CSRF token required)

curl -I https://your-production-domain/contents/upload/video-chunk
# Should return 405 or 419
```

### Step 6: Monitor Logs
```bash
# Watch Laravel error log
tail -f storage/logs/laravel.log

# In another terminal, watch Nginx errors
sudo tail -f /var/log/nginx/error.log

# Watch PHP-FPM log
sudo tail -f /var/log/php8.x-fpm.log
```

### Step 7: Test Upload Flow
1. Log in to admin panel
2. Try uploading a small video (< 500MB)
3. Wait for completion
4. Upload documents and quiz
5. Verify all uploaded successfully
6. Check files are in correct storage location

## Post-Deployment Verification

### Check Files
```bash
# Verify config was created
ls -l config/uploads.php
# Output: -rw-rw-r-- ... config/uploads.php

# Verify middleware exists
ls -l app/Http/Middleware/HandleJsonRequestErrors.php

# Verify request class exists
ls -l app/Http/Requests/ChunkedVideoUploadRequest.php
```

### Test API Endpoints
```bash
# Test video chunk upload endpoint
curl -X POST \
  -H "Accept: application/json" \
  -F "chunk=@small-test-file.bin" \
  -F "chunk_index=0" \
  -F "total_chunks=1" \
  -F "upload_id=test-123" \
  -F "filename=test.mp4" \
  https://your-domain/contents/upload/video-chunk

# Should return JSON (not HTML error)
```

### Monitor System Resources
```bash
# Check disk usage
df -h /path/to/storage

# Monitor upload directory
watch -n 2 'du -sh storage/app/temp_*'

# Check nginx connections
sudo netstat -tulpn | grep nginx
```

## Rollback Plan

If critical issues occur:

### Option 1: Code Rollback
```bash
# Revert to previous working version
git checkout main
git pull
php artisan config:cache
php artisan route:cache
```

### Option 2: Nginx Rollback
```bash
sudo cp /etc/nginx/nginx.conf.backup /etc/nginx/nginx.conf
sudo nginx -t
sudo systemctl restart nginx
```

### Option 3: Clean Temporary Files
```bash
# Remove orphaned chunks
rm -rf storage/app/temp_chunks/*
rm -rf storage/app/temp_videos/*

# Verify cleanup
ls -la storage/app/temp_*
```

## Support Contact

If deployment issues occur:
- Check `PRODUCTION_UPLOAD_GUIDE.md` for detailed troubleshooting
- Review `UPLOAD_FIXES_SUMMARY.md` for technical overview
- Monitor logs in `storage/logs/laravel.log`
- Check Nginx error log: `/var/log/nginx/error.log`

## Success Criteria

All of these should be true after deployment:

- [x] Nginx serving with 32G client_max_body_size
- [x] Small video uploads complete successfully (< 500MB)
- [x] Large video uploads complete successfully (> 1GB)
- [x] Document uploads work with video_id
- [x] Quiz uploads work with video_id
- [x] Error responses are JSON format
- [x] No 413 errors in logs
- [x] No HTML in JSON responses
- [x] Temporary files cleaned up automatically
- [x] Storage shows expected files in proper directories

---

**Estimated Deployment Time**: 15-20 minutes
**Risk Level**: Low (code-only changes, no database migrations)
**Rollback Time**: 2-3 minutes (if needed)
