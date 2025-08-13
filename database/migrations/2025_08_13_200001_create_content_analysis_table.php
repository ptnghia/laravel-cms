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
        Schema::create('content_analysis', function (Blueprint $table) {
            $table->id();
            $table->string('content_type'); // post, page, product, etc.
            $table->unsignedBigInteger('content_id');
            $table->string('analysis_type'); // sentiment, readability, seo, keywords, etc.
            $table->json('analysis_data'); // Detailed analysis results
            $table->decimal('score', 5, 2)->nullable(); // Overall score
            $table->json('metrics')->nullable(); // Specific metrics (word count, reading time, etc.)
            $table->json('suggestions')->nullable(); // Improvement suggestions
            $table->string('language', 10)->default('en');
            $table->string('ai_model')->nullable();
            $table->timestamp('analyzed_at');
            $table->timestamps();

            $table->index(['content_type', 'content_id']);
            $table->index(['analysis_type', 'score']);
            $table->index('analyzed_at');
            $table->index('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_analysis');
    }
};
