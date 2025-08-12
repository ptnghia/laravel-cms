<?php

namespace Tests\Feature\Api;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Setting;

class SystemTest extends BaseApiTest
{
    /** @test */
    public function admin_can_view_dashboard_quick_stats(): void
    {
        $response = $this->actingAsAdmin()
            ->apiGet('/api/admin/dashboard/quick-stats');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'stats' => [
                    'posts' => ['total', 'published', 'drafts', 'today'],
                    'comments' => ['total', 'approved', 'pending', 'today'],
                    'users' => ['total', 'active', 'new_today', 'online'],
                    'system' => ['total_views', 'avg_rating', 'storage_used', 'uptime'],
                ],
            ],
        ]);
    }

    /** @test */
    public function admin_can_view_analytics_overview(): void
    {
        $response = $this->actingAsAdmin()
            ->apiGet('/api/admin/analytics/overview');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'statistics' => [
                    'content',
                    'users',
                    'media',
                    'system',
                ],
            ],
        ]);
    }

    /** @test */
    public function admin_can_view_system_info(): void
    {
        $response = $this->actingAsAdmin()
            ->apiGet('/api/admin/system/info');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'system_info' => [
                    'application' => ['name', 'version', 'environment'],
                    'server' => ['php_version', 'laravel_version'],
                    'database' => ['connection', 'driver'],
                    'cache' => ['driver'],
                    'queue' => ['default'],
                    'mail' => ['driver'],
                ],
            ],
        ]);
    }

    /** @test */
    public function admin_can_check_system_health(): void
    {
        $response = $this->actingAsAdmin()
            ->apiGet('/api/admin/system/health');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'health' => [
                    'status',
                    'checks' => [
                        'database' => ['status', 'message'],
                        'cache' => ['status', 'message'],
                        'storage' => ['status', 'message'],
                        'queue' => ['status', 'message'],
                    ],
                    'timestamp',
                ],
            ],
        ]);
    }

    /** @test */
    public function admin_can_clear_cache(): void
    {
        $response = $this->actingAsAdmin()
            ->apiPost('/api/admin/system/clear-cache', [
                'type' => 'config',
            ]);

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.cleared.0', 'Configuration cache');
    }

    /** @test */
    public function admin_can_optimize_application(): void
    {
        $response = $this->actingAsAdmin()
            ->apiPost('/api/admin/system/optimize');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => ['optimizations'],
        ]);
    }

    /** @test */
    public function guest_can_view_public_menus(): void
    {
        $menu = Menu::factory()->create([
            'name' => 'Header Menu',
            'location' => 'header',
            'is_active' => true,
        ]);

        $response = $this->apiGet('/api/public/menus');

        $this->assertApiSuccess($response);
        $this->assertPaginatedResponse($response);
    }

    /** @test */
    public function guest_can_view_menu_by_location(): void
    {
        $menu = Menu::factory()->create([
            'location' => 'header',
            'is_active' => true,
        ]);
        MenuItem::factory()->count(3)->create(['menu_id' => $menu->id]);

        $response = $this->apiGet('/api/public/menus/location/header');

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.location', 'header');
        $response->assertJsonCount(3, 'data.menu_items');
    }

    /** @test */
    public function admin_can_create_menu(): void
    {
        $menuData = [
            'name' => 'Test Menu',
            'location' => 'test_location',
            'is_active' => true,
            'menu_items' => [
                [
                    'title' => 'Home',
                    'url' => '/',
                    'sort_order' => 0,
                ],
                [
                    'title' => 'About',
                    'url' => '/about',
                    'sort_order' => 1,
                ],
            ],
        ];

        $response = $this->actingAsAdmin()
            ->apiPost('/api/admin/menus', $menuData);

        $this->assertApiSuccess($response, 201);
        $response->assertJsonPath('data.name', 'Test Menu');
        $response->assertJsonCount(2, 'data.menu_items');

        $this->assertDatabaseHas('menus', [
            'name' => 'Test Menu',
            'location' => 'test_location',
        ]);
    }

    /** @test */
    public function admin_can_view_available_themes(): void
    {
        $response = $this->actingAsAdmin()
            ->apiGet('/api/admin/themes');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'themes' => [
                    '*' => [
                        'name',
                        'display_name',
                        'path',
                        'is_active',
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function admin_can_get_current_theme(): void
    {
        $response = $this->actingAsAdmin()
            ->apiGet('/api/admin/themes/current');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'theme' => [
                    'name',
                    'display_name',
                    'is_active',
                ],
            ],
        ]);
    }

    /** @test */
    public function admin_can_activate_theme(): void
    {
        $response = $this->actingAsAdmin()
            ->apiPost('/api/admin/themes/activate', [
                'theme' => 'default',
            ]);

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.theme', 'default');

        $this->assertDatabaseHas('settings', [
            'key' => 'active_theme',
            'value' => 'default',
        ]);
    }

    /** @test */
    public function admin_can_get_theme_customization(): void
    {
        $response = $this->actingAsAdmin()
            ->apiGet('/api/admin/themes/customization');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'customizations' => [
                    'colors',
                    'typography',
                    'layout',
                    'header',
                    'footer',
                ],
            ],
        ]);
    }

    /** @test */
    public function admin_can_update_theme_customization(): void
    {
        $customizationData = [
            'section' => 'colors',
            'settings' => [
                'primary_color' => '#FF5722',
                'secondary_color' => '#607D8B',
            ],
        ];

        $response = $this->actingAsAdmin()
            ->apiPost('/api/admin/themes/customization', $customizationData);

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.section', 'colors');

        $this->assertDatabaseHas('settings', [
            'key' => 'theme_colors_primary_color',
            'value' => '#FF5722',
        ]);
    }

    /** @test */
    public function regular_user_cannot_access_admin_system_routes(): void
    {
        $routes = [
            '/api/admin/dashboard',
            '/api/admin/analytics/overview',
            '/api/admin/system/info',
            '/api/admin/themes',
            '/api/admin/menus',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAsUser()->apiGet($route);
            $this->assertForbidden($response, "Route {$route} should be forbidden for regular users");
        }
    }

    /** @test */
    public function editor_can_access_content_management_routes(): void
    {
        $routes = [
            '/api/admin/dashboard',
            '/api/posts',
            '/api/categories',
            '/api/tags',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAsEditor()->apiGet($route);
            $this->assertApiSuccess($response, "Route {$route} should be accessible for editors");
        }
    }

    /** @test */
    public function menu_locations_are_available(): void
    {
        $response = $this->actingAsAdmin()
            ->apiGet('/api/admin/menu-locations');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'locations',
            ],
        ]);
    }
}
