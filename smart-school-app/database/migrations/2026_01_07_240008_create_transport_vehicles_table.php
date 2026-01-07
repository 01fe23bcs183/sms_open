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
        Schema::create('transport_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_number', 20)->unique();
            $table->string('vehicle_type', 50)->nullable();
            $table->string('vehicle_model', 100)->nullable();
            $table->integer('capacity');
            $table->string('driver_name', 100)->nullable();
            $table->string('driver_phone', 20)->nullable();
            $table->string('driver_license', 50)->nullable();
            $table->foreignId('route_id')->nullable()->constrained('transport_routes')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('route_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_vehicles');
    }
};
