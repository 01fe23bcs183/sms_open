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
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('library_categories')->onDelete('cascade');
            $table->string('isbn', 20)->unique();
            $table->string('title', 255);
            $table->string('author', 255)->nullable();
            $table->string('publisher', 255)->nullable();
            $table->string('edition', 50)->nullable();
            $table->integer('publish_year')->nullable();
            $table->string('rack_number', 20)->nullable();
            $table->integer('quantity');
            $table->integer('available_quantity');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('language', 50)->nullable();
            $table->integer('pages')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('category_id');
            $table->index('isbn');
            $table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_books');
    }
};
