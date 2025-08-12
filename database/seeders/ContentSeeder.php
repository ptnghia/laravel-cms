<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories first
        $this->createCategories();

        // Create tags
        $this->createTags();

        // Create pages
        $this->createPages();

        // Create posts with relationships
        $this->createPosts();

        // Create comments
        $this->createComments();
    }

    /**
     * Create categories with parent-child relationships.
     */
    private function createCategories(): void
    {
        // Create root categories
        $rootCategories = [
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Latest technology news and trends'],
            ['name' => 'Business', 'slug' => 'business', 'description' => 'Business insights and strategies'],
            ['name' => 'Lifestyle', 'slug' => 'lifestyle', 'description' => 'Lifestyle tips and inspiration'],
            ['name' => 'Travel', 'slug' => 'travel', 'description' => 'Travel guides and experiences'],
            ['name' => 'Health', 'slug' => 'health', 'description' => 'Health and wellness articles'],
        ];

        $createdRootCategories = [];
        foreach ($rootCategories as $categoryData) {
            $category = Category::factory()->active()->create($categoryData);
            $createdRootCategories[] = $category;
        }

        // Create child categories
        $childCategories = [
            ['parent' => 'Technology', 'name' => 'Web Development', 'slug' => 'web-development'],
            ['parent' => 'Technology', 'name' => 'Mobile Apps', 'slug' => 'mobile-apps'],
            ['parent' => 'Technology', 'name' => 'AI & Machine Learning', 'slug' => 'ai-machine-learning'],
            ['parent' => 'Business', 'name' => 'Startups', 'slug' => 'startups'],
            ['parent' => 'Business', 'name' => 'Marketing', 'slug' => 'marketing'],
            ['parent' => 'Business', 'name' => 'Finance', 'slug' => 'finance'],
            ['parent' => 'Lifestyle', 'name' => 'Food & Cooking', 'slug' => 'food-cooking'],
            ['parent' => 'Lifestyle', 'name' => 'Fashion', 'slug' => 'fashion'],
            ['parent' => 'Travel', 'name' => 'Europe', 'slug' => 'europe'],
            ['parent' => 'Travel', 'name' => 'Asia', 'slug' => 'asia'],
        ];

        foreach ($childCategories as $childData) {
            $parentCategory = collect($createdRootCategories)->firstWhere('name', $childData['parent']);
            if ($parentCategory) {
                Category::factory()->active()->create([
                    'name' => $childData['name'],
                    'slug' => $childData['slug'],
                    'parent_id' => $parentCategory->id,
                    'description' => "Articles about {$childData['name']}",
                ]);
            }
        }

        // Create some additional random categories
        Category::factory(5)->active()->create();

        $this->command->info('Created categories with parent-child relationships.');
    }

    /**
     * Create tags.
     */
    private function createTags(): void
    {
        // Create specific tags
        $specificTags = [
            'Laravel', 'PHP', 'JavaScript', 'Vue.js', 'React', 'Node.js',
            'SEO', 'Marketing', 'Social Media', 'Content Strategy',
            'Productivity', 'Remote Work', 'Entrepreneurship',
            'Travel Tips', 'Budget Travel', 'Adventure',
            'Fitness', 'Nutrition', 'Mental Health', 'Wellness'
        ];

        foreach ($specificTags as $tagName) {
            Tag::factory()->create([
                'name' => $tagName,
                'slug' => \Str::slug($tagName),
                'usage_count' => rand(5, 50),
            ]);
        }

        // Create additional random tags
        Tag::factory(20)->create();

        $this->command->info('Created tags.');
    }

    /**
     * Create pages.
     */
    private function createPages(): void
    {
        // Create main pages
        $mainPages = [
            [
                'title' => 'Home',
                'slug' => 'home',
                'content' => '<h1>Welcome to Laravel CMS</h1><p>This is a powerful content management system built with Laravel.</p>',
                'template' => 'landing',
                'status' => 'published',
                'is_active' => true,
            ],
            [
                'title' => 'About Us',
                'slug' => 'about',
                'content' => '<h1>About Us</h1><p>Learn more about our company and mission.</p>',
                'template' => 'default',
                'status' => 'published',
                'is_active' => true,
            ],
            [
                'title' => 'Contact',
                'slug' => 'contact',
                'content' => '<h1>Contact Us</h1><p>Get in touch with our team.</p>',
                'template' => 'contact',
                'status' => 'published',
                'is_active' => true,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<h1>Privacy Policy</h1><p>Our privacy policy and data handling practices.</p>',
                'template' => 'default',
                'status' => 'published',
                'is_active' => true,
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => '<h1>Terms of Service</h1><p>Terms and conditions for using our services.</p>',
                'template' => 'default',
                'status' => 'published',
                'is_active' => true,
            ],
        ];

        foreach ($mainPages as $pageData) {
            Page::factory()->create($pageData);
        }

        // Create additional random pages
        Page::factory(10)->published()->create();

        $this->command->info('Created pages.');
    }

    /**
     * Create posts with relationships.
     */
    private function createPosts(): void
    {
        $categories = Category::all();
        $tags = Tag::all();
        $authors = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['author', 'editor', 'admin', 'super_admin']);
        })->get();

        // Create published posts
        $publishedPosts = Post::factory(50)
            ->published()
            ->create()
            ->each(function ($post) use ($categories, $tags, $authors) {
                // Assign random category
                if ($categories->isNotEmpty()) {
                    $post->category_id = $categories->random()->id;
                    $post->save();
                }

                // Assign random author
                if ($authors->isNotEmpty()) {
                    $post->author_id = $authors->random()->id;
                    $post->save();
                }

                // Attach random tags (1-5 tags per post)
                if ($tags->isNotEmpty()) {
                    $randomTags = $tags->random(rand(1, min(5, $tags->count())));
                    $post->tags()->attach($randomTags->pluck('id'));

                    // Update tag usage counts
                    foreach ($randomTags as $tag) {
                        $tag->increment('usage_count');
                    }
                }
            });

        // Create draft posts
        Post::factory(15)
            ->draft()
            ->create()
            ->each(function ($post) use ($categories, $authors) {
                if ($categories->isNotEmpty()) {
                    $post->category_id = $categories->random()->id;
                    $post->save();
                }
                if ($authors->isNotEmpty()) {
                    $post->author_id = $authors->random()->id;
                    $post->save();
                }
            });

        // Create scheduled posts
        Post::factory(5)
            ->scheduled()
            ->create()
            ->each(function ($post) use ($categories, $authors) {
                if ($categories->isNotEmpty()) {
                    $post->category_id = $categories->random()->id;
                    $post->save();
                }
                if ($authors->isNotEmpty()) {
                    $post->author_id = $authors->random()->id;
                    $post->save();
                }
            });

        // Create some featured posts
        Post::factory(10)
            ->published()
            ->featured()
            ->popular()
            ->create()
            ->each(function ($post) use ($categories, $tags, $authors) {
                if ($categories->isNotEmpty()) {
                    $post->category_id = $categories->random()->id;
                    $post->save();
                }
                if ($authors->isNotEmpty()) {
                    $post->author_id = $authors->random()->id;
                    $post->save();
                }
                if ($tags->isNotEmpty()) {
                    $randomTags = $tags->random(rand(2, min(6, $tags->count())));
                    $post->tags()->attach($randomTags->pluck('id'));
                }
            });

        $this->command->info('Created posts with categories, tags, and authors.');
    }

    /**
     * Create comments for posts and pages.
     */
    private function createComments(): void
    {
        $posts = Post::published()->get();
        $pages = Page::published()->get();
        $users = User::all();

        // Create comments for posts
        foreach ($posts->take(30) as $post) {
            // Create root comments
            $rootComments = Comment::factory(rand(1, 5))
                ->approved()
                ->create([
                    'commentable_id' => $post->id,
                    'commentable_type' => Post::class,
                    'user_id' => $users->random()->id,
                ]);

            // Create replies to some comments
            foreach ($rootComments->take(2) as $rootComment) {
                Comment::factory(rand(0, 3))
                    ->approved()
                    ->create([
                        'commentable_id' => $post->id,
                        'commentable_type' => Post::class,
                        'parent_id' => $rootComment->id,
                        'user_id' => $users->random()->id,
                    ]);
            }
        }

        // Create some guest comments
        foreach ($posts->take(10) as $post) {
            Comment::factory(rand(1, 2))
                ->guest()
                ->approved()
                ->create([
                    'commentable_id' => $post->id,
                    'commentable_type' => Post::class,
                ]);
        }

        // Create some pending comments
        foreach ($posts->take(5) as $post) {
            Comment::factory(rand(1, 2))
                ->pending()
                ->create([
                    'commentable_id' => $post->id,
                    'commentable_type' => Post::class,
                    'user_id' => $users->random()->id,
                ]);
        }

        $this->command->info('Created comments with replies.');
    }
}
