<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $post = $this->route('post');

        if (!$user || !$post) {
            return false;
        }

        // Super admin and admin can edit any post
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return true;
        }

        // Editor can edit any post
        if ($user->hasRole('editor')) {
            return true;
        }

        // Author can only edit their own posts
        if ($user->hasRole('author')) {
            return $user->id === $post->author_id;
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
        $postId = $this->route('post')->id ?? null;

        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,' . $postId,
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image_id' => 'nullable|exists:media,id',
            'gallery' => 'nullable|array',
            'gallery.*' => 'exists:media,id',
            'status' => 'required|in:draft,published,scheduled,private',
            'post_type' => 'required|in:post,article,news,review',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:255',
            'scheduled_at' => 'nullable|date|after:now',
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
            'title.required' => 'Tiêu đề bài viết là bắt buộc.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'slug.unique' => 'Slug này đã được sử dụng.',
            'content.required' => 'Nội dung bài viết là bắt buộc.',
            'excerpt.max' => 'Tóm tắt không được vượt quá 500 ký tự.',
            'status.required' => 'Trạng thái bài viết là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'post_type.required' => 'Loại bài viết là bắt buộc.',
            'post_type.in' => 'Loại bài viết không hợp lệ.',
            'scheduled_at.after' => 'Thời gian đăng phải sau thời điểm hiện tại.',
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

        // Set scheduled_at to null if status is not scheduled
        if ($this->status !== 'scheduled') {
            $this->merge(['scheduled_at' => null]);
        }
    }
}
