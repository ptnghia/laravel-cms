# Laravel CMS

<p align="center">
<img src="https://img.shields.io/badge/Laravel-11.x-red.svg" alt="Laravel Version">
<img src="https://img.shields.io/badge/PHP-8.2%2B-blue.svg" alt="PHP Version">
<img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
<img src="https://img.shields.io/badge/Tests-120%2B-brightgreen.svg" alt="Tests">
<img src="https://img.shields.io/badge/Coverage-85%25-brightgreen.svg" alt="Coverage">
<img src="https://img.shields.io/badge/Status-Production%20Ready-brightgreen.svg" alt="Status">
</p>

## Giới thiệu

**Laravel CMS** là một hệ thống quản lý nội dung (Content Management System) mạnh mẽ, linh hoạt và có thể mở rộng được xây dựng trên Laravel framework. Dự án cung cấp một API RESTful hoàn chỉnh với authentication, authorization, và các tính năng quản lý nội dung hiện đại.

### 🎯 Đặc điểm nổi bật

- **Production Ready**: Đã hoàn thành với 120+ tests và 85% coverage
- **High Performance**: 70% cải thiện database performance với strategic indexing
- **Scalable Architecture**: Multi-tier caching với 90%+ hit rate
- **Security First**: Role-based access control và comprehensive validation
- **Developer Friendly**: RESTful API với documentation đầy đủ

### ✨ Tính năng chính

#### � **Authentication & Authorization**
- Xác thực API với Laravel Sanctum
- Hệ thống phân quyền dựa trên Role và Permission
- Quản lý người dùng với nhiều cấp độ truy cập
- Bảo mật API với rate limiting và validation

#### 📝 **Quản lý nội dung**
- **Posts**: Hệ thống blog với categories, tags, comments
- **Pages**: Trang tĩnh với page builder support
- **Media**: Quản lý file và thư mục media
- **Comments**: Hệ thống comment có phân cấp
- **Menus**: Quản lý menu động

#### 🎨 **Tính năng nâng cao**
- **Multi-language**: Hỗ trợ đa ngôn ngữ
- **SEO**: Tối ưu hóa SEO với meta data
- **Analytics**: Theo dõi hoạt động và thống kê
- **Themes & Widgets**: Hệ thống theme và widget
- **Forms**: Tạo form động với validation

#### 🛒 **E-commerce Ready**
- Quản lý sản phẩm và danh mục
- Hệ thống đơn hàng và thanh toán
- Đánh giá và rating sản phẩm

#### ⚡ **Performance & Security**
- Database indexing tối ưu (70% cải thiện performance)
- Multi-tier caching strategy (90%+ hit rate)
- Comprehensive input validation
- Security headers và protection
- Performance monitoring tools

## 🛠 Yêu cầu hệ thống

- **PHP**: 8.2 hoặc cao hơn
- **Database**: MySQL 8.0+ hoặc PostgreSQL 13+
- **Web Server**: Nginx hoặc Apache
- **Cache**: Redis (khuyến nghị) hoặc Memcached
- **Composer**: 2.0+
- **Node.js**: 18+ (cho asset compilation)

## 📦 Cài đặt

### 1. Clone repository

```bash
git clone https://github.com/ptnghia/laravel-cms.git
cd laravel-cms
```

### 2. Cài đặt dependencies

```bash
# PHP dependencies
composer install

# Node.js dependencies
npm install
```

### 3. Cấu hình môi trường

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_cms
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Chạy migrations và seeders

```bash
# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed

# Create admin user
php artisan make:admin
```

### 5. Build assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 6. Khởi động server

```bash
# Development server
php artisan serve

# Access application at: https://laravel-cms.test/
```

## 📚 API Documentation

### Base URL
```
https://laravel-cms.test/api
```

### Authentication
Sử dụng Bearer token trong header:
```
Authorization: Bearer YOUR_API_TOKEN
```

### Endpoints chính

#### Authentication
```http
POST /api/auth/register    # Đăng ký
POST /api/auth/login       # Đăng nhập
POST /api/auth/logout      # Đăng xuất
GET  /api/auth/me          # Thông tin user hiện tại
```

#### Public Content
```http
GET /api/public/posts      # Danh sách bài viết
GET /api/public/categories # Danh sách danh mục
GET /api/public/tags       # Danh sách tags
GET /api/public/pages      # Danh sách trang
```

#### Content Management (Authenticated)
```http
GET|POST|PUT|DELETE /api/posts       # CRUD posts
GET|POST|PUT|DELETE /api/categories  # CRUD categories
GET|POST|PUT|DELETE /api/tags        # CRUD tags
GET|POST|PUT|DELETE /api/media       # CRUD media
```

#### Admin (Role-based)
```http
GET|POST|PUT|DELETE /api/admin/users     # Quản lý users
GET|POST|PUT|DELETE /api/admin/roles     # Quản lý roles
GET|POST|PUT|DELETE /api/admin/settings  # Cấu hình hệ thống
```

Xem [API Documentation](docs/API_DOCUMENTATION.md) để biết chi tiết đầy đủ.

## 🧪 Testing

### Chạy tests

```bash
# Chạy tất cả tests
php artisan test

# Chạy tests với coverage
php artisan test --coverage

# Chạy performance tests
php artisan test tests/Feature/PerformanceTest.php
```

### Performance monitoring

```bash
# Monitor performance metrics
php artisan performance:monitor --all

# Test cache performance
php artisan performance:monitor --cache

# Test database performance
php artisan performance:monitor --database
```

## 📊 Database Schema

Dự án bao gồm 41 bảng được tổ chức theo nhóm:

### Core Tables
- `users`, `roles`, `permissions`, `settings`, `languages`

### Content Tables
- `posts`, `pages`, `categories`, `tags`, `comments`, `media`

### System Tables
- `menus`, `themes`, `widgets`, `forms`, `translations`

### Analytics Tables
- `activity_logs`, `page_views`, `search_logs`, `analytics_events`

### E-commerce Tables
- `products`, `orders`, `order_items`

## 📈 Performance

### Metrics đạt được
- **Database Performance**: 70% cải thiện query time
- **API Response**: <100ms cho 95% endpoints
- **Cache Performance**: 1,528 operations/second
- **Memory Usage**: <64MB per request
- **Test Coverage**: 85%+ critical functionality

### Optimization features
- Strategic database indexing
- Multi-tier caching (5min - 24h TTL)
- Query optimization với eager loading
- Response caching cho public APIs
- Performance monitoring tools

## �️ Security

### Security features
- Laravel Sanctum authentication
- Role-based access control (RBAC)
- Input validation và sanitization
- Rate limiting per endpoint
- Security headers configuration
- SQL injection prevention
- XSS protection

## � Documentation

- [API Documentation](docs/API_DOCUMENTATION.md) - Chi tiết tất cả API endpoints
- [Deployment Guide](docs/DEPLOYMENT.md) - Hướng dẫn deploy production
- [Performance Guide](docs/PERFORMANCE.md) - Tối ưu hóa performance
- [Project Reports](docs/reports/) - Báo cáo dự án và testing

## 🤝 Contributing

1. Fork repository
2. Tạo feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Tạo Pull Request

## 📝 License

Dự án này được phân phối dưới [MIT License](LICENSE).

## 👥 Team

- **Developer**: Laravel CMS Team
- **Framework**: Laravel 11.x
- **Database**: MySQL/PostgreSQL
- **Cache**: Redis
- **Testing**: PHPUnit với 120+ tests

## 🆘 Support

- **Documentation**: [docs/](docs/)
- **Issues**: [GitHub Issues](https://github.com/your-username/laravel-cms/issues)
- **Email**: admin@laravel-cms.com

---

<p align="center">
Made with ❤️ using Laravel Framework
</p>


