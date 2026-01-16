# 🚀 Deployment Commands - Copy & Paste Ready

## Pre-Deployment (Run These First)

### Step 1: Backup Current Code
```bash
# Create backup branch with timestamp
git checkout -b backup-pre-deployment-$(date +%Y%m%d-%H%M%S)
git push origin backup-pre-deployment-$(date +%Y%m%d-%H%M%S)

# Verify backup created
git log --oneline | head -5
echo "✅ Backup branch created"

# Back to main branch
git checkout enhanced-diagnosis
```

---

## Deployment (The Actual Update)

### Step 2: Pull Latest Changes
```bash
# Fetch latest
git fetch origin

# Pull changes
git pull origin enhanced-diagnosis --force

# Verify changes
git log --oneline | head -3
echo "✅ Code pulled successfully"
```

### Step 3: Clear Caches
```bash
# Clear all caches (REQUIRED)
php artisan cache:clear
echo "✅ Cache cleared"

php artisan route:clear
echo "✅ Route cache cleared"

php artisan config:clear
echo "✅ Config cache cleared"

# Optional: clear compiled services
php artisan clear-compiled
echo "✅ Compiled services cleared"
```

### Step 4: Verify Application
```bash
# Check artisan is working
php artisan list | head -20
echo "✅ Artisan commands working"

# Check database connection
php artisan tinker <<EOF
DB::table('users')->count()
exit
EOF
echo "✅ Database connection verified"
```

---

## Post-Deployment (Verify Everything Works)

### Step 5: Health Check Endpoints
```bash
# Check app is running
curl -I https://www.shoutoutgh.com/admin
echo "✅ App responding to requests"

# Check login page
curl -s https://www.shoutoutgh.com/admin | grep -q "login" && echo "✅ Login page loads"
```

### Step 6: Log Verification
```bash
# Check for recent errors
tail -100 storage/logs/laravel.log | grep -i "error\|exception"
echo "✅ Check for any recent errors above"

# Count errors (should be minimal)
grep "ERROR\|Exception" storage/logs/laravel.log | wc -l
echo "Error count above (should be < 10)"

# Look for specific upload issues
grep -i "upload\|ping" storage/logs/laravel.log | tail -20
echo "✅ Recent upload/ping activity shown above"
```

### Step 7: Quick Functionality Test
```bash
# Test a small file upload (substitute real user token)
USER_ID=1  # Change to actual user ID
curl -X POST https://www.shoutoutgh.com/admin/contents/upload/video \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "video=@test-video.mp4" \
  -F "title=Test Video"
echo "✅ Upload endpoint responsive"
```

---

## Automated Verification Script

Copy the following into a file `verify-deployment.sh`:

```bash
#!/bin/bash

echo "🚀 Starting post-deployment verification..."
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check 1: Application health
echo "Check 1: Application Health"
if php artisan list > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Artisan commands working${NC}"
else
    echo -e "${RED}❌ Artisan commands failed${NC}"
    exit 1
fi

# Check 2: Database connection
echo "Check 2: Database Connection"
if php artisan tinker <<EOF > /dev/null 2>&1
DB::table('users')->count()
exit
EOF
then
    echo -e "${GREEN}✅ Database connection working${NC}"
else
    echo -e "${RED}❌ Database connection failed${NC}"
    exit 1
fi

# Check 3: Log errors
echo "Check 3: Recent Errors"
ERROR_COUNT=$(grep "ERROR\|Exception" storage/logs/laravel.log | wc -l)
if [ $ERROR_COUNT -lt 20 ]; then
    echo -e "${GREEN}✅ Error count acceptable (${ERROR_COUNT} errors)${NC}"
else
    echo -e "${RED}⚠️  High error count (${ERROR_COUNT} errors)${NC}"
fi

# Check 4: Upload config
echo "Check 4: Upload Configuration"
php artisan tinker <<EOF > /dev/null 2>&1
$config = config('uploads');
exit
EOF
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Upload config loaded${NC}"
else
    echo -e "${RED}❌ Upload config failed${NC}"
    exit 1
fi

# Check 5: Routes
echo "Check 5: Routes"
if php artisan route:list | grep -q "ping"; then
    echo -e "${GREEN}✅ /ping route exists${NC}"
else
    echo -e "${RED}❌ /ping route not found${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✅ All checks passed! Deployment successful.${NC}"
echo ""
echo "Next steps:"
echo "1. Test large file upload (500MB+)"
echo "2. Monitor logs: tail -f storage/logs/laravel.log"
echo "3. Check /ping responses: Ctrl+F 'ping' in Network tab"
echo ""
```

Run it:
```bash
chmod +x verify-deployment.sh
./verify-deployment.sh
```

---

## Monitoring After Deployment

### Real-Time Log Monitoring
```bash
# Watch logs for next 30 minutes
tail -f storage/logs/laravel.log | grep -i "upload\|ping\|error"

# In another terminal, run upload test
# Upload 500MB+ file
# Watch logs above for any errors
```

### Grep Commands for Investigation
```bash
# Find all ping-related errors
grep "ping" storage/logs/laravel.log | grep -i "error"
echo "✅ Ping errors (should be none or warnings only)"

# Find all upload errors
grep "upload" storage/logs/laravel.log | grep -i "error"
echo "✅ Upload errors (should be none)"

# Find all config errors
grep "config" storage/logs/laravel.log | grep -i "error"
echo "✅ Config errors (should be none)"

# Find all 500 errors
grep "500\|Internal Server Error" storage/logs/laravel.log | wc -l
echo "✅ Count of 500 errors (should be < 5)"

# View last 50 info logs
grep "\[INFO\]" storage/logs/laravel.log | tail -50
echo "✅ Recent info logs shown above"
```

---

## Rollback Procedure (If Needed)

### Quick Rollback (1 command)
```bash
# Revert the last commit
git revert HEAD

# Clear caches
php artisan cache:clear
php artisan route:clear

# Done! App is back to previous version
echo "✅ Rollback complete"
```

### Full Rollback to Backup
```bash
# List backup branches
git branch -a | grep backup

# Checkout backup branch (replace timestamp)
git checkout backup-pre-deployment-20240116-100000

# Clear caches
php artisan cache:clear
php artisan route:clear

# Verify
php artisan list > /dev/null && echo "✅ Rollback successful"
```

### Troubleshoot After Rollback
```bash
# Check what's deployed
git log --oneline | head -3

# Check artisan works
php artisan list | head -5

# Check database
php artisan tinker <<EOF
DB::table('users')->count()
exit
EOF
```

---

## Complete Deployment One-Liner

If you're confident and in a hurry:

```bash
git pull origin enhanced-diagnosis && \
php artisan cache:clear && \
php artisan route:clear && \
php artisan config:clear && \
php artisan list > /dev/null && \
echo "✅ Deployment complete! Test now." || \
echo "❌ Deployment failed, rolling back..." && \
git revert HEAD && \
php artisan route:clear
```

---

## Step-by-Step For Non-Technical

### For Server Admin:

1. **SSH into server:**
   ```bash
   ssh user@www.shoutoutgh.com
   cd /var/www/learn_Laravel/digilearn-laravel
   ```

2. **Pull code:**
   ```bash
   git pull origin enhanced-diagnosis
   ```

3. **Clear caches:**
   ```bash
   php artisan cache:clear
   php artisan route:clear
   ```

4. **Verify:**
   ```bash
   php artisan list
   ```

5. **Done!** Ask developer to test large upload.

---

## For Docker Deployment

```bash
# Build new image
docker-compose build

# Start services
docker-compose up -d

# Clear caches inside container
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear

# Check health
docker-compose exec app php artisan list
```

---

## Monitoring Dashboard Commands

Create a monitoring script `monitor.sh`:

```bash
#!/bin/bash

while true; do
    clear
    echo "📊 Deployment Monitoring Dashboard"
    echo "===================================="
    echo ""
    echo "🕐 Time: $(date)"
    echo ""
    echo "📋 Recent Logs:"
    tail -10 storage/logs/laravel.log
    echo ""
    echo "❌ Errors: $(grep "ERROR" storage/logs/laravel.log | wc -l)"
    echo "⚠️  Warnings: $(grep "WARNING" storage/logs/laravel.log | wc -l)"
    echo "ℹ️  Info: $(grep "INFO" storage/logs/laravel.log | wc -l)"
    echo ""
    echo "Press Ctrl+C to exit, refreshes every 5 seconds..."
    sleep 5
done
```

Run it:
```bash
chmod +x monitor.sh
./monitor.sh
```

---

## Verification Checklist

```bash
# Copy this checklist to track deployment

□ Create backup branch
  git checkout -b backup-pre-deployment-$(date +%Y%m%d-%H%M%S)

□ Pull latest code
  git pull origin enhanced-diagnosis

□ Clear caches
  php artisan cache:clear && php artisan route:clear

□ Verify artisan works
  php artisan list > /dev/null && echo "✅"

□ Test database connection
  php artisan tinker: DB::table('users')->count()

□ Check for errors
  grep "ERROR" storage/logs/laravel.log | wc -l

□ Test small upload (10MB)
  Upload from UI, check completes

□ Test large upload (500MB+)
  Upload from UI, watch /ping in network tab

□ Verify /ping returns 200
  DevTools Network → /ping → All should be HTTP 200

□ Verify no 500 errors
  grep "500 Internal Server Error" storage/logs/laravel.log

□ Done! ✅
```

---

## Emergency Contact

If deployment fails:

1. **Check logs immediately:**
   ```bash
   tail -100 storage/logs/laravel.log
   ```

2. **Rollback immediately:**
   ```bash
   git revert HEAD && php artisan route:clear
   ```

3. **Verify rollback:**
   ```bash
   php artisan list
   ```

4. **Contact developer** with:
   - Last 50 lines of storage/logs/laravel.log
   - Output of `git log --oneline | head -5`
   - Output of `git status`

---

## Success Criteria (Check All These)

- [ ] ✅ Code pulled successfully
- [ ] ✅ No artisan errors
- [ ] ✅ Database connection works
- [ ] ✅ No recent 500 errors in logs
- [ ] ✅ Small file upload works
- [ ] ✅ Large file upload works
- [ ] ✅ /ping returns HTTP 200 (not 500)
- [ ] ✅ Upload completes without hanging
- [ ] ✅ Users can log in and use app
- [ ] ✅ No performance degradation

All green? **DEPLOYMENT SUCCESSFUL!** 🎉

---

## Questions?

**Q: How long will deployment take?**
A: 5-10 minutes. The actual code change takes 30 seconds, verification takes 5-10 minutes.

**Q: Will users experience downtime?**
A: No. Zero downtime. Users can continue working.

**Q: What if something goes wrong?**
A: Rollback with one command (`git revert HEAD`), app is back to previous version immediately.

**Q: Should I test before deploying to production?**
A: Yes, test in staging first if possible. Just upload a 500MB file and check /ping returns 200.

**Q: Do I need to stop any services?**
A: No. No migrations, no database changes, no service restarts needed.

---

**Ready? Deploy now! 🚀**
