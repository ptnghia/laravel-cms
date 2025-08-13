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
        Schema::create('workflow_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->onDelete('cascade');
            $table->foreignId('triggered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('trigger_event')->nullable(); // What triggered this execution
            $table->json('input_data')->nullable(); // Input data for the workflow
            $table->json('execution_log'); // Step-by-step execution log
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable(); // Execution duration
            $table->json('output_data')->nullable(); // Final output data
            $table->timestamps();

            $table->index(['workflow_id', 'status']);
            $table->index(['triggered_by', 'created_at']);
            $table->index(['status', 'started_at']);
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_executions');
    }
};
