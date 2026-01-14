# Production Deployment Guide - Large File Upload Support

## Problem Summary
- **Development (Apache)**: Small upload limits (~100MB-256MB)
- **Production (Nginx)**: Configured for 32GB uploads but requests are being rejected with 413 errors

## Root Causes
1. **Nginx `client_max_body_size` too small** - Default is only 1MB
2. **Sending entire file at once** - Causes payload to exceed limits
3. **Validation errors returning HTML instead of JSON** - AJAX expects JSON responses

## Solutions Implemented

### 1. Nginx Configuration (Required)
Update your Nginx server block to support large uploads:

```nginx
# /etc/nginx/sites-available/your-domain (or nginx.conf http block)

http {
    # Allow uploads up to 32GB
    client_max_body_size 32G;
    
    # Increase timeouts for large uploads
    proxy_connect_timeout 600s;
    proxy_send_timeout 600s;
    proxy_read_timeout 600s;
    
    # Enable buffering for large files
    client_body_buffer_size 128M;
    proxy_buffering on;
    proxy_buffer_size 128M;
    proxy_buffers 4 256M;
    proxy_busy_buffers_size 512M;
    proxy_max_temp_file_size 2048M;
}

# In your server block:
server {
    # Specific upload endpoint with high limits
    location ~ ^/api/contents/upload/ {
        client_max_body_size 32G;
        client_body_buffer_size 128M;
        
        # ... other directives
    }
}
```

**After updating**: `sudo systemctl restart nginx`

### 2. PHP Configuration (FPM)
Ensure PHP-FPM is configured to handle large uploads:

```ini
# /etc/php/8.x/fpm/php.ini (or 8.3, 8.2 depending on your version)

post_max_size = 32G
upload_max_filesize = 32G
memory_limit = 512M
default_socket_timeout = 600
max_execution_time = 600
max_input_time = 600

# For chunked uploads (optional but recommended)
file_uploads = On
upload_tmp_dir = /var/tmp/php-uploads
```

**After updating**: `sudo systemctl restart php8.x-fpm`

### 3. Laravel Configuration
Already implemented:
- ✅ `config/uploads.php` - Centralized upload limits
- ✅ `Middleware/HandleJsonRequestErrors.php` - Ensures JSON error responses
- ✅ `ChunkedVideoUploadRequest.php` - Validates chunked uploads
- ✅ `AdminController::uploadVideoChunk()` - Handles chunked reassembly
- ✅ Dynamic validation rules - Uses config instead of hardcoded values

### 4. Frontend JavaScript (Chunked Upload)
The JavaScript needs to be updated to use chunked uploads. This prevents the 413 error by:
- Breaking large files into 10MB chunks
- Uploading sequentially
- Reassembling on server

**Implementation location**: `resources/views/admin/contents/index.blade.php`

Key JavaScript functions to add:
```javascript
async function uploadVideoInChunks(videoFile) {
    const chunkSize = 10 * 1024 * 1024; // 10MB chunks
    const totalChunks = Math.ceil(videoFile.size / chunkSize);
    const uploadId = generateUUID();
    
    for (let i = 0; i < totalChunks; i++) {
        const start = i * chunkSize;
        const end = Math.min(start + chunkSize, videoFile.size);
        const chunk = videoFile.slice(start, end);
        
        const formData = new FormData();
        formData.append('chunk', chunk);
        formData.append('chunk_index', i);
        formData.append('total_chunks', totalChunks);
        formData.append('upload_id', uploadId);
        formData.append('filename', videoFile.name);
        formData.append('_token', '{{ csrf_token() }}');
        
        const response = await fetch('{{ route("contents.upload.video-chunk") }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData
        });
        
        const result = await response.json();
        if (!result.success) throw new Error(result.message);
        
        // Update progress: (i + 1) / totalChunks * 100
        const progress = Math.round(((i + 1) / totalChunks) * 100);
        updateProgress('video', progress);
    }
    
    return uploadId;
}
```

## Deployment Checklist

- [ ] Update Nginx configuration with `client_max_body_size 32G`
- [ ] Restart Nginx: `sudo systemctl restart nginx`
- [ ] Verify PHP `post_max_size` and `upload_max_filesize` are 32G
- [ ] Restart PHP-FPM: `sudo systemctl restart php8.x-fpm`
- [ ] Create temp directories:
  ```bash
  mkdir -p /path/to/storage/app/temp_chunks
  mkdir -p /path/to/storage/app/temp_videos
  chmod 755 /path/to/storage/app/temp_*
  ```
- [ ] Test chunked upload endpoint before deploying
- [ ] Deploy code changes from development branch
- [ ] Monitor production logs for upload errors

## Testing Production Upload

1. **Small file test** (< 100MB):
   - Upload via normal endpoint: `POST /contents/upload/video`
   - Should work if PHP limits are set correctly

2. **Large file test** (> 1GB):
   - Upload via chunked endpoint: `POST /contents/upload/video-chunk`
   - Monitor progress to ensure chunks upload successfully
   - Check reassembled file integrity

## Error Codes Reference

| Code | Meaning | Solution |
|------|---------|----------|
| **413** | Payload Too Large | Increase Nginx `client_max_body_size` |
| **500** | Server Error | Check Laravel logs: `storage/logs/laravel.log` |
| **422** | Validation Failed | Check field validation in request class |
| **405** | Method Not Allowed | Verify route POST method is correct |

## Monitoring

Monitor these logs after deployment:
```bash
# Laravel errors
tail -f storage/logs/laravel.log

# Nginx errors  
tail -f /var/log/nginx/error.log

# PHP-FPM errors
tail -f /var/log/php8.x-fpm.log
```

## Rollback Plan

If issues occur:

1. **For Nginx limits**: Revert to previous nginx.conf
2. **For Laravel code**: Rollback git branch
3. **For temporary files**: Clean up orphaned chunks:
   ```bash
   rm -rf storage/app/temp_chunks/*
   rm -rf storage/app/temp_videos/*
   ```

## Performance Notes

- **Chunked uploads** bypass the 413 limit but are slightly slower (network overhead)
- **Direct uploads** (small files) are faster
- **Recommend**: Use chunked for files > 500MB, direct for smaller files
- **Optimal chunk size**: 10MB (balance between overhead and reliability)

## Security Considerations

- ✅ CSRF token validation on all upload endpoints
- ✅ User authentication required (admin/teacher/content_creator)
- ✅ File MIME type validation
- ✅ Filename sanitization
- ✅ Temporary files cleaned up after reassembly
- ⚠️ Monitor disk space: 32GB * concurrent users could fill disk

---

**Last Updated**: January 2026
**Environment**: Nginx + PHP-FPM + Laravel 11
**Max Upload**: 32GB (configurable via env variables)
