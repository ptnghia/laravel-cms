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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('notifiable_id');
            $table->string('notifiable_type');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index('type');
            $table->index('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
