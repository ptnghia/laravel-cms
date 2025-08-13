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
        Schema::create('plugin_installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->onDelete('cascade');
            $table->foreignId('installed_by')->constrained('users')->onDelete('cascade');
            $table->string('installation_method'); // manual, marketplace, upload, git
            $table->string('source_url')->nullable(); // Where plugin was downloaded from
            $table->string('installation_path'); // Where plugin files are stored
            $table->json('installation_log')->nullable(); // Installation process log
            $table->enum('status', ['installing', 'installed', 'failed', 'uninstalling', 'uninstalled'])->default('installing');
            $table->text('error_message')->nullable();
            $table->json('backup_data')->nullable(); // Backup data before installation
            $table->timestamp('installation_started_at');
            $table->timestamp('installation_completed_at')->nullable();
            $table->timestamp('uninstalled_at')->nullable();
            $table->timestamps();

            $table->index(['plugin_id', 'status']);
            $table->index(['installed_by', 'created_at']);
            $table->index('installation_completed_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_installations');
    }
};
