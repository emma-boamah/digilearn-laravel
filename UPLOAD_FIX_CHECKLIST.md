# ✅ Upload Fix Implementation Checklist

## 🔴 CRITICAL - Production Server (DO FIRST)

### Fix PHP Socket Timeout
- [ ] SSH to production server
- [ ] Edit `/etc/php/8.2/fpm/php.ini`
- [ ] Change: `default_socket_timeout = 60` → `default_socket_timeout = 3600`
- [ ] Save file (Ctrl+X, Y, Enter in nano)
- [ ] Run: `sudo systemctl restart php8.2-fpm`
- [ ] Run: `sudo systemctl restart nginx`
- [ ] Verify: `php -i | grep default_socket_timeout` should show `3600`

### Create Storage Directories  
- [ ] Run: `mkdir -p /var/www/digilearn-laravel/storage/app/public/temp_chunks`
- [ ] Run: `mkdir -p /var/www/digilearn-laravel/storage/app/public/temp_videos`
- [ ] Run: `sudo chown -R www-data:www-data /var/www/digilearn-laravel/storage/app/public/temp_chunks`
- [ ] Run: `sudo chown -R www-data:www-data /var/www/digilearn-laravel/storage/app/public/temp_videos`
- [ ] Run: `sudo chmod -R 755 /var/www/digilearn-laravel/storage/app/public/temp_chunks`
- [ ] Run: `sudo chmod -R 755 /var/www/digilearn-laravel/storage/app/public/temp_videos`
- [ ] Verify: `ls -la /var/www/digilearn-laravel/storage/app/public/ | grep temp`

### Deploy Updated Code
- [ ] Pull latest changes: `cd /var/www/digilearn-laravel && git pull origin debug-upload`
- [ ] Clear cache: `php artisan config:cache`
- [ ] Clear cache: `php artisan cache:clear`

---

## 🟢 DEVELOPMENT (ALREADY DONE)

### Code Changes
- [x] Fixed AdminController.php storage paths (lines 3989-4050)
- [x] Updated temp_chunks path: `app/temp_chunks` → `app/public/temp_chunks`
- [x] Updated temp_videos path: `app/temp_videos` → `app/public/temp_videos`
- [x] Created local directories: `storage/app/public/temp_chunks` and `temp_videos`

### Documentation
- [x] Created UPLOAD_FIX_SUMMARY.md
- [x] Created PRODUCTION_ACTION_REQUIRED.md
- [x] Created CODE_CHANGES_SUMMARY.md
- [x] Created this checklist

---

## 🧪 Testing (After Production Fixes)

### Basic Upload Test
- [ ] Open the admin panel
- [ ] Click "Upload Content"
- [ ] Select a **small video** (< 100MB)
- [ ] Monitor Network tab while uploading
- [ ] **Expected**: Fetch request completes within 30-60 seconds
- [ ] **Check**: Video file appears in `/var/www/digilearn-laravel/storage/app/public/temp_videos/`

### Large File Test  
- [ ] Select a **large video** (500MB - 2GB)
- [ ] Click Finish
- [ ] Monitor Network tab
- [ ] **Expected**: Multiple POST requests for chunks, progress bar shows real progress
- [ ] **Check**: temp_videos directory contains reassembled file

### Error Checking
- [ ] Check logs: `tail -f /var/www/digilearn-laravel/storage/logs/laravel.log`
- [ ] Look for any PHP errors or warnings
- [ ] Verify no "socket timeout" errors appear

---

## 🔍 Verification

### Production Server Verification
```bash
# Check PHP timeout is correct
php -i | grep default_socket_timeout
# Should output: default_socket_timeout => 3600

# Check directories exist
ls -la /var/www/digilearn-laravel/storage/app/public/temp_chunks
ls -la /var/www/digilearn-laravel/storage/app/public/temp_videos
# Both should show rwxr-xr-x permissions with www-data owner

# Check no old temp files are stuck
ls /var/www/digilearn-laravel/storage/app/public/temp_videos/
# Should be empty (or show only recently uploaded files)
```

### Code Verification
```bash
# Verify correct paths in code
grep -n "app/public/temp_chunks" /var/www/digilearn-laravel/app/Http/Controllers/AdminController.php
grep -n "app/public/temp_videos" /var/www/digilearn-laravel/app/Http/Controllers/AdminController.php
# Should show multiple matches
```

---

## 📞 Troubleshooting

### Upload Still Hangs After Fixes?

**Step 1: Check PHP timeout**
```bash
php -i | grep default_socket_timeout
# Must be 3600, not 60
```

**Step 2: Check directories exist and are writable**
```bash
ls -la /var/www/digilearn-laravel/storage/app/public/temp_chunks
ls -la /var/www/digilearn-laravel/storage/app/public/temp_videos
# Both should show: drwxr-xr-x with www-data owner
```

**Step 3: Check logs for errors**
```bash
tail -50 /var/www/digilearn-laravel/storage/logs/laravel.log
# Look for "socket timeout" or "permission denied" errors
```

**Step 4: Verify Nginx config**
```bash
grep -A 3 "client_max_body_size" /etc/nginx/sites-available/shoutoutgh.com
# Should show: client_max_body_size 32G;
```

**Step 5: Check if request reaches server**
```bash
# In another terminal, watch logs:
tail -f /var/www/digilearn-laravel/storage/logs/laravel.log

# Then try upload in browser
# You should see log entries appearing in real-time
```

---

## 📊 Expected Behavior

### Before Fix
- Click Finish → Progress modal shows
- Network tab shows fetch to `/admin/contents/upload/video` 
- Status: "(pending)" 0.0 kB
- After 60 seconds: Connection timeout or hangs indefinitely

### After Fix
- Click Finish → Progress modal shows
- Network tab shows fetch requests
- For small files: 1 fetch request completes in seconds
- For large files: Multiple chunk upload requests, progress bar updates
- Status: "200 OK" when complete
- File saved to `/var/www/digilearn-laravel/storage/app/public/temp_videos/`

---

## 🎯 Summary

| Task | Status | Timeline |
|------|--------|----------|
| Fix PHP timeout | Required | 5 min |
| Create directories | Required | 2 min |
| Deploy code | Required | 5 min |
| Test small upload | Verify | 5 min |
| Test large upload | Verify | ~5 min |
| **Total** | **Required** | **22 min** |

All fixes are ready. Just need to execute on production server! 🚀
