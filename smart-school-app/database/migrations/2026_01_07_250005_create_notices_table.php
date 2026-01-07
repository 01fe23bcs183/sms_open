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
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('content');
            $table->json('target_roles')->nullable();
            $table->json('target_classes')->nullable();
            $table->date('notice_date');
            $table->date('expiry_date')->nullable();
            $table->string('attachment', 255)->nullable();
            $table->boolean('is_published')->default(false);
            $table->foreignId('published_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('notice_date');
            $table->index('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
