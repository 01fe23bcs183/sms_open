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
        Schema::create('hostel_room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained('hostels')->onDelete('cascade');
            $table->string('name', 100);
            $table->integer('capacity');
            $table->integer('beds_per_room');
            $table->decimal('fees_per_month', 10, 2)->nullable();
            $table->text('facilities')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('hostel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_room_types');
    }
};
