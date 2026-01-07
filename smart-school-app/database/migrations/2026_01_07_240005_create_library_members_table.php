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
        Schema::create('library_members', function (Blueprint $table) {
            $table->id();
            $table->enum('member_type', ['student', 'teacher', 'staff']);
            $table->unsignedBigInteger('member_id');
            $table->string('membership_number', 50)->unique();
            $table->date('membership_date');
            $table->date('expiry_date')->nullable();
            $table->integer('max_books')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['member_type', 'member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_members');
    }
};
