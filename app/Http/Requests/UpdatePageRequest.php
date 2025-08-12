<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $page = $this->route('page');

        if (!$user || !$page) {
            return false;
        }

        // Super admin and admin can edit any page
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return true;
        }

        // Editor can edit any page
        if ($user->hasRole('editor')) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pageId = $this->route('page')->id ?? null;

        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . $pageId,
            'content' => 'required|string',
            'template' => 'nullable|string|max:100',
            'page_builder_data' => 'nullable|array',
            'status' => 'required|in:draft,published,private',
            'published_at' => 'nullable|date',
            'parent_id' => 'nullable|exists:pages,id',
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
            'meta_data' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề trang là bắt buộc.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'slug.unique' => 'Slug này đã được sử dụng.',
            'content.required' => 'Nội dung trang là bắt buộc.',
            'status.required' => 'Trạng thái trang là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'parent_id.exists' => 'Trang cha không tồn tại.',
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
        if (!$this->slug && $this->title) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->title)
            ]);
        }

        // Prevent circular parent-child relationship
        if ($this->parent_id && $this->route('page')) {
            $pageId = $this->route('page')->id;
            if ($this->parent_id == $pageId) {
                $this->merge(['parent_id' => null]);
            }
        }
    }
}
