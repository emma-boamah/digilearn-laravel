# Upload System Architecture - Production Fix

## Problem Timeline

```
┌─────────────────────────────────────────────────────────────┐
│ USER ACTION: Upload 2GB Video on Production                │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │  Browser Request (Entire 2GB file)   │
        │  POST /contents/upload/video          │
        │  Content-Length: 2000000000 bytes    │
        └───────────────────────────────────────┘
                            │
                            ▼
        ╔═══════════════════════════════════════╗
        ║  NGINX (HTTP Server Layer)            ║
        ║  ❌ client_max_body_size = 1MB        ║
        ║  (DEFAULT - TOO SMALL)                ║
        ╚═══════════════════════════════════════╝
                            │
                ┌───────────┴───────────┐
                │                       │
        ❌ REQUEST TOO LARGE           (doesn't reach here)
        HTTP 413 Error                  │
        (STOPS HERE)                    ▼
                                   Laravel Code
```

## Solution Layers

```
┌──────────────────────────────────────────────────────────────┐
│ PRODUCTION SOLUTION (3 Layers)                               │
└──────────────────────────────────────────────────────────────┘

┌─ Layer 1: SERVER CONFIGURATION ────────────────────────────┐
│                                                              │
│  /etc/nginx/nginx.conf                                      │
│  ────────────────────────────────────────────────────────  │
│  http {                                                      │
│    client_max_body_size 32G;          ← FIX #1: Allow 32GB │
│    client_body_buffer_size 128M;                            │
│    proxy_buffering on;                                      │
│    proxy_buffer_size 128M;                                  │
│    proxy_buffers 4 256M;                                    │
│    ...                                                       │
│  }                                                           │
│                                                              │
│  Status: ⏳ NEEDS TO BE APPLIED ON PRODUCTION              │
└──────────────────────────────────────────────────────────────┘

┌─ Layer 2: LARAVEL CODE ────────────────────────────────────┐
│                                                              │
│  config/uploads.php                                         │
│  ────────────────────────────────────────────────────────  │
│  return [                                                    │
│    'max_file_size' => 34359738368,    ← FIX #2: 32GB limit │
│    'video' => [                                             │
│      'max_size' => 34359738368,                             │
│      'mimes' => [mp4, mov, avi, ...],                       │
│    ],                                                        │
│  ];                                                          │
│                                                              │
│  Status: ✅ CREATED                                         │
└──────────────────────────────────────────────────────────────┘

┌─ Layer 3: REQUEST HANDLING ────────────────────────────────┐
│                                                              │
│  Middleware/HandleJsonRequestErrors.php                     │
│  ────────────────────────────────────────────────────────  │
│  Ensures validation errors return JSON instead of HTML      │
│                                                              │
│  Status: ✅ CREATED & REGISTERED                            │
└──────────────────────────────────────────────────────────────┘
```

## After Fix - Upload Flow

```
┌─────────────────────────────────────────────────────────────┐
│ USER ACTION: Upload 2GB Video (After Fix)                  │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │  Browser/JavaScript                   │
        │  Splits file into 10MB chunks         │
        │  Sends chunks sequentially            │
        └───────────────────────────────────────┘
                            │
        ┌───────────────────┴─────────────────┐
        │                                     │
        ▼                                     ▼
    CHUNK 1 (10MB)                        CHUNK 2 (10MB)
    POST /contents/upload/video-chunk    POST /contents/upload/video-chunk
        │                                     │
        ▼                                     ▼
    ╔═══════════════════════════════╗   ╔═══════════════════════════════╗
    ║  NGINX                        ║   ║  NGINX                        ║
    ║  ✅ client_max_body_size=32G  ║   ║  ✅ client_max_body_size=32G  ║
    ║  Size: 10MB < 32G ✓           ║   ║  Size: 10MB < 32G ✓           ║
    ║  PASSES                       ║   ║  PASSES                       ║
    ╚═══════════════════════════════╝   ╚═══════════════════════════════╝
        │                                     │
        ▼                                     ▼
    ┌───────────────────────────────────────────────────┐
    │  Laravel ChunkedVideoUploadRequest                 │
    │  ✅ Validates: max:10485760 (10MB chunk)          │
    │  ✅ Returns: JSON response                        │
    │  ✅ Stores: temp_chunks/{uploadId}/chunk_0       │
    │  ✅ Stores: temp_chunks/{uploadId}/chunk_1       │
    └───────────────────────────────────────────────────┘
        │
        │ (repeat for chunks 3-200...)
        │
        ▼
    ┌───────────────────────────────────────────────────┐
    │  uploadVideoChunk() Controller                     │
    │                                                   │
    │  if (all_chunks_received) {                       │
    │    - Open final file                             │
    │    - Write chunks 0-200 sequentially             │
    │    - Delete temp chunk files                     │
    │    - Return video_id                             │
    │  }                                                │
    └───────────────────────────────────────────────────┘
        │
        ▼
    ✅ SUCCESS - video_id available
    ✅ Documents can now upload with video_id
    ✅ Quiz can now upload with video_id
```

## Error Response Flow

```
BEFORE (Old Code)
─────────────────
POST /contents/upload/documents
  Content: [document file]
  video_id: (empty or wrong)
        │
        ▼
  Laravel Validation
  ❌ Fails (missing video_id or size > 20MB)
        │
        ▼
  Exception Thrown
        │
        ▼
  Browser Receives: HTML Error Page
  JavaScript Tries: JSON.parse(htmlString)
  ❌ Error: "Unexpected token '<'"
        │
        ▼
  User Sees: "Video upload failed: ... is not valid JSON"

AFTER (Fixed Code)
──────────────────
POST /contents/upload/documents
  Content: [document file]
  video_id: 12345
        │
        ▼
  HandleJsonRequestErrors Middleware
  ✅ Forces Accept: application/json
        │
        ▼
  Laravel Validation
  ❌ Fails (size > 32GB or invalid MIME)
        │
        ▼
  Exception Thrown
        │
        ▼
  Browser Receives: JSON Error Response
  {
    "success": false,
    "message": "Document file cannot exceed 32GB."
  }
  JavaScript Parses: JSON object
  ✅ Success - proper error message displayed
```

## Configuration Locations

```
NGINX LAYER
───────────
/etc/nginx/nginx.conf
    http {
        client_max_body_size 32G;  ← MUST BE HERE
        ...
    }
    
├─ Test: sudo nginx -t
└─ Apply: sudo systemctl restart nginx


PHP LAYER (Already Correct)
────────────────────────────
/etc/php/8.3/fpm/php.ini (or your version)
    post_max_size = 32G
    upload_max_filesize = 32G
    memory_limit = 512M
    
├─ Check: php -i | grep -i "upload"
└─ Status: ✅ ALREADY CONFIGURED


LARAVEL LAYER
──────────────
/config/uploads.php (NEW)
    return [
        'max_file_size' => 34359738368,
        'video' => [...],
        'document' => [...],
        ...
    ];
    
├─ Loaded: config('uploads')
├─ Overridable: .env variables
└─ Status: ✅ CREATED


MIDDLEWARE LAYER
────────────────
/app/Http/Middleware/HandleJsonRequestErrors.php (NEW)
    Ensures all responses are JSON for AJAX requests
    
├─ Registered: bootstrap/app.php
├─ Applied To: All requests
└─ Status: ✅ CREATED
```

## Request Flow (Detailed)

```
SMALL FILE (< 500MB) - Direct Upload
────────────────────────────────────
  Browser: [300MB video file]
             │
             ▼
  One POST request: /contents/upload/video
             │
             ▼
  Nginx: Size 300MB < 32G ✓
             │
             ▼
  Laravel: Validation from config/uploads.php
           - Checks: mimes = [mp4, mov, ...] ✓
           - Checks: max = 34359738368 ✓
             │
             ▼
  Store to: storage/videos/{filename}
             │
             ▼
  Response: { success: true, video_id: 123 }
             │
             ▼
  Frontend: Upload documents with video_id: 123 ✓


LARGE FILE (> 1GB) - Chunked Upload
──────────────────────────────────
  Browser: [4GB video file]
             │
             ▼
  Split into 10MB chunks (400 chunks)
             │
             ├─ Chunk 0 (10MB)
             ├─ Chunk 1 (10MB)
             ├─ Chunk 2 (10MB)
             ...
             └─ Chunk 399 (10MB)
             │
             ▼
  Sequential POSTs: /contents/upload/video-chunk
  Each with: { chunk, chunk_index, total_chunks, upload_id }
             │
        ┌────┴────┬────────┬─────────────────┐
        │          │        │                 │
        ▼          ▼        ▼                 ▼
      CHUNK 0   CHUNK 1   CHUNK 2   ...   CHUNK 399
        │          │        │                 │
        ▼          ▼        ▼                 ▼
      Nginx ✓   Nginx ✓   Nginx ✓   ...   Nginx ✓
      Store    Store    Store            Store
             │
             ▼
  After Chunk 399: Server Reassembles
  - Read all 400 chunks from temp_chunks/{uploadId}/
  - Write to single file: temp_videos/{uploadId}_filename.mp4
  - Delete all temp chunks
  - Return video_id: 123
             │
             ▼
  Response: { success: true, data: { completed: true, ... } }
             │
             ▼
  Frontend: Upload documents with video_id: 123 ✓
```

## Size Reference Table

```
┌─────────────────────┬──────────────────┬────────────────────┐
│ File Size           │ Upload Method    │ PHP/Nginx Issue?   │
├─────────────────────┼──────────────────┼────────────────────┤
│ 50 MB               │ Direct           │ ✅ No issue        │
│ 500 MB              │ Direct           │ ✅ No issue        │
│ 1 GB                │ Direct           │ ❌ May 413 error   │
│ 4 GB                │ Direct           │ ❌ 413 error       │
│ 32 GB               │ Direct           │ ❌ 413 error       │
├─────────────────────┼──────────────────┼────────────────────┤
│ 50 MB               │ Chunked (10MB)   │ ✅ No issue        │
│ 500 MB              │ Chunked (10MB)   │ ✅ No issue        │
│ 1 GB                │ Chunked (10MB)   │ ✅ No issue        │
│ 4 GB                │ Chunked (10MB)   │ ✅ No issue        │
│ 32 GB               │ Chunked (10MB)   │ ✅ No issue        │
└─────────────────────┴──────────────────┴────────────────────┘

With Nginx config:
  client_max_body_size 32G
  client_body_buffer_size 128M
  
Direct uploads can handle: 32GB (if server allows)
Chunked uploads always work: up to 32GB (via multiple requests)
```

## Status Dashboard

```
╔════════════════════════════════════════════════════════════╗
║                    PRODUCTION READINESS                    ║
╠════════════════════════════════════════════════════════════╣
║                                                            ║
║  Laravel Code Changes              ✅ COMPLETE            ║
║  ├─ Config file created            ✅                     ║
║  ├─ Validation rules updated       ✅                     ║
║  ├─ JSON middleware added          ✅                     ║
║  ├─ Chunked upload controller      ✅                     ║
║  └─ Routes configured              ✅                     ║
║                                                            ║
║  Server Configuration              ⏳ PENDING             ║
║  ├─ Nginx client_max_body_size     ⏳ NOT YET             ║
║  ├─ Nginx timeouts                 ⏳ NOT YET             ║
║  ├─ Nginx buffering                ⏳ NOT YET             ║
║  ├─ Temp directories created       ⏳ NOT YET             ║
║  └─ Cache cleared                  ⏳ NOT YET             ║
║                                                            ║
║  Documentation                     ✅ COMPLETE            ║
║  ├─ Production guide               ✅                     ║
║  ├─ Deployment checklist           ✅                     ║
║  ├─ Troubleshooting guide          ✅                     ║
║  └─ Architecture diagram           ✅                     ║
║                                                            ║
║  READY FOR DEPLOYMENT: YES (after Nginx config)           ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝
```

---

**Generated**: January 2026
**Environment**: Nginx + PHP-FPM + Laravel 11
**Max Upload**: 32GB (configurable)
