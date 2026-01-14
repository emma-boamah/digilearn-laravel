<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChunkedVideoUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['admin', 'teacher', 'content_creator']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $uploadConfig = config('uploads');
        $chunkMaxSize = $uploadConfig['chunk']['size'] / 1024; // Convert bytes to KB
        
        return [
            'chunk' => 'required|file|max:' . $chunkMaxSize,
            'chunk_index' => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1',
            'upload_id' => 'required|string',
            'filename' => 'required|string',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        $uploadConfig = config('uploads');
        return [
            'chunk.required' => 'Chunk file is required.',
            'chunk.file' => 'Chunk must be a valid file.',
            'chunk.max' => 'Each chunk cannot exceed ' . $uploadConfig['chunk']['size'] / (1024 * 1024) . 'MB.',
            'chunk_index.required' => 'Chunk index is required.',
            'chunk_index.integer' => 'Chunk index must be an integer.',
            'total_chunks.required' => 'Total chunks count is required.',
            'total_chunks.integer' => 'Total chunks must be an integer.',
            'upload_id.required' => 'Upload ID is required.',
            'filename.required' => 'Filename is required.',
        ];
    }
}
