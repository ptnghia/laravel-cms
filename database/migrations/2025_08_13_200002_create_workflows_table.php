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
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_type'); // manual, automatic, scheduled, event
            $table->json('trigger_conditions')->nullable(); // Conditions for automatic triggers
            $table->json('workflow_steps'); // Array of workflow steps
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->integer('execution_count')->default(0);
            $table->timestamp('last_executed_at')->nullable();
            $table->json('settings')->nullable(); // Additional workflow settings
            $table->timestamps();

            $table->index(['status', 'trigger_type']);
            $table->index('created_by');
            $table->index('last_executed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
