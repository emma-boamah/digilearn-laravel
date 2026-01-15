# 🚀 URGENT: Production Server Actions Required

## THE ISSUE
Your uploads hang with a "(pending)" request in the Network tab because:
1. PHP socket timeout is 60 seconds (too short for large uploads)
2. Storage paths for chunks are incorrect
3. Temporary directories are missing

## ⏰ IMMEDIATE FIX (Production Server)

### Step 1: Fix PHP Socket Timeout (5 minutes)
```bash
# SSH into production server and run:
sudo nano /etc/php/8.2/fpm/php.ini

# Find this line:
# default_socket_timeout = 60

# Replace with:
default_socket_timeout = 3600

# Save and exit (Ctrl+X, Y, Enter)

# Then restart PHP:
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

### Step 2: Create Missing Directories (2 minutes)
```bash
# SSH into production server and run:
mkdir -p /var/www/digilearn-laravel/storage/app/public/temp_chunks
mkdir -p /var/www/digilearn-laravel/storage/app/public/temp_videos

sudo chown -R www-data:www-data /var/www/digilearn-laravel/storage/app/public/temp_chunks
sudo chown -R www-data:www-data /var/www/digilearn-laravel/storage/app/public/temp_videos
sudo chmod -R 755 /var/www/digilearn-laravel/storage/app/public/temp_chunks
sudo chmod -R 755 /var/www/digilearn-laravel/storage/app/public/temp_videos
```

### Step 3: Deploy Updated Code (5 minutes)
```bash
# Pull the latest code that has the storage path fixes:
cd /var/www/digilearn-laravel
git pull origin debug-upload

# Clear Laravel cache:
php artisan config:cache
php artisan cache:clear
```

---

## ✅ DEVELOPMENT: COMPLETED

These changes are already made locally:
- ✅ Fixed AdminController.php storage paths
- ✅ Created temp_chunks and temp_videos directories
- ✅ Code ready to deploy

---

## 🧪 TEST AFTER FIXING

1. Try uploading a **small video** (< 100MB)
   - Should complete in seconds
   - Check Network tab - fetch should complete

2. Monitor `/var/www/digilearn-laravel/storage/logs/laravel.log` for errors

3. Check if files appear in `/var/www/digilearn-laravel/storage/app/public/temp_videos/`

4. Once small uploads work, try **larger files** (> 500MB)

---

## 📋 SUMMARY OF CHANGES

| Issue | Cause | Fix |
|-------|-------|-----|
| Uploads hang at (pending) | PHP socket timeout 60s | Change to 3600s |
| Storage path wrong | Using `app/temp_chunks` | Use `app/public/temp_chunks` |
| Chunks can't save | Missing directories | Create directories with permissions |

---

## ❓ QUESTIONS?

If uploads still hang after these steps:
1. Check error log: `tail -f /var/www/digilearn-laravel/storage/logs/laravel.log`
2. Verify directories exist: `ls -la /var/www/digilearn-laravel/storage/app/public/temp_*`
3. Check PHP timeout: `php -i | grep default_socket_timeout`

Let me know the results! 🎯
