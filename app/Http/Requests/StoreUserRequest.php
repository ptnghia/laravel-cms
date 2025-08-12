<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && (
            $this->user()->hasRole('super_admin') ||
            $this->user()->hasRole('admin')
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
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
            'phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'bio' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive,suspended',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên người dùng là bắt buộc.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.required' => 'Mật khẩu là bắt buộc.',
            'phone.regex' => 'Số điện thoại không đúng định dạng.',
            'bio.max' => 'Tiểu sử không được vượt quá 1000 ký tự.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'avatar.image' => 'Avatar phải là file hình ảnh.',
            'avatar.mimes' => 'Avatar phải có định dạng: jpeg, png, jpg, gif.',
            'avatar.max' => 'Avatar không được vượt quá 2MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'tên',
            'email' => 'email',
            'password' => 'mật khẩu',
            'phone' => 'số điện thoại',
            'bio' => 'tiểu sử',
            'status' => 'trạng thái',
            'roles' => 'vai trò',
            'avatar' => 'ảnh đại diện',
        ];
    }
}
