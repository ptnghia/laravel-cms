<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Laravel CMS Database Seeding...');

        // Run seeders in correct order (respecting dependencies)
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            SystemSeeder::class,
            ContentSeeder::class,
        ]);

        $this->command->info('🎉 Laravel CMS Database Seeding Completed!');
        $this->command->info('');
        $this->command->info('📋 Summary:');
        $this->command->info('✅ Roles and Permissions created');
        $this->command->info('✅ Admin users created');
        $this->command->info('✅ System settings configured');
        $this->command->info('✅ Content (categories, tags, posts, pages) created');
        $this->command->info('✅ Comments and relationships established');
        $this->command->info('');
        $this->command->info('🔑 Login Credentials:');
        $this->command->info('Super Admin: admin@laravel-cms.com / password');
        $this->command->info('Admin: admin2@laravel-cms.com / password');
        $this->command->info('Editor: editor@laravel-cms.com / password');
        $this->command->info('Author: john@laravel-cms.com / password');
        $this->command->info('Author: jane@laravel-cms.com / password');
    }
}
