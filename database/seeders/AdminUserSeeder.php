<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@laravel-cms.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
                'bio' => 'System administrator with full access to all features.',
                'last_login_at' => now(),
            ]
        );

        // Assign Super Admin role
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole && !$superAdmin->hasRole('super_admin')) {
            $superAdmin->roles()->attach($superAdminRole->id);
        }

        // Create Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin2@laravel-cms.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
                'bio' => 'Administrator with content management access.',
                'last_login_at' => now()->subDays(1),
            ]
        );

        // Assign Admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$admin->hasRole('admin')) {
            $admin->roles()->attach($adminRole->id);
        }

        // Create Editor user
        $editor = User::firstOrCreate(
            ['email' => 'editor@laravel-cms.com'],
            [
                'name' => 'Editor User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
                'bio' => 'Content editor responsible for reviewing and publishing articles.',
                'last_login_at' => now()->subDays(2),
            ]
        );

        // Assign Editor role
        $editorRole = Role::where('name', 'editor')->first();
        if ($editorRole && !$editor->hasRole('editor')) {
            $editor->roles()->attach($editorRole->id);
        }

        // Create Author users
        $authors = [
            [
                'name' => 'John Author',
                'email' => 'john@laravel-cms.com',
                'bio' => 'Freelance writer specializing in technology and business topics.',
            ],
            [
                'name' => 'Jane Writer',
                'email' => 'jane@laravel-cms.com',
                'bio' => 'Content creator with expertise in digital marketing and SEO.',
            ],
        ];

        $authorRole = Role::where('name', 'author')->first();

        foreach ($authors as $authorData) {
            $author = User::firstOrCreate(
                ['email' => $authorData['email']],
                [
                    'name' => $authorData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'status' => 'active',
                    'bio' => $authorData['bio'],
                    'last_login_at' => now()->subDays(rand(1, 7)),
                ]
            );

            if ($authorRole && !$author->hasRole('author')) {
                $author->roles()->attach($authorRole->id);
            }
        }

        // Create demo users with User role
        $userRole = Role::where('name', 'user')->first();

        User::factory(10)->create()->each(function ($user) use ($userRole) {
            if ($userRole && !$user->hasRole('user')) {
                $user->roles()->attach($userRole->id);
            }
        });

        $this->command->info('Created admin users and demo users with appropriate roles.');
        $this->command->info('Login credentials:');
        $this->command->info('Super Admin: admin@laravel-cms.com / password');
        $this->command->info('Admin: admin2@laravel-cms.com / password');
        $this->command->info('Editor: editor@laravel-cms.com / password');
        $this->command->info('Author: john@laravel-cms.com / password');
        $this->command->info('Author: jane@laravel-cms.com / password');
    }
}
