<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions first
        $this->createPermissions();

        // Create roles
        $this->createRoles();

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Create all permissions.
     */
    private function createPermissions(): void
    {
        $permissions = [
            // Users module
            ['name' => 'users.view', 'display_name' => 'View Users', 'description' => 'Can view users list', 'module' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'description' => 'Can create new users', 'module' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users', 'description' => 'Can edit users', 'module' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'description' => 'Can delete users', 'module' => 'users'],
            ['name' => 'users.manage_roles', 'display_name' => 'Manage User Roles', 'description' => 'Can assign/remove roles', 'module' => 'users'],

            // Posts module
            ['name' => 'posts.view', 'display_name' => 'View Posts', 'description' => 'Can view posts', 'module' => 'posts'],
            ['name' => 'posts.create', 'display_name' => 'Create Posts', 'description' => 'Can create posts', 'module' => 'posts'],
            ['name' => 'posts.edit', 'display_name' => 'Edit Posts', 'description' => 'Can edit posts', 'module' => 'posts'],
            ['name' => 'posts.edit_own', 'display_name' => 'Edit Own Posts', 'description' => 'Can edit own posts only', 'module' => 'posts'],
            ['name' => 'posts.delete', 'display_name' => 'Delete Posts', 'description' => 'Can delete posts', 'module' => 'posts'],
            ['name' => 'posts.publish', 'display_name' => 'Publish Posts', 'description' => 'Can publish posts', 'module' => 'posts'],

            // Pages module
            ['name' => 'pages.view', 'display_name' => 'View Pages', 'description' => 'Can view pages', 'module' => 'pages'],
            ['name' => 'pages.create', 'display_name' => 'Create Pages', 'description' => 'Can create pages', 'module' => 'pages'],
            ['name' => 'pages.edit', 'display_name' => 'Edit Pages', 'description' => 'Can edit pages', 'module' => 'pages'],
            ['name' => 'pages.delete', 'display_name' => 'Delete Pages', 'description' => 'Can delete pages', 'module' => 'pages'],

            // Categories module
            ['name' => 'categories.view', 'display_name' => 'View Categories', 'description' => 'Can view categories', 'module' => 'categories'],
            ['name' => 'categories.create', 'display_name' => 'Create Categories', 'description' => 'Can create categories', 'module' => 'categories'],
            ['name' => 'categories.edit', 'display_name' => 'Edit Categories', 'description' => 'Can edit categories', 'module' => 'categories'],
            ['name' => 'categories.delete', 'display_name' => 'Delete Categories', 'description' => 'Can delete categories', 'module' => 'categories'],

            // Media module
            ['name' => 'media.view', 'display_name' => 'View Media', 'description' => 'Can view media files', 'module' => 'media'],
            ['name' => 'media.upload', 'display_name' => 'Upload Media', 'description' => 'Can upload media files', 'module' => 'media'],
            ['name' => 'media.edit', 'display_name' => 'Edit Media', 'description' => 'Can edit media files', 'module' => 'media'],
            ['name' => 'media.delete', 'display_name' => 'Delete Media', 'description' => 'Can delete media files', 'module' => 'media'],

            // Comments module
            ['name' => 'comments.view', 'display_name' => 'View Comments', 'description' => 'Can view comments', 'module' => 'comments'],
            ['name' => 'comments.create', 'display_name' => 'Create Comments', 'description' => 'Can create comments', 'module' => 'comments'],
            ['name' => 'comments.edit', 'display_name' => 'Edit Comments', 'description' => 'Can edit comments', 'module' => 'comments'],
            ['name' => 'comments.delete', 'display_name' => 'Delete Comments', 'description' => 'Can delete comments', 'module' => 'comments'],
            ['name' => 'comments.moderate', 'display_name' => 'Moderate Comments', 'description' => 'Can approve/reject comments', 'module' => 'comments'],

            // Settings module
            ['name' => 'settings.view', 'display_name' => 'View Settings', 'description' => 'Can view settings', 'module' => 'settings'],
            ['name' => 'settings.edit', 'display_name' => 'Edit Settings', 'description' => 'Can edit settings', 'module' => 'settings'],

            // Menus module
            ['name' => 'menus.view', 'display_name' => 'View Menus', 'description' => 'Can view menus', 'module' => 'menus'],
            ['name' => 'menus.create', 'display_name' => 'Create Menus', 'description' => 'Can create menus', 'module' => 'menus'],
            ['name' => 'menus.edit', 'display_name' => 'Edit Menus', 'description' => 'Can edit menus', 'module' => 'menus'],
            ['name' => 'menus.delete', 'display_name' => 'Delete Menus', 'description' => 'Can delete menus', 'module' => 'menus'],

            // Forms module
            ['name' => 'forms.view', 'display_name' => 'View Forms', 'description' => 'Can view forms', 'module' => 'forms'],
            ['name' => 'forms.create', 'display_name' => 'Create Forms', 'description' => 'Can create forms', 'module' => 'forms'],
            ['name' => 'forms.edit', 'display_name' => 'Edit Forms', 'description' => 'Can edit forms', 'module' => 'forms'],
            ['name' => 'forms.delete', 'display_name' => 'Delete Forms', 'description' => 'Can delete forms', 'module' => 'forms'],
            ['name' => 'forms.submissions', 'display_name' => 'View Form Submissions', 'description' => 'Can view form submissions', 'module' => 'forms'],

            // Products module (E-commerce)
            ['name' => 'products.view', 'display_name' => 'View Products', 'description' => 'Can view products', 'module' => 'products'],
            ['name' => 'products.create', 'display_name' => 'Create Products', 'description' => 'Can create products', 'module' => 'products'],
            ['name' => 'products.edit', 'display_name' => 'Edit Products', 'description' => 'Can edit products', 'module' => 'products'],
            ['name' => 'products.delete', 'display_name' => 'Delete Products', 'description' => 'Can delete products', 'module' => 'products'],

            // Orders module (E-commerce)
            ['name' => 'orders.view', 'display_name' => 'View Orders', 'description' => 'Can view orders', 'module' => 'orders'],
            ['name' => 'orders.edit', 'display_name' => 'Edit Orders', 'description' => 'Can edit orders', 'module' => 'orders'],
            ['name' => 'orders.delete', 'display_name' => 'Delete Orders', 'description' => 'Can delete orders', 'module' => 'orders'],

            // System module
            ['name' => 'system.backup', 'display_name' => 'System Backup', 'description' => 'Can create system backups', 'module' => 'system'],
            ['name' => 'system.logs', 'display_name' => 'View System Logs', 'description' => 'Can view system logs', 'module' => 'system'],
            ['name' => 'system.maintenance', 'display_name' => 'System Maintenance', 'description' => 'Can perform system maintenance', 'module' => 'system'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $this->command->info('Created ' . count($permissions) . ' permissions.');
    }

    /**
     * Create roles.
     */
    private function createRoles(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Admin',
                'description' => 'Full system access with all permissions',
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Administrative access to manage content and users',
            ],
            [
                'name' => 'editor',
                'display_name' => 'Editor',
                'description' => 'Can edit and publish content',
            ],
            [
                'name' => 'author',
                'display_name' => 'Author',
                'description' => 'Can create and edit own content',
            ],
            [
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'Basic user with limited access',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }

        $this->command->info('Created ' . count($roles) . ' roles.');
    }

    /**
     * Assign permissions to roles.
     */
    private function assignPermissionsToRoles(): void
    {
        // Super Admin - All permissions
        $superAdmin = Role::where('name', 'super_admin')->first();
        $allPermissions = Permission::all();
        $superAdmin->permissions()->sync($allPermissions->pluck('id'));

        // Admin - Most permissions except system critical ones
        $admin = Role::where('name', 'admin')->first();
        $adminPermissions = Permission::whereNotIn('name', [
            'system.maintenance',
            'users.delete', // Can't delete users
        ])->get();
        $admin->permissions()->sync($adminPermissions->pluck('id'));

        // Editor - Content management permissions
        $editor = Role::where('name', 'editor')->first();
        $editorPermissions = Permission::whereIn('module', [
            'posts', 'pages', 'categories', 'media', 'comments', 'menus'
        ])->get();
        $editor->permissions()->sync($editorPermissions->pluck('id'));

        // Author - Limited content permissions
        $author = Role::where('name', 'author')->first();
        $authorPermissions = Permission::whereIn('name', [
            'posts.view', 'posts.create', 'posts.edit_own',
            'categories.view', 'media.view', 'media.upload',
            'comments.view', 'comments.create'
        ])->get();
        $author->permissions()->sync($authorPermissions->pluck('id'));

        // User - Very limited permissions
        $user = Role::where('name', 'user')->first();
        $userPermissions = Permission::whereIn('name', [
            'comments.create', 'comments.view'
        ])->get();
        $user->permissions()->sync($userPermissions->pluck('id'));

        $this->command->info('Assigned permissions to roles.');
    }
}
