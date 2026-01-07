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
        Schema::create('hostel_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('hostel_id')->constrained('hostels')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('hostel_rooms')->onDelete('cascade');
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->onDelete('cascade');
            $table->date('admission_date');
            $table->date('leaving_date')->nullable();
            $table->decimal('hostel_fees', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('student_id');
            $table->index('room_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_assignments');
    }
};
