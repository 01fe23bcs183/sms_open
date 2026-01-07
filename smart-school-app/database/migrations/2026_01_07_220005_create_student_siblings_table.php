<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Prompt 24: Create Student Siblings Table Migration
     * Purpose: Manage sibling relationships between students
     */
    public function up(): void
    {
        Schema::create('student_siblings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('sibling_id')->constrained('students')->onDelete('cascade');
            $table->enum('relation', ['brother', 'sister']);
            $table->timestamps();
            
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_siblings');
    }
};
