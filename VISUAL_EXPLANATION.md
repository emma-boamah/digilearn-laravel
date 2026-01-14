# 🎯 Upload Hanging Issue - Visual Explanation

## The Problem

```
USER PERSPECTIVE:
┌─────────────────────────────────────────────┐
│ Click "Finish" button                       │
│ ↓                                           │
│ Progress modal appears ✓                    │
│ ↓                                           │
│ Network tab shows:                          │
│   POST /admin/contents/upload/video         │
│   Status: (pending) 0.0 kB                  │
│ ↓                                           │
│ Wait... wait... wait...                     │
│ ↓                                           │
│ 60 seconds later → Connection times out    │
│ Upload hangs indefinitely 💀                │
└─────────────────────────────────────────────┘
```

## Root Causes

### Cause #1: PHP Socket Timeout is Too Short
```
Timeline of a Large File Upload:

Time 0s:    Browser sends 1st chunk (10MB)
            ↓
            Server: "Got it, storing to disk..."

Time 15s:   Browser sends 2nd chunk (10MB)  
            ↓
            Server: "Got it, storing..."

Time 30s:   Browser sends 3rd chunk (10MB)
            ↓
            Server: "Got it, storing..."

Time 45s:   Server is processing, no data coming in
            Browser waiting for response
            
Time 60s:   ⚠️  PHP FPM TIMEOUT!
            Socket closes because no data for 60 seconds
            
Time 61s:   Browser still waiting for response
            Server connection already closed
            → INFINITE HANG
```

**Problem**: `default_socket_timeout = 60` seconds  
**Solution**: Change to `default_socket_timeout = 3600` (1 hour)

---

### Cause #2: Wrong Storage Path

```
WHAT THE CODE WAS DOING:

uploadVideoChunk() called
    ↓
    Try to create: storage/app/temp_chunks/upload_123/
    ↓
    Directory structure on disk:
    /storage/app/
    ├── private/
    └── public/
        └── temp_videos/  ← File expected here
    
    ❌ But code looking for: storage/app/temp_chunks/
    ❌ Path doesn't match where files are stored!
    
    Result: Files not found, upload fails
```

**Problem**: Mismatched storage paths  
**Solution**: 
- Use consistent paths: `storage/app/public/temp_chunks/`
- Use consistent paths: `storage/app/public/temp_videos/`

---

### Cause #3: Missing Directories

```
DIRECTORY STRUCTURE PROBLEM:

/var/www/digilearn-laravel/storage/app/public/
├── avatars/
└── temp_videos/
    └── [empty]

❌ Missing: temp_chunks/

When code tries to create temp files:
    mkdir(storage/app/public/temp_chunks/...)
    ↓
    ✓ Directory created on demand

But if permissions wrong or disk full:
    ✗ mkdir fails
    ✗ Files can't be stored
    ✗ Upload fails
```

**Problem**: Directories didn't exist beforehand  
**Solution**: Pre-create directories with proper permissions

---

## The Fix

### Part 1: Fix PHP Socket Timeout

```bash
# Before
default_socket_timeout = 60

# After  
default_socket_timeout = 3600  # 1 hour
```

**Impact**: Large uploads won't timeout mid-transfer

---

### Part 2: Fix Storage Paths

```php
// Before (WRONG)
$tempDir = storage_path('app/temp_chunks/' . $uploadId);

// After (CORRECT)
$tempChunksDir = storage_path('app/public/temp_chunks/' . $uploadId);
```

**Impact**: Chunks stored in correct location, found during reassembly

---

### Part 3: Create Directories

```bash
mkdir -p /var/www/digilearn-laravel/storage/app/public/temp_chunks
mkdir -p /var/www/digilearn-laravel/storage/app/public/temp_videos
chown -R www-data:www-data /var/www/digilearn-laravel/storage/app/public/temp_*
chmod -R 755 /var/www/digilearn-laravel/storage/app/public/temp_*
```

**Impact**: Directories exist and have proper permissions

---

## After Fix: Expected Flow

```
SUCCESSFUL LARGE FILE UPLOAD (After Fix):

┌────────────────────────────────────────────┐
│ User clicks "Finish" with 1GB video file   │
└────────────────────────────────────────────┘
            ↓
┌────────────────────────────────────────────┐
│ JavaScript detects file > 500MB            │
│ → Triggers uploadVideoInChunksHybrid()     │
└────────────────────────────────────────────┘
            ↓
┌────────────────────────────────────────────┐
│ Progress: 5% - Preparing video data...     │
│ Network: No requests yet                   │
└────────────────────────────────────────────┘
            ↓
┌────────────────────────────────────────────┐
│ Progress: 10% - Uploading chunk 1/100      │
│ Network: POST chunk upload (10MB) ✓        │
│ Server: Stores to temp_chunks/chunk_0     │
└────────────────────────────────────────────┘
            ↓
┌────────────────────────────────────────────┐
│ Progress: 15% - Uploading chunk 2/100      │
│ Network: POST chunk upload (10MB) ✓        │
│ Server: Stores to temp_chunks/chunk_1     │
└────────────────────────────────────────────┘
            ↓
        ... repeat for all 100 chunks ...
            ↓
┌────────────────────────────────────────────┐
│ Progress: 95% - Uploading chunk 100/100    │
│ Network: All chunks received ✓              │
│ Server: Begins reassembly                  │
└────────────────────────────────────────────┘
            ↓
┌────────────────────────────────────────────┐
│ Progress: 95-100% - Processing video...    │
│ Server: Combines all chunks into 1GB file  │
│ Server: Stores to temp_videos/upload_xxx   │
│ Socket timeout: NO PROBLEM (3600s available)
└────────────────────────────────────────────┘
            ↓
┌────────────────────────────────────────────┐
│ Progress: 100% - Video uploaded!           │
│ Network: Final metadata POST ✓              │
│ Server: Returns video_id to JavaScript     │
└────────────────────────────────────────────┘
            ↓
┌────────────────────────────────────────────┐
│ Database: Video record created             │
│ Storage: File saved and accessible         │
│ User: Sees "Upload completed!"             │
└────────────────────────────────────────────┘
```

---

## Configuration Summary

### Before Fix
```
❌ PHP socket timeout: 60 seconds (TOO SHORT)
❌ Chunk storage path: app/temp_chunks (WRONG)
❌ Video storage path: app/temp_videos (WRONG)
❌ Directories: Missing/Created on demand (RISKY)
```

### After Fix
```
✅ PHP socket timeout: 3600 seconds (1 hour)
✅ Chunk storage path: app/public/temp_chunks (CORRECT)
✅ Video storage path: app/public/temp_videos (CORRECT)
✅ Directories: Pre-created with proper permissions (SAFE)
```

---

## Server Configuration Status

| Setting | Value | Status |
|---------|-------|--------|
| Nginx client_max_body_size | 32G | ✅ OK |
| Nginx client_body_timeout | 3600s | ✅ OK |
| Nginx fastcgi_read_timeout | 3600s | ✅ OK |
| PHP post_max_size | 32G | ✅ OK |
| PHP upload_max_filesize | 32G | ✅ OK |
| PHP max_execution_time | 0 (unlimited) | ✅ OK |
| PHP default_socket_timeout | 60s | ❌ NEEDS FIX → 3600s |
| Disk space /var | 107GB free | ✅ OK |

---

## Key Insights

1. **The "(pending)" status**: Browser IS sending the request, server IS receiving it, but server is not RESPONDING
2. **60-second limit**: Exact timeout matches PHP's `default_socket_timeout` setting
3. **Storage paths**: Code was looking for files in wrong directory structure
4. **Infrastructure**: Your Nginx and PHP-FPM are already configured for large uploads, just needed adjustments

---

## Next Steps

1. **Immediate**: Fix PHP socket timeout on production (5 minutes)
2. **Quick**: Create storage directories (2 minutes)
3. **Deploy**: Push code changes (5 minutes)
4. **Test**: Try uploading files to verify fix (10 minutes)

**Total time to fix: ~22 minutes** ⏱️

After this, uploads will work smoothly! 🎉
