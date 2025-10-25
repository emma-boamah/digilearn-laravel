<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\VideoSourceService;

class StoreVideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Will be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'video_source' => 'required|in:local,youtube,vimeo,mux',
            'external_video_url' => 'required_if:video_source,youtube,vimeo,mux|nullable|url',
            'video_path' => 'required_if:video_source,local|nullable|file|mimes:mp4,mov,avi,mkv,webm,3gp,mpeg,ogg|max:102400', // 100MB max
            'thumbnail_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'grade_level' => 'nullable|string|max:50',
            'duration_seconds' => 'nullable|integer|min:1|max:36000', // Max 10 hours
            'is_featured' => 'boolean',
            'status' => 'in:pending,approved,rejected,processing'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Video title is required.',
            'title.max' => 'Video title cannot exceed 255 characters.',
            'video_source.required' => 'Please select a video source.',
            'video_source.in' => 'Invalid video source selected.',
            'external_video_url.required_if' => 'Video URL is required for external sources.',
            'external_video_url.url' => 'Please provide a valid video URL.',
            'video_path.required_if' => 'Video file is required for local uploads.',
            'video_path.file' => 'The uploaded file must be a valid video file.',
            'video_path.mimes' => 'Supported video formats: MP4, MOV, AVI, MKV, WebM, 3GP, MPEG, OGG.',
            'video_path.max' => 'Video file size cannot exceed 100MB.',
            'thumbnail_path.image' => 'Thumbnail must be a valid image file.',
            'thumbnail_path.mimes' => 'Supported thumbnail formats: JPEG, PNG, JPG, GIF.',
            'thumbnail_path.max' => 'Thumbnail file size cannot exceed 5MB.',
            'grade_level.max' => 'Grade level cannot exceed 50 characters.',
            'duration_seconds.integer' => 'Duration must be a valid number.',
            'duration_seconds.min' => 'Duration must be at least 1 second.',
            'duration_seconds.max' => 'Duration cannot exceed 10 hours.',
            'status.in' => 'Invalid status selected.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $videoSource = $this->input('video_source');
            $externalUrl = $this->input('external_video_url');

            // Validate external video URL based on source
            if (in_array($videoSource, ['youtube', 'vimeo', 'mux']) && $externalUrl) {
                $parsed = VideoSourceService::validateVideoUrl($externalUrl, $videoSource);

                if (!$parsed) {
                    $validator->errors()->add('external_video_url', 'The provided URL is not a valid ' . ucfirst($videoSource) . ' video URL.');
                } else {
                    // Store parsed data for later use
                    $this->merge([
                        'external_video_id' => $parsed['video_id'],
                        'parsed_embed_url' => $parsed['embed_url'],
                    ]);
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Set default values
        if (!$this->has('is_featured')) {
            $this->merge(['is_featured' => false]);
        }

        if (!$this->has('status')) {
            $this->merge(['status' => 'pending']);
        }

        // If video_source is not provided but external_video_url is, try to detect it
        if (!$this->has('video_source') && $this->has('external_video_url')) {
            $parsed = VideoSourceService::parseVideoUrl($this->input('external_video_url'));
            if ($parsed) {
                $this->merge(['video_source' => $parsed['source']]);
            }
        }
    }
}
