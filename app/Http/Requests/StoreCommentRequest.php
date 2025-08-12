<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow both authenticated and guest users to comment
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'content' => 'required|string|max:2000|min:10',
            'commentable_type' => 'required|in:App\Models\Post,App\Models\Page',
            'commentable_id' => 'required|integer',
            'parent_id' => 'nullable|exists:comments,id',
        ];

        // Guest users must provide name and email
        if (!$user) {
            $rules['author_name'] = 'required|string|max:255|min:2';
            $rules['author_email'] = 'required|email|max:255';
            $rules['author_website'] = 'nullable|url|max:255';

            // Add honeypot and captcha for spam protection
            $rules['website'] = 'nullable|max:0'; // Honeypot field
            $rules['captcha'] = 'required|captcha'; // If using captcha
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Nội dung bình luận là bắt buộc.',
            'content.min' => 'Nội dung bình luận phải có ít nhất 10 ký tự.',
            'content.max' => 'Nội dung bình luận không được vượt quá 2000 ký tự.',
            'commentable_type.required' => 'Loại nội dung là bắt buộc.',
            'commentable_type.in' => 'Loại nội dung không hợp lệ.',
            'commentable_id.required' => 'ID nội dung là bắt buộc.',
            'author_name.required' => 'Tên là bắt buộc.',
            'author_name.min' => 'Tên phải có ít nhất 2 ký tự.',
            'author_name.max' => 'Tên không được vượt quá 255 ký tự.',
            'author_email.required' => 'Email là bắt buộc.',
            'author_email.email' => 'Email không đúng định dạng.',
            'author_website.url' => 'Website không đúng định dạng URL.',
            'website.max' => 'Trường này phải để trống.', // Honeypot message
            'captcha.required' => 'Vui lòng xác thực captcha.',
            'captcha.captcha' => 'Captcha không đúng.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Remove honeypot field from validation data
        if ($this->has('website')) {
            $this->request->remove('website');
        }

        // Set author info from authenticated user if available
        if ($this->user()) {
            $this->merge([
                'author_name' => $this->user()->name,
                'author_email' => $this->user()->email,
            ]);
        }
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        // Log failed comment attempts for spam detection
        if (!$this->user()) {
            \Log::warning('Failed comment validation', [
                'ip' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        parent::failedValidation($validator);
    }
}
