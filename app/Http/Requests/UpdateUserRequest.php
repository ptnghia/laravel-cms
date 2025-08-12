<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $targetUser = $this->route('user');

        if (!$user || !$targetUser) {
            return false;
        }

        // Super admin can edit any user
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin can edit non-super-admin users
        if ($user->hasRole('admin')) {
            return !$targetUser->hasRole('super_admin');
        }

        // Users can edit their own profile (limited fields)
        return $user->id === $targetUser->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id ?? null;
        $user = $this->user();
        $isAdmin = $user && ($user->hasRole('super_admin') || $user->hasRole('admin'));

        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-\(\)]+$/',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        // Admin-only fields
        if ($isAdmin) {
            $rules['password'] = ['nullable', 'string', Password::min(8)->letters()->numbers()];
            $rules['status'] = 'required|in:active,inactive,suspended';
            $rules['roles'] = 'nullable|array';
            $rules['roles.*'] = 'exists:roles,id';
        } else {
            // Regular users can only change password with current password
            $rules['current_password'] = 'required_with:password|current_password';
            $rules['password'] = ['nullable', 'string', Password::min(8)->letters()->numbers()];
        }

        return $rules;
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
            'current_password.required_with' => 'Mật khẩu hiện tại là bắt buộc khi thay đổi mật khẩu.',
            'current_password.current_password' => 'Mật khẩu hiện tại không đúng.',
            'phone.regex' => 'Số điện thoại không đúng định dạng.',
            'bio.max' => 'Tiểu sử không được vượt quá 1000 ký tự.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'avatar.image' => 'Avatar phải là file hình ảnh.',
            'avatar.mimes' => 'Avatar phải có định dạng: jpeg, png, jpg, gif.',
            'avatar.max' => 'Avatar không được vượt quá 2MB.',
        ];
    }
}
