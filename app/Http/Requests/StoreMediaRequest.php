<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole('super_admin') ||
            $this->user()->hasRole('admin') ||
            $this->user()->hasRole('editor') ||
            $this->user()->hasRole('author')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'files' => 'required|array|min:1|max:10',
            'files.*' => 'required|file|max:10240', // 10MB max per file
            'folder_id' => 'nullable|exists:media_folders,id',
            'alt_text' => 'nullable|array',
            'alt_text.*' => 'nullable|string|max:255',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'files.required' => 'Vui lòng chọn ít nhất một file để upload.',
            'files.array' => 'Dữ liệu files không hợp lệ.',
            'files.min' => 'Vui lòng chọn ít nhất một file.',
            'files.max' => 'Chỉ được upload tối đa 10 files cùng lúc.',
            'files.*.required' => 'File không được để trống.',
            'files.*.file' => 'Dữ liệu phải là file.',
            'files.*.max' => 'File không được vượt quá 10MB.',
            'folder_id.exists' => 'Thư mục không tồn tại.',
            'alt_text.*.max' => 'Alt text không được vượt quá 255 ký tự.',
            'description.*.max' => 'Mô tả không được vượt quá 1000 ký tự.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'files' => 'files',
            'folder_id' => 'thư mục',
            'alt_text' => 'alt text',
            'description' => 'mô tả',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate file types
            if ($this->hasFile('files')) {
                foreach ($this->file('files') as $index => $file) {
                    if ($file && $file->isValid()) {
                        $mimeType = $file->getMimeType();
                        $allowedTypes = [
                            // Images
                            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
                            // Documents
                            'application/pdf', 'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/plain', 'text/csv',
                            // Videos
                            'video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo',
                            // Audio
                            'audio/mpeg', 'audio/wav', 'audio/ogg',
                            // Archives
                            'application/zip', 'application/x-rar-compressed',
                        ];

                        if (!in_array($mimeType, $allowedTypes)) {
                            $validator->errors()->add(
                                "files.{$index}",
                                'Loại file không được hỗ trợ: ' . $mimeType
                            );
                        }
                    }
                }
            }
        });
    }
}
