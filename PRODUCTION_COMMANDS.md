# Production Server - Exact Commands to Run

## Copy-Paste These Commands in Order

### Step 1: Fix PHP Socket Timeout (SSH to Production)

```bash
# Edit the PHP config file
sudo nano /etc/php/8.2/fpm/php.ini
```

Once nano opens:
1. Press: `Ctrl+W` (search)
2. Type: `default_socket_timeout`
3. Press: `Enter`
4. You'll see: `default_socket_timeout = 60`
5. Change `60` to `3600`
6. Press: `Ctrl+X` (exit)
7. Press: `Y` (yes to save)
8. Press: `Enter` (confirm filename)

---

### Step 2: Restart PHP-FPM and Nginx

```bash
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

Verify the changes took effect:
```bash
php -i | grep default_socket_timeout
```

Expected output:
```
default_socket_timeout => 3600 => 3600
```

---

### Step 3: Create Storage Directories

```bash
# Create both temp directories
mkdir -p /var/www/digilearn-laravel/storage/app/public/temp_chunks
mkdir -p /var/www/digilearn-laravel/storage/app/public/temp_videos

# Set ownership to www-data
sudo chown -R www-data:www-data /var/www/digilearn-laravel/storage/app/public/temp_chunks
sudo chown -R www-data:www-data /var/www/digilearn-laravel/storage/app/public/temp_videos

# Set permissions
sudo chmod -R 755 /var/www/digilearn-laravel/storage/app/public/temp_chunks
sudo chmod -R 755 /var/www/digilearn-laravel/storage/app/public/temp_videos
```

Verify directories were created:
```bash
ls -la /var/www/digilearn-laravel/storage/app/public/ | grep temp
```

Expected output:
```
drwxr-xr-x  2 www-data www-data 4096 Jan 14 17:35 temp_chunks
drwxr-xr-x  2 www-data www-data 4096 Jan 14 17:35 temp_videos
```

---

### Step 4: Deploy Updated Code

```bash
cd /var/www/digilearn-laravel

# Pull the latest code with fixes
git pull origin debug-upload

# Clear Laravel cache
php artisan config:cache
php artisan cache:clear

# Optional: Restart queue workers if using async uploads
php artisan queue:restart
```

---

### Step 5: Verify Everything is Ready

```bash
# 1. Check PHP timeout
echo "Checking PHP timeout..."
php -i | grep default_socket_timeout

# 2. Check directories exist
echo "Checking directories..."
ls -la /var/www/digilearn-laravel/storage/app/public/ | grep -E "temp_chunks|temp_videos"

# 3. Check code is deployed
echo "Checking code..."
grep -n "app/public/temp_chunks" /var/www/digilearn-laravel/app/Http/Controllers/AdminController.php

# 4. Check Nginx config
echo "Checking Nginx..."
grep "client_max_body_size" /etc/nginx/sites-available/shoutoutgh.com
```

Expected outputs:
- PHP timeout: `default_socket_timeout => 3600 => 3600`
- Directories: Both should exist with `drwxr-xr-x` and `www-data` owner
- Code: Should find match in AdminController.php
- Nginx: Should show `client_max_body_size 32G;`

---

## Test the Fix

### Test 1: Small File Upload (< 100MB)

1. Open admin panel: https://shoutoutgh.com/admin
2. Click "Upload Content"
3. Select a video file smaller than 100MB
4. Fill in title, subject, etc.
5. Click "Finish"
6. **Expected**: Upload completes in 30-60 seconds
7. **Check**: Network tab shows completed request (green ✓)
8. **Verify**: File appears in `/var/www/digilearn-laravel/storage/app/public/temp_videos/`

### Test 2: Large File Upload (> 500MB)

1. Open admin panel: https://shoutoutgh.com/admin
2. Click "Upload Content"
3. Select a video file larger than 500MB
4. Fill in form fields
5. Click "Finish"
6. **Expected**: Progress bar shows chunk uploads
7. **Expected**: Multiple POST requests visible in Network tab
8. **Expected**: Upload completes (takes longer depending on file size)
9. **Verify**: Final reassembled file in `/var/www/digilearn-laravel/storage/app/public/temp_videos/`

### Test 3: Check Logs for Errors

```bash
# Monitor logs in real-time while uploading
tail -f /var/www/digilearn-laravel/storage/logs/laravel.log

# Look for these GOOD messages:
# "Video uploaded successfully"
# "All chunks uploaded successfully"

# Watch for these BAD messages:
# "socket timeout"
# "permission denied"
# "file not found"
```

---

## Rollback (If Something Goes Wrong)

### Undo PHP Config Change

```bash
sudo nano /etc/php/8.2/fpm/php.ini
# Change default_socket_timeout back to 60
# Save and exit
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

### Revert Code Changes

```bash
cd /var/www/digilearn-laravel
git revert HEAD  # Reverts the last commit
git push origin debug-upload
```

---

## Troubleshooting

### If upload still hangs:

```bash
# 1. Verify PHP timeout actually changed
php -i | grep default_socket_timeout
# Must show: 3600 (not 60)

# 2. Check if PHP-FPM restarted successfully
sudo systemctl status php8.2-fpm
# Should show: active (running)

# 3. Check Nginx restarted successfully  
sudo systemctl status nginx
# Should show: active (running)

# 4. Check permissions on temp directories
ls -la /var/www/digilearn-laravel/storage/app/public/temp_chunks
ls -la /var/www/digilearn-laravel/storage/app/public/temp_videos
# Both should show: drwxr-xr-x with www-data owner

# 5. Check disk space
df -h /var
# Should have > 50GB free

# 6. Check Laravel error logs
tail -100 /var/www/digilearn-laravel/storage/logs/laravel.log
# Look for actual error messages
```

---

## Quick Reference

| Setting | File | Change |
|---------|------|--------|
| Socket timeout | `/etc/php/8.2/fpm/php.ini` | 60 → 3600 |
| Temp chunks | `/var/www/digilearn-laravel/storage/app/public/` | Create `temp_chunks` dir |
| Temp videos | `/var/www/digilearn-laravel/storage/app/public/` | Create `temp_videos` dir |
| Code | `app/Http/Controllers/AdminController.php` | Deploy latest version |

---

## Success Criteria

After applying all fixes, you should see:

✅ PHP socket timeout = 3600 seconds  
✅ temp_chunks directory exists and is writable  
✅ temp_videos directory exists and is writable  
✅ Code deployed with correct paths  
✅ Small uploads complete in < 1 minute  
✅ Large uploads show real progress  
✅ No "pending" requests hanging  
✅ No timeout errors in logs  

When all these are true, **the fix is complete!** 🎉

---

## Need Help?

If you get stuck:
1. Take a screenshot of the error
2. Run: `tail -50 /var/www/digilearn-laravel/storage/logs/laravel.log`
3. Copy the error output
4. Share both with your development team

Common issues and fixes available in the other documentation files.
