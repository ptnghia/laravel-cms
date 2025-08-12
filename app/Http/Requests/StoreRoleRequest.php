<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('super_admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:roles,name|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên role là bắt buộc.',
            'name.max' => 'Tên role không được vượt quá 100 ký tự.',
            'name.unique' => 'Role này đã tồn tại.',
            'name.regex' => 'Tên role chỉ được chứa chữ thường và dấu gạch dưới.',
            'display_name.required' => 'Tên hiển thị là bắt buộc.',
            'display_name.max' => 'Tên hiển thị không được vượt quá 255 ký tự.',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự.',
            'permissions.array' => 'Permissions phải là một mảng.',
            'permissions.*.exists' => 'Permission không tồn tại.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'tên role',
            'display_name' => 'tên hiển thị',
            'description' => 'mô tả',
            'permissions' => 'quyền',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Prevent creating system roles
            $systemRoles = ['super_admin', 'admin', 'editor', 'author', 'user'];

            if (in_array($this->name, $systemRoles)) {
                $validator->errors()->add('name', 'Không thể tạo role hệ thống.');
            }
        });
    }
}
