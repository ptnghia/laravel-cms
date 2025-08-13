# Laravel CMS API Documentation

## Overview

Laravel CMS provides a comprehensive RESTful API for managing content, users, and system settings. The API follows REST conventions and returns JSON responses.

## Base URL

```
https://laravel-cms.test/api
```

## Authentication

The API uses Laravel Sanctum for authentication. Include the Bearer token in the Authorization header:

```
Authorization: Bearer YOUR_API_TOKEN
```

## Rate Limiting

Different endpoints have different rate limits:

- **Authentication**: 5-10 requests per 15 minutes
- **General API**: 60-1000 requests per minute  
- **File Upload**: 5-50 requests per hour
- **Search**: 50-200 requests per minute

## Response Format

All API responses follow a consistent format:

### Success Response
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {},
  "meta": {},
  "timestamp": "2025-08-13T19:00:00.000000Z"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": {},
  "timestamp": "2025-08-13T19:00:00.000000Z"
}
```

## Authentication Endpoints

### Register User
```http
POST /api/auth/register
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Login
```http
POST /api/auth/login
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "1|abc123..."
  }
}
```

### Logout
```http
POST /api/auth/logout
```
*Requires authentication*

### Get Current User
```http
GET /api/auth/me
```
*Requires authentication*

## Public Content Endpoints

### Get Posts
```http
GET /api/public/posts
```

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)
- `search` - Search term
- `category` - Category ID or slug
- `tag` - Tag ID or slug
- `status` - Post status (published, draft, etc.)
- `sort` - Sort field (created_at, updated_at, title, view_count)
- `order` - Sort order (asc, desc)

### Get Single Post
```http
GET /api/public/posts/{id}
GET /api/public/posts/slug/{slug}
```

### Get Categories
```http
GET /api/public/categories
GET /api/public/categories/tree
```

### Get Tags
```http
GET /api/public/tags
GET /api/public/tags/popular
```

### Get Pages
```http
GET /api/public/pages
GET /api/public/pages/{id}
GET /api/public/pages/slug/{slug}
```

### Get Menus
```http
GET /api/public/menus
GET /api/public/menus/location/{location}
```

## Content Management Endpoints

*All endpoints require authentication*

### Posts
```http
GET    /api/posts           # List posts
POST   /api/posts           # Create post
GET    /api/posts/{id}      # Get post
PUT    /api/posts/{id}      # Update post
DELETE /api/posts/{id}      # Delete post

POST   /api/posts/{id}/publish    # Publish post
POST   /api/posts/{id}/unpublish  # Unpublish post
POST   /api/posts/{id}/duplicate  # Duplicate post
```

### Categories
```http
GET    /api/categories           # List categories
POST   /api/categories           # Create category
GET    /api/categories/{id}      # Get category
PUT    /api/categories/{id}      # Update category
DELETE /api/categories/{id}      # Delete category

GET    /api/categories/tree      # Get category tree
```

### Tags
```http
GET    /api/tags           # List tags
POST   /api/tags           # Create tag
GET    /api/tags/{id}      # Get tag
PUT    /api/tags/{id}      # Update tag
DELETE /api/tags/{id}      # Delete tag

GET    /api/tags/popular   # Get popular tags
GET    /api/tags/search    # Search tags
```

### Comments
```http
GET    /api/comments                    # List comments
POST   /api/comments                    # Create comment
GET    /api/comments/{id}               # Get comment
PUT    /api/comments/{id}               # Update comment
DELETE /api/comments/{id}               # Delete comment

POST   /api/comments/{id}/approve       # Approve comment
POST   /api/comments/{id}/spam          # Mark as spam
GET    /api/comments/{type}/{id}        # Get comments for resource
```

### Media
```http
GET    /api/media           # List media files
POST   /api/media           # Upload file
GET    /api/media/{id}      # Get media file
PUT    /api/media/{id}      # Update media
DELETE /api/media/{id}      # Delete media
```

## Admin Endpoints

*Require admin role and specific permissions*

### User Management
```http
GET    /api/admin/users                    # List users (users.view)
POST   /api/admin/users                    # Create user (users.create)
GET    /api/admin/users/{id}               # Get user (users.view)
PUT    /api/admin/users/{id}               # Update user (users.edit)
DELETE /api/admin/users/{id}               # Delete user (users.delete)

POST   /api/admin/users/{id}/assign-roles  # Assign roles (users.edit)
POST   /api/admin/users/{id}/suspend       # Suspend user (users.edit)
POST   /api/admin/users/{id}/activate      # Activate user (users.edit)
GET    /api/admin/users/statistics         # User statistics (users.view)
```

### Role Management
```http
GET    /api/admin/roles                         # List roles (roles.view)
POST   /api/admin/roles                         # Create role (roles.create)
GET    /api/admin/roles/{id}                    # Get role (roles.view)
PUT    /api/admin/roles/{id}                    # Update role (roles.edit)
DELETE /api/admin/roles/{id}                    # Delete role (roles.delete)

POST   /api/admin/roles/{id}/assign-permissions # Assign permissions (roles.edit)
GET    /api/admin/roles/permissions             # List permissions (roles.view)
```

### Settings Management
```http
GET    /api/admin/settings                # List settings (settings.view)
POST   /api/admin/settings                # Create setting (settings.edit)
GET    /api/admin/settings/{id}           # Get setting (settings.view)
PUT    /api/admin/settings/{id}           # Update setting (settings.edit)
DELETE /api/admin/settings/{id}           # Delete setting (settings.delete)

POST   /api/admin/settings/bulk-update    # Bulk update (settings.edit)
GET    /api/admin/settings/groups         # Get setting groups (settings.view)
```

### System Management
```http
GET    /api/admin/system/info             # System information (super_admin)
POST   /api/admin/system/cache/clear      # Clear cache (super_admin)
POST   /api/admin/system/optimize         # Optimize system (super_admin)
POST   /api/admin/system/maintenance      # Toggle maintenance (super_admin)
GET    /api/admin/system/health           # Health check (super_admin)
```

### Analytics
```http
GET    /api/admin/analytics/overview      # Analytics overview
GET    /api/admin/analytics/content       # Content analytics
GET    /api/admin/analytics/users         # User analytics
GET    /api/admin/analytics/system        # System analytics
```

## Error Codes

| Code | Description |
|------|-------------|
| 200  | Success |
| 201  | Created |
| 400  | Bad Request |
| 401  | Unauthorized |
| 403  | Forbidden |
| 404  | Not Found |
| 422  | Validation Error |
| 429  | Too Many Requests |
| 500  | Internal Server Error |

## Validation Errors

Validation errors return a 422 status with detailed field errors:

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

## Pagination

List endpoints return paginated results:

```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 15,
    "to": 15,
    "total": 150
  },
  "links": {
    "first": "http://example.com/api/posts?page=1",
    "last": "http://example.com/api/posts?page=10",
    "prev": null,
    "next": "http://example.com/api/posts?page=2"
  }
}
```

## File Uploads

File uploads use multipart/form-data:

```http
POST /api/media
Content-Type: multipart/form-data

file: [binary data]
alt_text: "Image description"
folder_id: 1
```

## Filtering and Searching

Most list endpoints support filtering and searching:

```http
GET /api/posts?search=laravel&category=1&status=published&sort=created_at&order=desc
```

## API Versioning

The API supports versioning through headers:

```http
Accept: application/vnd.api+json
API-Version: v1
```

## SDKs and Tools

- **Postman Collection**: Available at `/api/docs/postman`
- **OpenAPI Spec**: Available at `/api/docs/openapi`
- **Interactive Docs**: Available at `/api/docs`

## Support

For API support, contact: admin@laravel-cms.com
