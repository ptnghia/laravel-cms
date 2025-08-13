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
        Schema::create('ai_content_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('content_type'); // post, page, product, etc.
            $table->unsignedBigInteger('content_id')->nullable();
            $table->string('suggestion_type'); // title, content, tags, seo, etc.
            $table->text('original_content')->nullable();
            $table->text('suggested_content');
            $table->json('ai_metadata')->nullable(); // AI model info, confidence score, etc.
            $table->enum('status', ['pending', 'accepted', 'rejected', 'applied'])->default('pending');
            $table->text('feedback')->nullable();
            $table->decimal('confidence_score', 3, 2)->nullable(); // 0.00 to 1.00
            $table->string('ai_model')->nullable(); // GPT-4, Claude, etc.
            $table->timestamps();

            $table->index(['content_type', 'content_id']);
            $table->index(['user_id', 'status']);
            $table->index(['suggestion_type', 'status']);
            $table->index('confidence_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_content_suggestions');
    }
};
