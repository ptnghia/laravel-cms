<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
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
            'key' => 'required|string|max:255|unique:settings,key|regex:/^[a-z0-9_]+$/',
            'value' => 'required',
            'type' => 'required|in:string,integer,boolean,json,array,text',
            'group' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'key.required' => 'Key setting là bắt buộc.',
            'key.max' => 'Key không được vượt quá 255 ký tự.',
            'key.unique' => 'Key này đã tồn tại.',
            'key.regex' => 'Key chỉ được chứa chữ thường, số và dấu gạch dưới.',
            'value.required' => 'Giá trị setting là bắt buộc.',
            'type.required' => 'Loại dữ liệu là bắt buộc.',
            'type.in' => 'Loại dữ liệu không hợp lệ.',
            'group.required' => 'Nhóm setting là bắt buộc.',
            'group.max' => 'Nhóm không được vượt quá 100 ký tự.',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'key' => 'key',
            'value' => 'giá trị',
            'type' => 'loại dữ liệu',
            'group' => 'nhóm',
            'description' => 'mô tả',
            'is_public' => 'công khai',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate value based on type
            $type = $this->type;
            $value = $this->value;

            switch ($type) {
                case 'integer':
                    if (!is_numeric($value)) {
                        $validator->errors()->add('value', 'Giá trị phải là số nguyên.');
                    }
                    break;

                case 'boolean':
                    if (!in_array($value, ['true', 'false', '1', '0', 1, 0, true, false])) {
                        $validator->errors()->add('value', 'Giá trị phải là true hoặc false.');
                    }
                    break;

                case 'json':
                case 'array':
                    if (is_string($value)) {
                        json_decode($value);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $validator->errors()->add('value', 'Giá trị phải là JSON hợp lệ.');
                        }
                    }
                    break;
            }

            // Prevent overriding system settings
            $systemKeys = [
                'app_name', 'app_url', 'app_env', 'app_debug',
                'db_connection', 'db_host', 'db_port', 'db_database',
                'mail_driver', 'mail_host', 'mail_port',
            ];

            if (in_array($this->key, $systemKeys)) {
                $validator->errors()->add('key', 'Không thể tạo setting hệ thống.');
            }
        });
    }
}
