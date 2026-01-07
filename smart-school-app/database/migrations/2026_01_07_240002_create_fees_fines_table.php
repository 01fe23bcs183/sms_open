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
        Schema::create('fees_fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fees_master_id')->constrained('fees_masters')->onDelete('cascade');
            $table->enum('fine_type', ['daily', 'weekly', 'monthly', 'one_time']);
            $table->decimal('fine_amount', 10, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees_fines');
    }
};
