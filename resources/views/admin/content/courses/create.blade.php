@extends('layouts.admin')

@section('title', 'Create Course - Admin Dashboard')
@section('page-title', 'Create New Course')
@section('page-description', 'Create a complete course with videos, documents, and quizzes')

@section('content')
<div class="course-create-container">
    <form method="POST" action="{{ route('admin.content.courses.store') }}" enctype="multipart/form-data" class="course-form">
        @csrf

         Progress Steps 
        <div class="progress-steps">
            <div class="step step-active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Basic Info</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Content</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Review</div>
            </div>
        </div>

         Step 1: Basic Information 
        <div class="form-section" id="step-1">
            <div class="section-header">
                <h3 class="section-title">Basic Information</h3>
                <p class="section-description">Enter the fundamental details about your course</p>
            </div>

            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label for="title" class="form-label required">Course Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           class="form-input" placeholder="Enter course title">
                    @error('title')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                           class="form-input" placeholder="e.g., Mathematics, Science">
                    @error('subject')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="grade_level" class="form-label">Grade Level</label>
                    <select name="grade_level" id="grade_level" class="form-select">
                        <option value="">Select Grade Level</option>
                        @foreach($gradeLevels as $level)
                            <option value="{{ $level }}" {{ old('grade_level') == $level ? 'selected' : '' }}>{{ $level }}</option>
                        @endforeach
                    </select>
                    @error('grade_level')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="price" class="form-label">Price (GHS)</label>
                    <div class="input-with-icon">
                        <span class="input-icon">₵</span>
                        <input type="number" name="price" id="price" value="{{ old('price', 0) }}" min="0" step="0.01"
                               class="form-input" placeholder="0.00">
                    </div>
                    @error('price')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group form-group-full">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="4" class="form-textarea" 
                              placeholder="Describe what students will learn in this course">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group form-group-full">
                    <label for="thumbnail" class="form-label">Course Thumbnail</label>
                    <div class="file-upload-area" id="thumbnailUpload">
                        <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="file-input">
                        <div class="file-upload-content">
                            <div class="file-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-upload-text">
                                <span class="file-upload-title">Click to upload or drag and drop</span>
                                <span class="file-upload-subtitle">PNG, JPG, GIF up to 2MB</span>
                            </div>
                        </div>
                        <div class="file-preview" id="thumbnailPreview" style="display: none;">
                            <img id="thumbnailImage" src="/placeholder.svg" alt="Thumbnail preview">
                            <button type="button" class="file-remove" onclick="removeThumbnail()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @error('thumbnail')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="status" class="form-label required">Status</label>
                    <select name="status" id="status" required class="form-select">
                        <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                    @error('status')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="form-checkbox">
                        <label for="is_featured" class="checkbox-label">
                            <span class="checkbox-text">Mark as featured course</span>
                            <span class="checkbox-description">Featured courses appear prominently on the platform</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="nextStep(2)">
                    <span>Continue</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

         Step 2: Course Content 
        <div class="form-section" id="step-2" style="display: none;">
            <div class="section-header">
                <h3 class="section-title">Course Content</h3>
                <p class="section-description">Select videos, documents, and quizzes to include in this course</p>
            </div>

             Videos Section 
            <div class="content-section">
                <div class="content-header">
                    <div class="content-icon content-icon-video">
                        <i class="fas fa-video"></i>
                    </div>
                    <div class="content-info">
                        <h4 class="content-title">Videos <span class="required-indicator">*</span></h4>
                        <p class="content-description">Add video lessons to your course</p>
                    </div>
                    <button type="button" class="btn btn-outline" onclick="openVideoSelector()">
                        <i class="fas fa-plus"></i>
                        <span>Add Videos</span>
                    </button>
                </div>
                <div class="selected-content" id="selected-videos">
                    <div class="empty-content">
                        <i class="fas fa-video"></i>
                        <span>No videos selected</span>
                    </div>
                </div>
                <input type="hidden" name="videos" id="videos-input" value="{{ old('videos', '[]') }}">
                @error('videos')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

             Documents Section 
            <div class="content-section">
                <div class="content-header">
                    <div class="content-icon content-icon-document">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="content-info">
                        <h4 class="content-title">Documents <span class="optional-indicator">Optional</span></h4>
                        <p class="content-description">Add supporting documents and materials</p>
                    </div>
                    <button type="button" class="btn btn-outline" onclick="openDocumentSelector()">
                        <i class="fas fa-plus"></i>
                        <span>Add Documents</span>
                    </button>
                </div>
                <div class="selected-content" id="selected-documents">
                    <div class="empty-content">
                        <i class="fas fa-file-alt"></i>
                        <span>No documents selected</span>
                    </div>
                </div>
                <input type="hidden" name="documents" id="documents-input" value="{{ old('documents', '[]') }}">
                @error('documents')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

             Quizzes Section 
            <div class="content-section">
                <div class="content-header">
                    <div class="content-icon content-icon-quiz">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="content-info">
                        <h4 class="content-title">Quizzes <span class="optional-indicator">Optional</span></h4>
                        <p class="content-description">Add assessments and quizzes</p>
                    </div>
                    <button type="button" class="btn btn-outline" onclick="openQuizSelector()">
                        <i class="fas fa-plus"></i>
                        <span>Add Quizzes</span>
                    </button>
                </div>
                <div class="selected-content" id="selected-quizzes">
                    <div class="empty-content">
                        <i class="fas fa-question-circle"></i>
                        <span>No quizzes selected</span>
                    </div>
                </div>
                <input type="hidden" name="quizzes" id="quizzes-input" value="{{ old('quizzes', '[]') }}">
                @error('quizzes')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="prevStep(1)">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back</span>
                </button>
                <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                    <span>Continue</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

         Step 3: Review 
        <div class="form-section" id="step-3" style="display: none;">
            <div class="section-header">
                <h3 class="section-title">Review & Submit</h3>
                <p class="section-description">Review your course details before creating</p>
            </div>

            <div class="review-card">
                <div class="review-section">
                    <h4 class="review-title">Course Information</h4>
                    <div class="review-grid">
                        <div class="review-item">
                            <span class="review-label">Title:</span>
                            <span class="review-value" id="review-title">-</span>
                        </div>
                        <div class="review-item">
                            <span class="review-label">Subject:</span>
                            <span class="review-value" id="review-subject">-</span>
                        </div>
                        <div class="review-item">
                            <span class="review-label">Grade Level:</span>
                            <span class="review-value" id="review-grade">-</span>
                        </div>
                        <div class="review-item">
                            <span class="review-label">Price:</span>
                            <span class="review-value" id="review-price">-</span>
                        </div>
                        <div class="review-item">
                            <span class="review-label">Status:</span>
                            <span class="review-value" id="review-status">-</span>
                        </div>
                        <div class="review-item">
                            <span class="review-label">Featured:</span>
                            <span class="review-value" id="review-featured">-</span>
                        </div>
                    </div>
                </div>

                <div class="review-section">
                    <h4 class="review-title">Content Summary</h4>
                    <div class="content-summary">
                        <div class="summary-item">
                            <div class="summary-icon summary-icon-video">
                                <i class="fas fa-video"></i>
                            </div>
                            <div class="summary-content">
                                <span class="summary-count" id="review-videos-count">0</span>
                                <span class="summary-label">Videos</span>
                            </div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-icon summary-icon-document">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="summary-content">
                                <span class="summary-count" id="review-documents-count">0</span>
                                <span class="summary-label">Documents</span>
                            </div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-icon summary-icon-quiz">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div class="summary-content">
                                <span class="summary-count" id="review-quizzes-count">0</span>
                                <span class="summary-label">Quizzes</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="prevStep(2)">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back</span>
                </button>
                <a href="{{ route('admin.content.courses.index') }}" class="btn btn-outline">
                    <span>Cancel</span>
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i>
                    <span>Create Course</span>
                </button>
            </div>
        </div>
    </form>
</div>

 Content Selection Modals 
<div id="videoModal" class="modal-overlay">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 class="modal-title">Select Videos</h3>
            <button type="button" class="modal-close" onclick="closeVideoModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-search">
                <input type="text" placeholder="Search videos..." class="search-input" id="videoSearch">
            </div>
            <div class="content-list" id="videoList">
                <div class="loading-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Loading videos...</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeVideoModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="confirmVideoSelection()">Add Selected Videos</button>
        </div>
    </div>
</div>

<div id="documentModal" class="modal-overlay">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 class="modal-title">Select Documents</h3>
            <button type="button" class="modal-close" onclick="closeDocumentModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-search">
                <input type="text" placeholder="Search documents..." class="search-input" id="documentSearch">
            </div>
            <div class="content-list" id="documentList">
                <div class="loading-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Loading documents...</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDocumentModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="confirmDocumentSelection()">Add Selected Documents</button>
        </div>
    </div>
</div>

<div id="quizModal" class="modal-overlay">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 class="modal-title">Select Quizzes</h3>
            <button type="button" class="modal-close" onclick="closeQuizModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-search">
                <input type="text" placeholder="Search quizzes..." class="search-input" id="quizSearch">
            </div>
            <div class="content-list" id="quizList">
                <div class="loading-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Loading quizzes...</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeQuizModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="confirmQuizSelection()">Add Selected Quizzes</button>
        </div>
    </div>
</div>

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .course-create-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 2rem;
    }

    /* Progress Steps */
    .progress-steps {
        display: flex;
        justify-content: center;
        margin-bottom: 3rem;
        position: relative;
    }

    .progress-steps::before {
        content: '';
        position: absolute;
        top: 1.5rem;
        left: 25%;
        right: 25%;
        height: 2px;
        background: #e5e7eb;
        z-index: 1;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
        flex: 1;
        max-width: 200px;
    }

    .step-number {
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        background: #f3f4f6;
        color: #9ca3af;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }

    .step-active .step-number {
        background: #2677B8;
        color: #ffffff;
    }

    .step-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #6b7280;
        text-align: center;
    }

    .step-active .step-label {
        color: #2677B8;
        font-weight: 600;
    }

    /* Form Sections */
    .form-section {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .section-header {
        margin-bottom: 2rem;
        text-align: center;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.5rem;
    }

    .section-description {
        color: #6b7280;
        font-size: 1rem;
    }

    /* Form Grid */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .form-group-full {
        grid-column: 1 / -1;
    }

    /* Form Elements */
    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-label.required::after {
        content: ' *';
        color: #E11E2D;
    }

    .form-input,
    .form-select,
    .form-textarea {
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background: #ffffff;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #2677B8;
        box-shadow: 0 0 0 3px rgba(38, 119, 184, 0.1);
    }

    .input-with-icon {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-weight: 600;
    }

    .input-with-icon .form-input {
        padding-left: 2.5rem;
    }

    .form-error {
        color: #E11E2D;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    /* File Upload */
    .file-upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .file-upload-area:hover {
        border-color: #2677B8;
        background: #f8fafc;
    }

    .file-input {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }

    .file-upload-icon {
        font-size: 2rem;
        color: #9ca3af;
        margin-bottom: 1rem;
    }

    .file-upload-title {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.25rem;
    }

    .file-upload-subtitle {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .file-preview {
        position: relative;
        display: inline-block;
    }

    .file-preview img {
        max-width: 200px;
        max-height: 150px;
        border-radius: 8px;
        object-fit: cover;
    }

    .file-remove {
        position: absolute;
        top: -0.5rem;
        right: -0.5rem;
        width: 1.5rem;
        height: 1.5rem;
        background: #E11E2D;
        color: #ffffff;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
    }

    /* Checkbox */
    .checkbox-group {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .form-checkbox {
        width: 1.25rem;
        height: 1.25rem;
        margin-top: 0.125rem;
        flex-shrink: 0;
    }

    .checkbox-label {
        display: flex;
        flex-direction: column;
        cursor: pointer;
    }

    .checkbox-text {
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.25rem;
    }

    .checkbox-description {
        font-size: 0.875rem;
        color: #6b7280;
    }

    /* Content Sections */
    .content-section {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .content-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .content-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .content-icon-video {
        background: #dbeafe;
        color: #2677B8;
    }

    .content-icon-document {
        background: #d1fae5;
        color: #10b981;
    }

    .content-icon-quiz {
        background: #e0e7ff;
        color: #7c3aed;
    }

    .content-info {
        flex: 1;
    }

    .content-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.25rem;
    }

    .required-indicator {
        color: #E11E2D;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .optional-indicator {
        color: #6b7280;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .content-description {
        color: #6b7280;
        font-size: 0.875rem;
    }

    .selected-content {
        min-height: 100px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #ffffff;
        padding: 1rem;
    }

    .empty-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 80px;
        color: #9ca3af;
        font-size: 0.875rem;
    }

    .empty-content i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .selected-items {
        display: grid;
        gap: 0.75rem;
    }

    .selected-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: #f3f4f6;
        border-radius: 8px;
    }

    .selected-item-icon {
        width: 2rem;
        height: 2rem;
        background: #e5e7eb;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        flex-shrink: 0;
    }

    .selected-item-info {
        flex: 1;
        min-width: 0;
    }

    .selected-item-title {
        font-weight: 500;
        color: #111827;
        margin-bottom: 0.125rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .selected-item-meta {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .selected-item-remove {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 4px;
        transition: all 0.2s ease;
    }

    .selected-item-remove:hover {
        background: #e5e7eb;
        color: #6b7280;
    }

    /* Review Section */
    .review-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .review-section {
        margin-bottom: 2rem;
    }

    .review-section:last-child {
        margin-bottom: 0;
    }

    .review-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 1rem;
    }

    .review-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .review-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: #ffffff;
        border-radius: 8px;
    }

    .review-label {
        font-weight: 500;
        color: #6b7280;
    }

    .review-value {
        font-weight: 600;
        color: #111827;
    }

    .content-summary {
        display: flex;
        gap: 2rem;
        justify-content: center;
    }

    .summary-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .summary-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .summary-icon-video {
        background: #dbeafe;
        color: #2677B8;
    }

    .summary-icon-document {
        background: #d1fae5;
        color: #10b981;
    }

    .summary-icon-quiz {
        background: #e0e7ff;
        color: #7c3aed;
    }

    .summary-content {
        text-align: center;
    }

    .summary-count {
        display: block;
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
    }

    .summary-label {
        font-size: 0.875rem;
        color: #6b7280;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background: #2677B8;
        color: #ffffff;
    }

    .btn-primary:hover {
        background: #1e5a8a;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #6b7280;
        color: #ffffff;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    .btn-success {
        background: #10b981;
        color: #ffffff;
    }

    .btn-success:hover {
        background: #059669;
    }

    .btn-outline {
        background: #ffffff;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-outline:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 1rem;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: #ffffff;
        border-radius: 12px;
        max-width: 600px;
        width: 100%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }

    .modal-large {
        max-width: 800px;
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
    }

    .modal-close {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 4px;
        font-size: 1.25rem;
    }

    .modal-close:hover {
        background: #f3f4f6;
        color: #6b7280;
    }

    .modal-body {
        padding: 1.5rem;
        flex: 1;
        overflow-y: auto;
    }

    .modal-search {
        margin-bottom: 1rem;
    }

    .modal-search .search-input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;
    }

    .content-list {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .content-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .content-item:hover {
        background: #f9fafb;
    }

    .content-item:last-child {
        border-bottom: none;
    }

    .content-checkbox {
        flex-shrink: 0;
    }

    .content-item-info {
        flex: 1;
        min-width: 0;
    }

    .content-item-title {
        font-weight: 500;
        color: #111827;
        margin-bottom: 0.25rem;
    }

    .content-item-meta {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .loading-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        color: #6b7280;
    }

    .loading-state i {
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .modal-footer {
        padding: 1.5rem;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    /* Empty and Error States */
    .empty-state, .error-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        color: #6b7280;
        text-align: center;
    }

    .empty-state i, .error-state i {
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .error-state {
        color: #E11E2D;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .course-create-container {
            padding: 1rem;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .content-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .content-summary {
            flex-direction: column;
            gap: 1rem;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .progress-steps {
            flex-direction: column;
            gap: 1rem;
        }
        
        .progress-steps::before {
            display: none;
        }
    }
</style>

<script nonce="{{ request()->attributes->get('csp_nonce') }}">
let selectedVideos = [];
let selectedDocuments = [];
let selectedQuizzes = [];

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    // Load initial selections from form data
    const videosInput = document.getElementById('videos-input');
    const documentsInput = document.getElementById('documents-input');
    const quizzesInput = document.getElementById('quizzes-input');

    if (videosInput.value && videosInput.value !== '[]') {
        selectedVideos = JSON.parse(videosInput.value);
        renderSelectedVideos();
    }

    if (documentsInput.value && documentsInput.value !== '[]') {
        selectedDocuments = JSON.parse(documentsInput.value);
        renderSelectedDocuments();
    }

    if (quizzesInput.value && quizzesInput.value !== '[]') {
        selectedQuizzes = JSON.parse(quizzesInput.value);
        renderSelectedQuizzes();
    }

    // File upload handling
    const thumbnailInput = document.getElementById('thumbnail');
    thumbnailInput.addEventListener('change', handleThumbnailUpload);
});

// Step navigation
function nextStep(step) {
    // Validate current step
    if (step === 2 && !validateStep1()) return;
    if (step === 3 && !validateStep2()) return;

    // Hide current step
    document.querySelectorAll('.form-section').forEach(section => {
        section.style.display = 'none';
    });

    // Show target step
    document.getElementById(`step-${step}`).style.display = 'block';

    // Update progress
    document.querySelectorAll('.step').forEach(stepEl => {
        stepEl.classList.remove('step-active');
    });
    document.querySelector(`[data-step="${step}"]`).classList.add('step-active');

    // Update review if on step 3
    if (step === 3) {
        updateReview();
    }
}

function prevStep(step) {
    document.querySelectorAll('.form-section').forEach(section => {
        section.style.display = 'none';
    });
    document.getElementById(`step-${step}`).style.display = 'block';

    document.querySelectorAll('.step').forEach(stepEl => {
        stepEl.classList.remove('step-active');
    });
    document.querySelector(`[data-step="${step}"]`).classList.add('step-active');
}

// Validation
function validateStep1() {
    const title = document.getElementById('title').value.trim();
    const status = document.getElementById('status').value;

    if (!title) {
        alert('Please enter a course title');
        document.getElementById('title').focus();
        return false;
    }

    if (!status) {
        alert('Please select a status');
        document.getElementById('status').focus();
        return false;
    }

    return true;
}

function validateStep2() {
    if (selectedVideos.length === 0) {
        alert('Please select at least one video for the course');
        return false;
    }
    return true;
}

// File upload
function handleThumbnailUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('thumbnailPreview');
        const image = document.getElementById('thumbnailImage');
        const uploadContent = document.querySelector('.file-upload-content');

        image.src = e.target.result;
        preview.style.display = 'block';
        uploadContent.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function removeThumbnail() {
    const input = document.getElementById('thumbnail');
    const preview = document.getElementById('thumbnailPreview');
    const uploadContent = document.querySelector('.file-upload-content');

    input.value = '';
    preview.style.display = 'none';
    uploadContent.style.display = 'block';
}

// Video selection
function openVideoSelector() {
    const modal = document.getElementById('videoModal');
    const videoList = document.getElementById('videoList');

    // Show loading state
    videoList.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading videos...</span>
        </div>
    `;

    modal.classList.add('active');

    // Fetch videos from actual API endpoint
    fetch('/admin/content/videos?status=approved&per_page=50')
        .then(response => response.json())
        .then(data => {
            if (data.data && data.data.length > 0) {
                videoList.innerHTML = data.data.map(video => `
                    <div class="content-item">
                        <input type="checkbox" class="content-checkbox" value="${video.id}" ${selectedVideos.includes(video.id.toString()) ? 'checked' : ''}>
                        <div class="content-item-info">
                            <div class="content-item-title">${video.title}</div>
                            <div class="content-item-meta">${video.grade_level || 'N/A'} • ${video.duration_seconds ? Math.floor(video.duration_seconds / 60) + ' min' : 'N/A'}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                videoList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-video"></i>
                        <span>No approved videos found</span>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading videos:', error);
            videoList.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Failed to load videos. Please try again.</span>
                </div>
            `;
        });
}

function closeVideoModal() {
    document.getElementById('videoModal').classList.remove('active');
}

function confirmVideoSelection() {
    const checkboxes = document.querySelectorAll('#videoList .content-checkbox:checked');
    selectedVideos = Array.from(checkboxes).map(cb => cb.value);
    document.getElementById('videos-input').value = JSON.stringify(selectedVideos);
    renderSelectedVideos();
    closeVideoModal();
}

function renderSelectedVideos() {
    const container = document.getElementById('selected-videos');
    if (selectedVideos.length === 0) {
        container.innerHTML = `
            <div class="empty-content">
                <i class="fas fa-video"></i>
                <span>No videos selected</span>
            </div>
        `;
        return;
    }

    container.innerHTML = `
        <div class="selected-items">
            ${selectedVideos.map(id => `
                <div class="selected-item">
                    <div class="selected-item-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <div class="selected-item-info">
                        <div class="selected-item-title">Video ${id}</div>
                        <div class="selected-item-meta">Selected video</div>
                    </div>
                    <button type="button" class="selected-item-remove" onclick="removeVideo('${id}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `).join('')}
        </div>
    `;
}

function removeVideo(id) {
    selectedVideos = selectedVideos.filter(v => v !== id);
    document.getElementById('videos-input').value = JSON.stringify(selectedVideos);
    renderSelectedVideos();
}

// Document selection
function openDocumentSelector() {
    const modal = document.getElementById('documentModal');
    const documentList = document.getElementById('documentList');

    // Show loading state
    documentList.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading documents...</span>
        </div>
    `;

    modal.classList.add('active');

    // Fetch documents from actual API endpoint
    fetch('/admin/content/documents?per_page=50')
        .then(response => response.json())
        .then(data => {
            if (data.data && data.data.length > 0) {
                documentList.innerHTML = data.data.map(document => `
                    <div class="content-item">
                        <input type="checkbox" class="content-checkbox" value="${document.id}" ${selectedDocuments.includes(document.id.toString()) ? 'checked' : ''}>
                        <div class="content-item-info">
                            <div class="content-item-title">${document.title}</div>
                            <div class="content-item-meta">${document.grade_level || 'N/A'} • ${document.file_size ? (document.file_size / 1024 / 1024).toFixed(1) + ' MB' : 'N/A'}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                documentList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <span>No documents found</span>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading documents:', error);
            documentList.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Failed to load documents. Please try again.</span>
                </div>
            `;
        });
}

function closeDocumentModal() {
    document.getElementById('documentModal').classList.remove('active');
}

function confirmDocumentSelection() {
    const checkboxes = document.querySelectorAll('#documentList .content-checkbox:checked');
    selectedDocuments = Array.from(checkboxes).map(cb => cb.value);
    document.getElementById('documents-input').value = JSON.stringify(selectedDocuments);
    renderSelectedDocuments();
    closeDocumentModal();
}

function renderSelectedDocuments() {
    const container = document.getElementById('selected-documents');
    if (selectedDocuments.length === 0) {
        container.innerHTML = `
            <div class="empty-content">
                <i class="fas fa-file-alt"></i>
                <span>No documents selected</span>
            </div>
        `;
        return;
    }

    // For now, show IDs - in a real implementation, you'd fetch document details
    container.innerHTML = `
        <div class="selected-items">
            ${selectedDocuments.map(id => `
                <div class="selected-item">
                    <div class="selected-item-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="selected-item-info">
                        <div class="selected-item-title">Document ${id}</div>
                        <div class="selected-item-meta">Selected document</div>
                    </div>
                    <button type="button" class="selected-item-remove" onclick="removeDocument('${id}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `).join('')}
        </div>
    `;
}

function removeDocument(id) {
    selectedDocuments = selectedDocuments.filter(d => d !== id);
    document.getElementById('documents-input').value = JSON.stringify(selectedDocuments);
    renderSelectedDocuments();
}

// Quiz selection
function openQuizSelector() {
    const modal = document.getElementById('quizModal');
    const quizList = document.getElementById('quizList');

    // Show loading state
    quizList.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading quizzes...</span>
        </div>
    `;

    modal.classList.add('active');

    // Fetch quizzes from actual API endpoint
    fetch('/admin/content/quizzes?per_page=50')
        .then(response => response.json())
        .then(data => {
            if (data.data && data.data.length > 0) {
                quizList.innerHTML = data.data.map(quiz => `
                    <div class="content-item">
                        <input type="checkbox" class="content-checkbox" value="${quiz.id}" ${selectedQuizzes.includes(quiz.id.toString()) ? 'checked' : ''}>
                        <div class="content-item-info">
                            <div class="content-item-title">${quiz.title}</div>
                            <div class="content-item-meta">${quiz.grade_level || 'N/A'} • ${quiz.subject || 'N/A'}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                quizList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-question-circle"></i>
                        <span>No quizzes found</span>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading quizzes:', error);
            quizList.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Failed to load quizzes. Please try again.</span>
                </div>
            `;
        });
}

function closeQuizModal() {
    document.getElementById('quizModal').classList.remove('active');
}

function confirmQuizSelection() {
    const checkboxes = document.querySelectorAll('#quizList .content-checkbox:checked');
    selectedQuizzes = Array.from(checkboxes).map(cb => cb.value);
    document.getElementById('quizzes-input').value = JSON.stringify(selectedQuizzes);
    renderSelectedQuizzes();
    closeQuizModal();
}

function renderSelectedQuizzes() {
    const container = document.getElementById('selected-quizzes');
    if (selectedQuizzes.length === 0) {
        container.innerHTML = `
            <div class="empty-content">
                <i class="fas fa-question-circle"></i>
                <span>No quizzes selected</span>
            </div>
        `;
        return;
    }

    // For now, show IDs - in a real implementation, you'd fetch quiz details
    container.innerHTML = `
        <div class="selected-items">
            ${selectedQuizzes.map(id => `
                <div class="selected-item">
                    <div class="selected-item-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="selected-item-info">
                        <div class="selected-item-title">Quiz ${id}</div>
                        <div class="selected-item-meta">Selected quiz</div>
                    </div>
                    <button type="button" class="selected-item-remove" onclick="removeQuiz('${id}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `).join('')}
        </div>
    `;
}

function removeQuiz(id) {
    selectedQuizzes = selectedQuizzes.filter(q => q !== id);
    document.getElementById('quizzes-input').value = JSON.stringify(selectedQuizzes);
    renderSelectedQuizzes();
}

// Review update
function updateReview() {
    // Update basic info
    document.getElementById('review-title').textContent = document.getElementById('title').value || '-';
    document.getElementById('review-subject').textContent = document.getElementById('subject').value || '-';
    document.getElementById('review-grade').textContent = document.getElementById('grade_level').value || '-';
    document.getElementById('review-price').textContent = document.getElementById('price').value ? `₵${document.getElementById('price').value}` : '-';
    document.getElementById('review-status').textContent = document.getElementById('status').value || '-';
    document.getElementById('review-featured').textContent = document.getElementById('is_featured').checked ? 'Yes' : 'No';

    // Update content counts
    document.getElementById('review-videos-count').textContent = selectedVideos.length;
    document.getElementById('review-documents-count').textContent = selectedDocuments.length;
    document.getElementById('review-quizzes-count').textContent = selectedQuizzes.length;
}

// Close modals when clicking outside
document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});
</script>
@endsection
