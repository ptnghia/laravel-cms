<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for maintenance mode. This allows
    | you to temporarily disable your application while performing updates
    | or maintenance tasks.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Status
    |--------------------------------------------------------------------------
    |
    | This option controls whether maintenance mode is enabled by default.
    | You can override this at runtime using the MaintenanceMode middleware
    | methods or by setting the cache value.
    |
    */

    'enabled' => env('MAINTENANCE_MODE_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Allowed IP Addresses
    |--------------------------------------------------------------------------
    |
    | These IP addresses will be allowed to access the application even when
    | maintenance mode is enabled. This is useful for allowing developers
    | and administrators to test the application during maintenance.
    |
    */

    'allowed_ips' => [
        '127.0.0.1',
        '::1',
        // Add your IP addresses here
        // '192.168.1.100',
        // '203.0.113.1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed User IDs
    |--------------------------------------------------------------------------
    |
    | These user IDs will be allowed to access the application even when
    | maintenance mode is enabled. This is useful for allowing specific
    | users to bypass maintenance mode.
    |
    */

    'allowed_user_ids' => [
        // 1, // Super admin user ID
        // 2, // Another admin user ID
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Maintenance Data
    |--------------------------------------------------------------------------
    |
    | This data will be used as defaults when maintenance mode is enabled
    | without specific data. You can override these values when enabling
    | maintenance mode programmatically.
    |
    */

    'default_data' => [
        'message' => 'Hệ thống đang được bảo trì. Vui lòng thử lại sau.',
        'reason' => 'Bảo trì định kỳ hệ thống',
        'estimated_duration' => '2 giờ',
        'retry_after' => 3600, // 1 hour in seconds
        'contact_email' => env('MAIL_FROM_ADDRESS', 'admin@laravel-cms.com'),
        'progress' => 0, // 0-100%
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance View
    |--------------------------------------------------------------------------
    |
    | This is the view that will be rendered when maintenance mode is active
    | and a user accesses the application via a web browser. You can customize
    | this view to match your application's design.
    |
    */

    'view' => 'maintenance',

    /*
    |--------------------------------------------------------------------------
    | Bypass Routes
    |--------------------------------------------------------------------------
    |
    | These routes will always be accessible even when maintenance mode is
    | enabled. This is useful for health checks, status endpoints, and
    | authentication routes.
    |
    */

    'bypass_routes' => [
        'api/health',
        'api/status',
        'api/ping',
        'api/auth/login',
        'api/maintenance/status',
        'up', // Laravel's health check route
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | These settings control how maintenance mode data is cached. The TTL
    | determines how long the maintenance mode status and data will be
    | cached before being refreshed.
    |
    */

    'cache' => [
        'ttl' => 3600, // 1 hour in seconds
        'key_prefix' => 'maintenance_mode',
    ],

];
