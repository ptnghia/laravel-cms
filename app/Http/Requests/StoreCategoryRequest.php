<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole('super_admin') ||
            $this->user()->hasRole('admin') ||
            $this->user()->hasRole('editor')
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
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'seo_meta' => 'nullable|array',
            'seo_meta.title' => 'nullable|string|max:60',
            'seo_meta.description' => 'nullable|string|max:160',
            'seo_meta.keywords' => 'nullable|string|max:255',
            'seo_meta.canonical_url' => 'nullable|url',
            'seo_meta.og_title' => 'nullable|string|max:60',
            'seo_meta.og_description' => 'nullable|string|max:160',
            'seo_meta.og_image' => 'nullable|url',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên danh mục là bắt buộc.',
            'name.max' => 'Tên danh mục không được vượt quá 255 ký tự.',
            'slug.unique' => 'Slug này đã được sử dụng.',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự.',
            'image.image' => 'File phải là hình ảnh.',
            'image.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif, webp.',
            'image.max' => 'Hình ảnh không được vượt quá 2MB.',
            'parent_id.exists' => 'Danh mục cha không tồn tại.',
            'sort_order.min' => 'Thứ tự sắp xếp phải lớn hơn hoặc bằng 0.',
            'seo_meta.title.max' => 'SEO title không được vượt quá 60 ký tự.',
            'seo_meta.description.max' => 'SEO description không được vượt quá 160 ký tự.',
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

        // Set default sort_order if not provided
        if (!$this->sort_order) {
            $this->merge(['sort_order' => 0]);
        }

        // Set default is_active if not provided
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }
}
