<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\Language;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create system settings
        $this->createSettings();

        // Create languages
        $this->createLanguages();

        // Create email templates
        $this->createEmailTemplates();

        // Create menus
        $this->createMenus();
    }

    /**
     * Create system settings.
     */
    private function createSettings(): void
    {
        $settings = [
            // General settings
            ['key' => 'site_name', 'value' => 'Laravel CMS', 'type' => 'string', 'group' => 'general', 'description' => 'Website name', 'is_public' => true],
            ['key' => 'site_description', 'value' => 'A powerful content management system built with Laravel', 'type' => 'string', 'group' => 'general', 'description' => 'Website description', 'is_public' => true],
            ['key' => 'site_keywords', 'value' => 'laravel, cms, content management, php', 'type' => 'string', 'group' => 'general', 'description' => 'Website keywords', 'is_public' => true],
            ['key' => 'site_logo', 'value' => '/images/logo.png', 'type' => 'string', 'group' => 'general', 'description' => 'Website logo path', 'is_public' => true],
            ['key' => 'site_favicon', 'value' => '/images/favicon.ico', 'type' => 'string', 'group' => 'general', 'description' => 'Website favicon path', 'is_public' => true],
            ['key' => 'timezone', 'value' => 'UTC', 'type' => 'string', 'group' => 'general', 'description' => 'Default timezone', 'is_public' => false],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'string', 'group' => 'general', 'description' => 'Date format', 'is_public' => true],
            ['key' => 'time_format', 'value' => 'H:i:s', 'type' => 'string', 'group' => 'general', 'description' => 'Time format', 'is_public' => true],

            // Content settings
            ['key' => 'posts_per_page', 'value' => '10', 'type' => 'integer', 'group' => 'content', 'description' => 'Number of posts per page', 'is_public' => true],
            ['key' => 'excerpt_length', 'value' => '150', 'type' => 'integer', 'group' => 'content', 'description' => 'Default excerpt length', 'is_public' => true],
            ['key' => 'allow_comments', 'value' => '1', 'type' => 'boolean', 'group' => 'content', 'description' => 'Allow comments on posts', 'is_public' => true],
            ['key' => 'moderate_comments', 'value' => '1', 'type' => 'boolean', 'group' => 'content', 'description' => 'Moderate comments before publishing', 'is_public' => false],
            ['key' => 'auto_publish_comments', 'value' => '0', 'type' => 'boolean', 'group' => 'content', 'description' => 'Auto publish comments from registered users', 'is_public' => false],

            // SEO settings
            ['key' => 'seo_title_separator', 'value' => ' | ', 'type' => 'string', 'group' => 'seo', 'description' => 'SEO title separator', 'is_public' => true],
            ['key' => 'seo_meta_description', 'value' => 'Laravel CMS - A powerful content management system', 'type' => 'string', 'group' => 'seo', 'description' => 'Default meta description', 'is_public' => true],
            ['key' => 'google_analytics_id', 'value' => '', 'type' => 'string', 'group' => 'seo', 'description' => 'Google Analytics tracking ID', 'is_public' => false],
            ['key' => 'google_search_console', 'value' => '', 'type' => 'string', 'group' => 'seo', 'description' => 'Google Search Console verification code', 'is_public' => false],

            // Email settings
            ['key' => 'mail_from_name', 'value' => 'Laravel CMS', 'type' => 'string', 'group' => 'email', 'description' => 'Default sender name', 'is_public' => false],
            ['key' => 'mail_from_address', 'value' => 'noreply@laravel-cms.com', 'type' => 'string', 'group' => 'email', 'description' => 'Default sender email', 'is_public' => false],
            ['key' => 'admin_email', 'value' => 'admin@laravel-cms.com', 'type' => 'string', 'group' => 'email', 'description' => 'Admin email address', 'is_public' => false],

            // Social media settings
            ['key' => 'facebook_url', 'value' => '', 'type' => 'string', 'group' => 'social', 'description' => 'Facebook page URL', 'is_public' => true],
            ['key' => 'twitter_url', 'value' => '', 'type' => 'string', 'group' => 'social', 'description' => 'Twitter profile URL', 'is_public' => true],
            ['key' => 'instagram_url', 'value' => '', 'type' => 'string', 'group' => 'social', 'description' => 'Instagram profile URL', 'is_public' => true],
            ['key' => 'linkedin_url', 'value' => '', 'type' => 'string', 'group' => 'social', 'description' => 'LinkedIn profile URL', 'is_public' => true],

            // Media settings
            ['key' => 'max_upload_size', 'value' => '10240', 'type' => 'integer', 'group' => 'media', 'description' => 'Maximum upload size in KB', 'is_public' => false],
            ['key' => 'allowed_file_types', 'value' => 'jpg,jpeg,png,gif,pdf,doc,docx', 'type' => 'string', 'group' => 'media', 'description' => 'Allowed file extensions', 'is_public' => false],
            ['key' => 'image_quality', 'value' => '85', 'type' => 'integer', 'group' => 'media', 'description' => 'Image compression quality', 'is_public' => false],

            // Security settings
            ['key' => 'enable_registration', 'value' => '1', 'type' => 'boolean', 'group' => 'security', 'description' => 'Allow user registration', 'is_public' => true],
            ['key' => 'require_email_verification', 'value' => '1', 'type' => 'boolean', 'group' => 'security', 'description' => 'Require email verification', 'is_public' => false],
            ['key' => 'login_attempts', 'value' => '5', 'type' => 'integer', 'group' => 'security', 'description' => 'Maximum login attempts', 'is_public' => false],
            ['key' => 'lockout_duration', 'value' => '15', 'type' => 'integer', 'group' => 'security', 'description' => 'Lockout duration in minutes', 'is_public' => false],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Created ' . count($settings) . ' system settings.');
    }

    /**
     * Create languages.
     */
    private function createLanguages(): void
    {
        $languages = [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_active' => true, 'is_default' => true],
            ['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'is_active' => true, 'is_default' => false],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'is_active' => false, 'is_default' => false],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'is_active' => false, 'is_default' => false],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'is_active' => false, 'is_default' => false],
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語', 'is_active' => false, 'is_default' => false],
            ['code' => 'ko', 'name' => 'Korean', 'native_name' => '한국어', 'is_active' => false, 'is_default' => false],
            ['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文', 'is_active' => false, 'is_default' => false],
        ];

        foreach ($languages as $language) {
            Language::firstOrCreate(
                ['code' => $language['code']],
                $language
            );
        }

        $this->command->info('Created ' . count($languages) . ' languages.');
    }

    /**
     * Create email templates.
     */
    private function createEmailTemplates(): void
    {
        $templates = [
            [
                'name' => 'welcome_email',
                'subject' => 'Welcome to {{site_name}}!',
                'content' => "Hello {{user_name}},\n\nWelcome to {{site_name}}! We're excited to have you on board.\n\nYour account has been successfully created. You can now log in and start exploring our features.\n\nIf you have any questions, feel free to contact our support team.\n\nBest regards,\nThe {{site_name}} Team"
            ],
            [
                'name' => 'password_reset',
                'subject' => 'Reset Your Password - {{site_name}}',
                'content' => "Hello {{user_name}},\n\nYou requested a password reset for your account. Click the link below to reset your password:\n\n{{reset_link}}\n\nThis link will expire in 60 minutes for security reasons.\n\nIf you didn't request this password reset, please ignore this email.\n\nBest regards,\nThe {{site_name}} Team"
            ],
            [
                'name' => 'email_verification',
                'subject' => 'Verify Your Email Address - {{site_name}}',
                'content' => "Hello {{user_name}},\n\nPlease verify your email address by clicking the link below:\n\n{{verification_link}}\n\nIf you didn't create an account, please ignore this email.\n\nBest regards,\nThe {{site_name}} Team"
            ],
            [
                'name' => 'new_comment',
                'subject' => 'New Comment on "{{post_title}}"',
                'content' => "Hello {{author_name}},\n\nA new comment has been posted on your article \"{{post_title}}\".\n\nComment by: {{commenter_name}}\nComment: {{comment_content}}\n\nYou can view and moderate the comment here: {{post_url}}\n\nBest regards,\nThe {{site_name}} Team"
            ],
            [
                'name' => 'comment_approved',
                'subject' => 'Your Comment Has Been Approved',
                'content' => "Hello {{commenter_name}},\n\nYour comment on \"{{post_title}}\" has been approved and is now visible to other readers.\n\nView your comment: {{post_url}}\n\nThank you for contributing to our community!\n\nBest regards,\nThe {{site_name}} Team"
            ],
            [
                'name' => 'newsletter',
                'subject' => '{{newsletter_title}} - {{site_name}}',
                'content' => "Hello {{subscriber_name}},\n\n{{newsletter_content}}\n\nTo unsubscribe from our newsletter, click here: {{unsubscribe_link}}\n\nBest regards,\nThe {{site_name}} Team"
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::firstOrCreate(
                ['name' => $template['name']],
                $template
            );
        }

        $this->command->info('Created ' . count($templates) . ' email templates.');
    }

    /**
     * Create menus and menu items.
     */
    private function createMenus(): void
    {
        // Create main header menu
        $headerMenu = Menu::firstOrCreate(
            ['location' => 'header'],
            [
                'name' => 'Header Menu',
                'location' => 'header',
                'is_active' => true,
            ]
        );

        // Create header menu items
        $headerMenuItems = [
            ['title' => 'Home', 'url' => '/', 'sort_order' => 0],
            ['title' => 'About', 'url' => '/about', 'sort_order' => 10],
            ['title' => 'Blog', 'url' => '/blog', 'sort_order' => 20],
            ['title' => 'Contact', 'url' => '/contact', 'sort_order' => 30],
        ];

        foreach ($headerMenuItems as $itemData) {
            MenuItem::firstOrCreate(
                ['menu_id' => $headerMenu->id, 'title' => $itemData['title']],
                array_merge($itemData, ['menu_id' => $headerMenu->id])
            );
        }

        // Create footer menu
        $footerMenu = Menu::firstOrCreate(
            ['location' => 'footer'],
            [
                'name' => 'Footer Menu',
                'location' => 'footer',
                'is_active' => true,
            ]
        );

        // Create footer menu items
        $footerMenuItems = [
            ['title' => 'Privacy Policy', 'url' => '/privacy-policy', 'sort_order' => 0],
            ['title' => 'Terms of Service', 'url' => '/terms-of-service', 'sort_order' => 10],
            ['title' => 'Contact', 'url' => '/contact', 'sort_order' => 20],
            ['title' => 'Sitemap', 'url' => '/sitemap', 'sort_order' => 30],
        ];

        foreach ($footerMenuItems as $itemData) {
            MenuItem::firstOrCreate(
                ['menu_id' => $footerMenu->id, 'title' => $itemData['title']],
                array_merge($itemData, ['menu_id' => $footerMenu->id])
            );
        }

        // Create sidebar menu
        $sidebarMenu = Menu::firstOrCreate(
            ['location' => 'sidebar'],
            [
                'name' => 'Sidebar Menu',
                'location' => 'sidebar',
                'is_active' => true,
            ]
        );

        // Create sidebar menu items
        $sidebarMenuItems = [
            ['title' => 'Latest Posts', 'url' => '/blog', 'sort_order' => 0],
            ['title' => 'Categories', 'url' => '/categories', 'sort_order' => 10],
            ['title' => 'Archives', 'url' => '/archives', 'sort_order' => 20],
        ];

        foreach ($sidebarMenuItems as $itemData) {
            MenuItem::firstOrCreate(
                ['menu_id' => $sidebarMenu->id, 'title' => $itemData['title']],
                array_merge($itemData, ['menu_id' => $sidebarMenu->id])
            );
        }

        $this->command->info('Created menus and menu items.');
    }
}
