<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuRequest extends FormRequest
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
        $menuId = $this->route('menu')->id ?? null;

        return [
            'name' => 'required|string|max:255|unique:menus,name,' . $menuId,
            'location' => 'required|string|max:100|unique:menus,location,' . $menuId . '|regex:/^[a-z_]+$/',
            'is_active' => 'boolean',
            'menu_items' => 'nullable|array',
            'menu_items.*.title' => 'required|string|max:255',
            'menu_items.*.url' => 'required|string|max:500',
            'menu_items.*.target' => 'nullable|in:_self,_blank,_parent,_top',
            'menu_items.*.icon' => 'nullable|string|max:100',
            'menu_items.*.css_class' => 'nullable|string|max:255',
            'menu_items.*.parent_id' => 'nullable|integer',
            'menu_items.*.sort_order' => 'nullable|integer|min:0',
            'menu_items.*.is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên menu là bắt buộc.',
            'name.max' => 'Tên menu không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên menu này đã tồn tại.',
            'location.required' => 'Vị trí menu là bắt buộc.',
            'location.max' => 'Vị trí không được vượt quá 100 ký tự.',
            'location.unique' => 'Vị trí menu này đã được sử dụng.',
            'location.regex' => 'Vị trí chỉ được chứa chữ thường và dấu gạch dưới.',
            'menu_items.*.title.required' => 'Tiêu đề menu item là bắt buộc.',
            'menu_items.*.url.required' => 'URL menu item là bắt buộc.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean location
        if ($this->location) {
            $this->merge([
                'location' => strtolower(str_replace([' ', '-'], '_', $this->location))
            ]);
        }
    }
}
