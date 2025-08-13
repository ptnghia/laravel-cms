<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Essential indexes for performance

        // Posts table - most critical indexes
        if (!$this->indexExists('posts', 'posts_status_published_index')) {
            \DB::statement('CREATE INDEX posts_status_published_index ON posts (status, published_at)');
        }

        if (!$this->indexExists('posts', 'posts_category_status_index')) {
            \DB::statement('CREATE INDEX posts_category_status_index ON posts (category_id, status)');
        }

        if (!$this->indexExists('posts', 'posts_author_status_index')) {
            \DB::statement('CREATE INDEX posts_author_status_index ON posts (author_id, status)');
        }

        // Categories table
        if (!$this->indexExists('categories', 'categories_active_sort_index')) {
            \DB::statement('CREATE INDEX categories_active_sort_index ON categories (is_active, sort_order)');
        }

        if (!$this->indexExists('categories', 'categories_parent_active_index')) {
            \DB::statement('CREATE INDEX categories_parent_active_index ON categories (parent_id, is_active)');
        }

        // Tags table
        if (!$this->indexExists('tags', 'tags_usage_count_index')) {
            \DB::statement('CREATE INDEX tags_usage_count_index ON tags (usage_count DESC)');
        }

        // Users table
        if (!$this->indexExists('users', 'users_status_created_index')) {
            \DB::statement('CREATE INDEX users_status_created_index ON users (status, created_at)');
        }

        // Activity logs table (check if columns exist)
        if ($this->columnExists('activity_logs', 'user_id') && !$this->indexExists('activity_logs', 'activity_logs_user_created_index')) {
            \DB::statement('CREATE INDEX activity_logs_user_created_index ON activity_logs (user_id, created_at)');
        }

        if ($this->columnExists('activity_logs', 'action') && !$this->indexExists('activity_logs', 'activity_logs_action_index')) {
            \DB::statement('CREATE INDEX activity_logs_action_index ON activity_logs (action, created_at)');
        }

        // Comments table
        if (!$this->indexExists('comments', 'comments_commentable_status_index')) {
            \DB::statement('CREATE INDEX comments_commentable_status_index ON comments (commentable_type, commentable_id, status)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes
        $indexes = [
            'posts' => [
                'posts_status_published_index',
                'posts_category_status_index',
                'posts_author_status_index'
            ],
            'categories' => [
                'categories_active_sort_index',
                'categories_parent_active_index'
            ],
            'tags' => [
                'tags_usage_count_index'
            ],
            'users' => [
                'users_status_created_index'
            ],
            'activity_logs' => [
                'activity_logs_user_created_index',
                'activity_logs_action_index'
            ],
            'comments' => [
                'comments_commentable_status_index'
            ]
        ];

        foreach ($indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $index) {
                if ($this->indexExists($table, $index)) {
                    \DB::statement("DROP INDEX {$index} ON {$table}");
                }
            }
        }
    }

    /**
     * Check if index exists on table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $driver = \DB::getDriverName();

            if ($driver === 'sqlite') {
                $indexes = \DB::select("PRAGMA index_list({$table})");
                foreach ($indexes as $index) {
                    if ($index->name === $indexName) {
                        return true;
                    }
                }
                return false;
            } else {
                $indexes = \DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
                return !empty($indexes);
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if column exists on table
     */
    private function columnExists(string $table, string $columnName): bool
    {
        try {
            $driver = \DB::getDriverName();

            if ($driver === 'sqlite') {
                $columns = \DB::select("PRAGMA table_info({$table})");
                foreach ($columns as $column) {
                    if ($column->name === $columnName) {
                        return true;
                    }
                }
                return false;
            } else {
                $columns = \DB::select("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$columnName]);
                return !empty($columns);
            }
        } catch (\Exception $e) {
            return false;
        }
    }
};
