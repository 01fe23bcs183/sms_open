<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Prompt 28: Create Attendance Types Table Migration
     * Purpose: Define attendance types (present, absent, late, etc.)
     */
    public function up(): void
    {
        Schema::create('attendance_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('code', 10)->unique();
            $table->string('color', 7)->default('#000000');
            $table->boolean('is_present')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_types');
    }
};
