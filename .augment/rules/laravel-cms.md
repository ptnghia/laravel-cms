---
type: "manual"
---

# Quy tắc Cấu Hình Riêng Cho Dự Án Laravel CMS Trên macOS

## Thư mục làm việc mặc định
- **Root Project**:
  ```bash
  /Applications/XAMPP/xamppfiles/htdocs/laravel-cms/
  ```
- **Frontend**:
  ```bash
  /Applications/XAMPP/xamppfiles/htdocs/laravel-cms/frontend/
  ```
- **Backend**:
  ```bash
  /Applications/XAMPP/xamppfiles/htdocs/laravel-cms/backend/
  ```

## Quy tắc khi chạy lệnh
1. **Luôn di chuyển vào đúng thư mục trước khi chạy lệnh**:
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/laravel-cms/
   ```

2. **NPM/Node** (Frontend)
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/laravel-cms/frontend/
   npm install
   npm run dev
   ```

3. **Laravel Artisan** (Backend)
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/laravel-cms/backend/
   php artisan migrate
   php artisan serve
   ```

4. **Chỉnh sửa file bằng tool**
   - `str-replace-editor`: dùng đường dẫn đầy đủ từ root project.
     ```bash
     str-replace-editor /Applications/XAMPP/xamppfiles/htdocs/laravel-cms/backend/app/Http/Controllers/ExampleController.php
     ```
   - `save-file`: tương tự, luôn dùng absolute path.

5. **Commit sau khi hoàn thành**
   - Cập nhật tài liệu (`README.md`) nếu thay đổi liên quan.
   - Commit theo chuẩn:
     ```bash
     git add .
     git commit -m "feat: mô tả ngắn gọn thay đổi"
     ```
