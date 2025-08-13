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
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('version');
            $table->string('author')->nullable();
            $table->string('author_email')->nullable();
            $table->string('author_url')->nullable();
            $table->string('plugin_url')->nullable();
            $table->text('requirements')->nullable(); // JSON string of requirements
            $table->json('config')->nullable(); // Plugin configuration
            $table->string('main_file'); // Main plugin file path
            $table->enum('status', ['active', 'inactive', 'installed', 'error'])->default('installed');
            $table->text('error_message')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->json('hooks')->nullable(); // Registered hooks/filters
            $table->integer('priority')->default(10);
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index('slug');
            $table->index('activated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};
