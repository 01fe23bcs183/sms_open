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
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained('hostels')->onDelete('cascade');
            $table->foreignId('room_type_id')->constrained('hostel_room_types')->onDelete('cascade');
            $table->string('room_number', 20);
            $table->integer('floor_number')->nullable();
            $table->integer('capacity');
            $table->integer('occupied')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('hostel_id');
            $table->unique(['hostel_id', 'room_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_rooms');
    }
};
