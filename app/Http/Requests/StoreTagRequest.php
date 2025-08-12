<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
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
            'name' => 'required|string|max:100|unique:tags,name',
            'slug' => 'nullable|string|max:100|unique:tags,slug',
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
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'tên tag',
            'slug' => 'slug',
            'color' => 'màu sắc',
            'description' => 'mô tả',
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

        // Set default color if not provided
        if (!$this->color) {
            $colors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'];
            $this->merge(['color' => $colors[array_rand($colors)]]);
        }

        // Clean and format name
        if ($this->name) {
            $this->merge([
                'name' => trim($this->name)
            ]);
        }
    }
}
