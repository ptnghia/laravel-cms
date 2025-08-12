<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:menus,name',
            'location' => 'required|string|max:100|unique:menus,location|regex:/^[a-z_]+$/',
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
            'menu_items.*.title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'menu_items.*.url.required' => 'URL menu item là bắt buộc.',
            'menu_items.*.url.max' => 'URL không được vượt quá 500 ký tự.',
            'menu_items.*.target.in' => 'Target không hợp lệ.',
            'menu_items.*.icon.max' => 'Icon class không được vượt quá 100 ký tự.',
            'menu_items.*.css_class.max' => 'CSS class không được vượt quá 255 ký tự.',
            'menu_items.*.sort_order.min' => 'Thứ tự sắp xếp phải lớn hơn hoặc bằng 0.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'tên menu',
            'location' => 'vị trí',
            'is_active' => 'trạng thái hoạt động',
            'menu_items' => 'menu items',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default is_active if not provided
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }

        // Clean location
        if ($this->location) {
            $this->merge([
                'location' => strtolower(str_replace([' ', '-'], '_', $this->location))
            ]);
        }

        // Process menu items
        if ($this->menu_items) {
            $menuItems = $this->menu_items;
            foreach ($menuItems as $index => $item) {
                // Set default values
                if (!isset($item['target'])) {
                    $menuItems[$index]['target'] = '_self';
                }
                if (!isset($item['sort_order'])) {
                    $menuItems[$index]['sort_order'] = $index;
                }
                if (!isset($item['is_active'])) {
                    $menuItems[$index]['is_active'] = true;
                }
            }
            $this->merge(['menu_items' => $menuItems]);
        }
    }
}
