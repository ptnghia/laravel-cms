<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTagRequest extends FormRequest
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
        $tagId = $this->route('tag')->id ?? null;

        return [
            'name' => 'required|string|max:100|unique:tags,name,' . $tagId,
            'slug' => 'nullable|string|max:100|unique:tags,slug,' . $tagId,
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên tag là bắt buộc.',
            'name.max' => 'Tên tag không được vượt quá 100 ký tự.',
            'name.unique' => 'Tag này đã tồn tại.',
            'slug.unique' => 'Slug này đã được sử dụng.',
            'color.regex' => 'Màu sắc phải có định dạng hex (#RRGGBB).',
            'description.max' => 'Mô tả không được vượt quá 500 ký tự.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug if not provided
        if (!$this->slug && $this->name) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->name)
            ]);
        }

        // Clean and format name
        if ($this->name) {
            $this->merge([
                'name' => trim($this->name)
            ]);
        }
    }
}
