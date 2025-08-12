# Laravel CMS

<p align="center">
<img src="https://img.shields.io/badge/Laravel-11.x-red.svg" alt="Laravel Version">
<img src="https://img.shields.io/badge/PHP-8.3+-blue.svg" alt="PHP Version">
<img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
<img src="https://img.shields.io/badge/Status-In%20Development-yellow.svg" alt="Development Status">
</p>

## Giới thiệu

**Laravel CMS** là một hệ quản trị nội dung (Content Management System) hiện đại được xây dựng trên nền tảng Laravel mới nhất, PHP 8.3+ và MySQL/MariaDB. Dự án được thiết kế với kiến trúc module hóa, tập trung vào hiệu năng cao, khả năng mở rộng và trải nghiệm người dùng tối ưu.

### 🎯 Mục tiêu dự án

- **Hiệu năng cao**: Tải trang < 2 giây với ~500 concurrent users
- **Kiến trúc module**: Dễ dàng cài đặt/gỡ bỏ chức năng theo nhu cầu
- **Developer-friendly**: API chuẩn, tài liệu đầy đủ, dễ tùy chỉnh
- **End-user friendly**: Giao diện quản trị hiện đại, trực quan

### ✨ Tính năng chính

#### 🔧 Core Modules (Tính năng cốt lõi)

- **Quản lý nội dung**: Bài viết, trang tĩnh, danh mục, thẻ, media với trình soạn thảo rich text
- **Hệ thống người dùng**: Đăng nhập/đăng ký, phân quyền theo vai trò (author, editor, admin)
- **Module/Plugin**: Tự động phát hiện, cài đặt/gỡ bỏ, bật/tắt module linh hoạt
- **Theme/Template**: Quản lý giao diện, tùy chỉnh theme, preview real-time
- **Đa ngôn ngữ**: Hỗ trợ đa ngôn ngữ cho nội dung và giao diện
- **SEO tối ưu**: Meta tags, sitemap, schema markup, friendly URLs, robots.txt
- **Hệ thống cache**: Cache data, view, page để tối ưu hiệu năng

#### 🚀 Advanced Modules (Tính năng nâng cao)

- **E-commerce**: Quản lý sản phẩm, đơn hàng, khách hàng, thanh toán
- **Workflow**: Quy trình duyệt nội dung, quản lý trạng thái
- **Analytics**: Thống kê truy cập, phân tích nội dung
- **API**: RESTful API cho mobile app và tích hợp bên ngoài
- **Import/Export**: Hỗ trợ CSV, JSON cho việc di chuyển dữ liệu

## 🛠 Yêu cầu hệ thống

- **PHP**: 8.3 hoặc cao hơn
- **Laravel**: 11.x
- **Database**: MySQL 8.0+ hoặc MariaDB 10.4+
- **Web Server**: Apache 2.4+ hoặc Nginx 1.18+
- **Composer**: 2.0+
- **Node.js**: 18+ (cho build assets)

## 📦 Cài đặt

### 1. Clone repository

```bash
git clone https://github.com/ptnghia/laravel-cms.git
cd laravel-cms
```

### 2. Cài đặt dependencies

```bash
composer install
npm install
```

### 3. Cấu hình môi trường

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Cấu hình database

Chỉnh sửa file `.env` với thông tin database của bạn:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_cms
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Chạy migration và seeder

```bash
php artisan migrate
php artisan db:seed
```

### 6. Build assets

```bash
npm run build
```

### 7. Khởi chạy server

```bash
php artisan serve
```

Truy cập `http://localhost:8000` để sử dụng ứng dụng.

## 🗂 Cấu trúc dự án

```
laravel-cms/
├── app/
│   ├── Modules/           # Các module tùy chỉnh
│   ├── Themes/            # Các theme
│   └── ...
├── database/
│   ├── migrations/        # Database migrations
│   └── seeders/          # Database seeders
├── resources/
│   ├── views/            # Blade templates
│   ├── js/               # JavaScript files
│   └── css/              # CSS files
└── ...
```

## 🚀 Roadmap phát triển

### Giai đoạn 1: Nền tảng cốt lõi (2-3 tháng)
- ✅ Khởi tạo dự án Laravel
- 🔄 Hệ thống authentication & phân quyền
- 📝 Module quản lý nội dung cơ bản
- 🎨 Hệ thống theme/template

### Giai đoạn 2: Nâng cao & mở rộng (2-3 tháng)
- 🔌 Hệ thống module/plugin
- ✏️ Block editor hiện đại
- 🌐 API-first & Headless CMS
- 🔍 SEO Engine nâng cao

### Giai đoạn 3: AI & Phân tích (2-3 tháng)
- 🤖 AI Content Assistant
- 📊 Smart Analytics
- 🖼️ Tối ưu hình ảnh tự động

### Giai đoạn 4: Enterprise & E-commerce (3+ tháng)
- 🏢 Multi-tenant support
- 🛒 E-commerce đầy đủ
- 📋 Workflow doanh nghiệp
- 🔒 Bảo mật nâng cao

### Giai đoạn 5: Hệ sinh thái (6+ tháng)
- 🏪 Marketplace theme/plugin
- 📱 Mobile app
- ☁️ Cloud hosting & DevOps

## 🤝 Đóng góp

Chúng tôi hoan nghênh mọi đóng góp cho dự án! Vui lòng:

1. Fork repository
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

## 📄 License

Dự án này được phân phối dưới giấy phép [MIT License](https://opensource.org/licenses/MIT).

## 📞 Liên hệ

- **Author**: Phan Trung Nghia
- **Email**: ptnghia.dev@gmail.com
- **GitHub**: [@ptnghia](https://github.com/ptnghia)

---

⭐ Nếu dự án này hữu ích, hãy cho chúng tôi một star!
