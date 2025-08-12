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
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('target')->nullable()->after('url'); // _blank, _self, etc.
            $table->string('icon')->nullable()->after('target'); // CSS icon class
            $table->string('css_class')->nullable()->after('icon'); // Custom CSS classes
            $table->boolean('is_active')->default(true)->after('sort_order');

            // Add index for is_active
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['target', 'icon', 'css_class', 'is_active']);
        });
    }
};
