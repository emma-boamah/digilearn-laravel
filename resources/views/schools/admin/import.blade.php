@extends('schools.admin.layout')

@section('title', 'Bulk Import Users')

@section('styles')
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        .import-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 32px;
            max-width: 700px;
            margin-bottom: 24px;
        }

        .import-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .import-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .upload-area {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            background: var(--bg);
            transition: all 0.2s ease;
            margin-bottom: 20px;
        }

        .upload-area:hover,
        .upload-area.drag-over {
            border-color: var(--primary);
            background: rgba(37, 99, 235, 0.05);
        }

        .upload-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .upload-text {
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .upload-subtext {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .file-input {
            display: none;
        }

        .info-box {
            background: rgba(37, 99, 235, 0.05);
            border: 1px solid rgba(37, 99, 235, 0.1);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .info-title {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--primary);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-list {
            margin: 0;
            padding-left: 20px;
            font-size: 0.85rem;
            color: var(--text);
            line-height: 1.6;
        }

        .info-list li {
            margin-bottom: 4px;
        }

        .info-list code {
            background: var(--bg-card);
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid var(--border);
            font-family: monospace;
            font-size: 0.8rem;
        }

        #file-name-display {
            font-weight: 600;
            color: var(--primary);
            margin-top: 10px;
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="import-card">
        <h2 class="import-title">Bulk Import via CSV</h2>
        <p class="import-desc">Quickly add multiple students and teachers to your school by uploading a CSV file.</p>

        <div class="info-box">
            <div class="info-title">
                <i class="fas fa-info-circle"></i> Instructions
            </div>
            <ul class="info-list">
                <li>Download the <a href="{{ route('school.admin.users.import.template') }}"
                        style="color: var(--primary); font-weight: 600; text-decoration: none;">CSV Template</a>.</li>
                <li>Fill in the rows. The first row (headers) must remain exactly: <code>name, email, role, password</code>.
                </li>
                <li>The <code>role</code> column must be exactly either <code>student</code> or <code>teacher</code>.</li>
                <li>Passwords should be temporary (at least 8 characters). Users can change them later.</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('school.admin.users.import.submit') }}" enctype="multipart/form-data">
            @csrf

            <label class="upload-area" id="drop-zone">
                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <div class="upload-text">Click to browse or drag a CSV file here</div>
                <div class="upload-subtext">Max file size: 2MB</div>

                <input type="file" name="csv_file" id="csv_file" class="file-input" accept=".csv, .txt" required>
                <div class="sa-btn sa-btn-outline sa-btn-sm" style="pointer-events: none;">Select File</div>
                <div id="file-name-display"></div>
            </label>

            @error('csv_file')
                <div style="color: #dc2626; font-size: 0.85rem; margin-top: -10px; margin-bottom: 20px;">
                    {{ $message }}
                </div>
            @enderror

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="sa-btn sa-btn-primary" id="submit-btn" disabled>
                    <i class="fas fa-file-import"></i> Start Import
                </button>
                <a href="{{ route('school.admin.users') }}" class="sa-btn sa-btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('csv_file');
            const fileNameDisplay = document.getElementById('file-name-display');
            const submitBtn = document.getElementById('submit-btn');
            const dropZone = document.getElementById('drop-zone');

            fileInput.addEventListener('change', function (e) {
                if (this.files && this.files[0]) {
                    fileNameDisplay.textContent = 'Selected: ' + this.files[0].name;
                    fileNameDisplay.style.display = 'block';
                    submitBtn.disabled = false;
                } else {
                    fileNameDisplay.style.display = 'none';
                    submitBtn.disabled = true;
                }
            });

            // Drag and Drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'), false);
            });

            dropZone.addEventListener('drop', function (e) {
                let dt = e.dataTransfer;
                let files = dt.files;

                if (files.length > 0) {
                    fileInput.files = files;
                    // Trigger change event manually
                    const event = new Event('change');
                    fileInput.dispatchEvent(event);
                }
            }, false);
        });
    </script>
@endsection