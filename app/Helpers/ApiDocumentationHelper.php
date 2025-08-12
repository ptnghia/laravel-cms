<?php

namespace App\Helpers;

class ApiDocumentationHelper
{
    /**
     * Get API endpoint documentation.
     */
    public static function getEndpoints(): array
    {
        return [
            'authentication' => [
                'base_url' => '/api/auth',
                'endpoints' => [
                    'POST /register' => 'Đăng ký tài khoản mới',
                    'POST /login' => 'Đăng nhập',
                    'POST /logout' => 'Đăng xuất',
                    'POST /refresh' => 'Làm mới token',
                    'POST /forgot-password' => 'Quên mật khẩu',
                    'POST /reset-password' => 'Đặt lại mật khẩu',
                ],
            ],
            'profile' => [
                'base_url' => '/api/profile',
                'endpoints' => [
                    'GET /' => 'Xem thông tin profile',
                    'PUT /' => 'Cập nhật profile',
                    'PUT /password' => 'Đổi mật khẩu',
                    'POST /avatar' => 'Upload avatar',
                    'DELETE /avatar' => 'Xóa avatar',
                    'DELETE /account' => 'Xóa tài khoản',
                ],
            ],
            'posts' => [
                'base_url' => '/api/posts',
                'public_base_url' => '/api/public/posts',
                'endpoints' => [
                    'GET /' => 'Danh sách bài viết',
                    'POST /' => 'Tạo bài viết mới',
                    'GET /{id}' => 'Chi tiết bài viết',
                    'PUT /{id}' => 'Cập nhật bài viết',
                    'DELETE /{id}' => 'Xóa bài viết',
                    'POST /{id}/publish' => 'Xuất bản bài viết',
                    'POST /{id}/unpublish' => 'Hủy xuất bản',
                    'POST /{id}/duplicate' => 'Nhân bản bài viết',
                    'GET /slug/{slug}' => 'Lấy bài viết theo slug',
                ],
                'filters' => [
                    'search' => 'Tìm kiếm trong title, content, excerpt',
                    'status' => 'draft, published, scheduled, private',
                    'post_type' => 'post, article, news, review',
                    'category_id' => 'ID danh mục',
                    'author_id' => 'ID tác giả',
                    'tags' => 'Danh sách tag IDs',
                    'featured' => 'true/false',
                ],
                'sorting' => [
                    'sort_by' => 'created_at, updated_at, published_at, title, view_count',
                    'sort_order' => 'asc, desc',
                ],
            ],
            'categories' => [
                'base_url' => '/api/categories',
                'public_base_url' => '/api/public/categories',
                'endpoints' => [
                    'GET /' => 'Danh sách danh mục',
                    'POST /' => 'Tạo danh mục mới',
                    'GET /{id}' => 'Chi tiết danh mục',
                    'PUT /{id}' => 'Cập nhật danh mục',
                    'DELETE /{id}' => 'Xóa danh mục',
                    'GET /slug/{slug}' => 'Lấy danh mục theo slug',
                    'GET /tree' => 'Cây danh mục phân cấp',
                ],
            ],
            'tags' => [
                'base_url' => '/api/tags',
                'public_base_url' => '/api/public/tags',
                'endpoints' => [
                    'GET /' => 'Danh sách tag',
                    'POST /' => 'Tạo tag mới',
                    'GET /{id}' => 'Chi tiết tag',
                    'PUT /{id}' => 'Cập nhật tag',
                    'DELETE /{id}' => 'Xóa tag',
                    'GET /slug/{slug}' => 'Lấy tag theo slug',
                    'GET /popular' => 'Tag phổ biến',
                    'GET /search' => 'Tìm kiếm tag',
                ],
            ],
            'comments' => [
                'base_url' => '/api/comments',
                'public_base_url' => '/api/public/comments',
                'endpoints' => [
                    'GET /' => 'Danh sách comment',
                    'POST /' => 'Tạo comment mới',
                    'GET /{id}' => 'Chi tiết comment',
                    'PUT /{id}' => 'Cập nhật comment',
                    'DELETE /{id}' => 'Xóa comment',
                    'POST /{id}/approve' => 'Duyệt comment',
                    'POST /{id}/spam' => 'Đánh dấu spam',
                    'GET /{type}/{id}' => 'Comment của post/page',
                ],
            ],
            'media' => [
                'base_url' => '/api/media',
                'endpoints' => [
                    'GET /' => 'Danh sách media',
                    'POST /' => 'Upload file',
                    'GET /{id}' => 'Chi tiết media',
                    'PUT /{id}' => 'Cập nhật media',
                    'DELETE /{id}' => 'Xóa media',
                    'GET /{id}/download' => 'Download file',
                ],
            ],
            'admin_users' => [
                'base_url' => '/api/admin/users',
                'endpoints' => [
                    'GET /' => 'Danh sách người dùng',
                    'POST /' => 'Tạo người dùng mới',
                    'GET /{id}' => 'Chi tiết người dùng',
                    'PUT /{id}' => 'Cập nhật người dùng',
                    'DELETE /{id}' => 'Xóa người dùng',
                    'POST /{id}/assign-roles' => 'Gán vai trò',
                    'POST /{id}/suspend' => 'Tạm khóa',
                    'POST /{id}/activate' => 'Kích hoạt',
                    'GET /statistics' => 'Thống kê người dùng',
                ],
            ],
            'admin_roles' => [
                'base_url' => '/api/admin/roles',
                'endpoints' => [
                    'GET /' => 'Danh sách vai trò',
                    'POST /' => 'Tạo vai trò mới',
                    'GET /{id}' => 'Chi tiết vai trò',
                    'PUT /{id}' => 'Cập nhật vai trò',
                    'DELETE /{id}' => 'Xóa vai trò',
                    'GET /permissions' => 'Danh sách quyền',
                    'POST /{id}/assign-permissions' => 'Gán quyền',
                ],
            ],
            'admin_settings' => [
                'base_url' => '/api/admin/settings',
                'endpoints' => [
                    'GET /' => 'Danh sách cài đặt',
                    'POST /' => 'Tạo cài đặt mới',
                    'GET /{id}' => 'Chi tiết cài đặt',
                    'PUT /{id}' => 'Cập nhật cài đặt',
                    'DELETE /{id}' => 'Xóa cài đặt',
                    'GET /groups' => 'Cài đặt theo nhóm',
                    'POST /bulk-update' => 'Cập nhật hàng loạt',
                ],
            ],
        ];
    }

    /**
     * Get response format documentation.
     */
    public static function getResponseFormat(): array
    {
        return [
            'success_response' => [
                'success' => true,
                'message' => 'Success message',
                'timestamp' => '2024-01-01T00:00:00.000000Z',
                'status_code' => 200,
                'data' => '// Response data here',
            ],
            'error_response' => [
                'success' => false,
                'message' => 'Error message',
                'timestamp' => '2024-01-01T00:00:00.000000Z',
                'status_code' => 400,
                'error' => [
                    'code' => 400,
                    'type' => 'ERROR_TYPE',
                ],
                'errors' => '// Validation errors (if applicable)',
            ],
            'paginated_response' => [
                'success' => true,
                'message' => 'Data retrieved successfully',
                'timestamp' => '2024-01-01T00:00:00.000000Z',
                'status_code' => 200,
                'data' => [
                    'items' => '// Array of items',
                    'pagination' => [
                        'current_page' => 1,
                        'last_page' => 10,
                        'per_page' => 15,
                        'total' => 150,
                        'from' => 1,
                        'to' => 15,
                        'has_more_pages' => true,
                        'links' => [
                            'first' => 'http://example.com/api/posts?page=1',
                            'last' => 'http://example.com/api/posts?page=10',
                            'prev' => null,
                            'next' => 'http://example.com/api/posts?page=2',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get authentication documentation.
     */
    public static function getAuthenticationDocs(): array
    {
        return [
            'type' => 'Bearer Token (Laravel Sanctum)',
            'header' => 'Authorization: Bearer {token}',
            'how_to_get_token' => [
                '1. POST /api/auth/login with email and password',
                '2. Extract token from response.data.token',
                '3. Include in Authorization header for protected routes',
            ],
            'token_expiration' => 'Tokens expire based on sanctum configuration',
            'refresh_token' => 'Use POST /api/auth/refresh to get new token',
        ];
    }

    /**
     * Get common query parameters documentation.
     */
    public static function getQueryParametersDocs(): array
    {
        return [
            'pagination' => [
                'page' => 'Page number (default: 1)',
                'per_page' => 'Items per page (default: 15, max: 100)',
            ],
            'sorting' => [
                'sort_by' => 'Field to sort by (default: created_at)',
                'sort_order' => 'Sort direction: asc or desc (default: desc)',
            ],
            'filtering' => [
                'search' => 'Search term for text fields',
                'status' => 'Filter by status',
                'created_at_from' => 'Filter from date (Y-m-d H:i:s)',
                'created_at_to' => 'Filter to date (Y-m-d H:i:s)',
            ],
            'includes' => [
                'include' => 'Comma-separated list of relationships to include',
                'example' => '?include=author,category,tags',
            ],
        ];
    }

    /**
     * Get error codes documentation.
     */
    public static function getErrorCodesDocs(): array
    {
        return [
            200 => 'Success - Request completed successfully',
            201 => 'Created - Resource created successfully',
            204 => 'No Content - Request successful, no content returned',
            400 => 'Bad Request - Invalid request format or parameters',
            401 => 'Unauthorized - Authentication required',
            403 => 'Forbidden - Insufficient permissions',
            404 => 'Not Found - Resource or endpoint not found',
            422 => 'Validation Error - Request data validation failed',
            429 => 'Rate Limit Exceeded - Too many requests',
            500 => 'Internal Server Error - Server error occurred',
        ];
    }
}
