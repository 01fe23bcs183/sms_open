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
        Schema::create('fees_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('fees_allotment_id')->constrained('fees_allotments')->onDelete('cascade');
            $table->string('transaction_id', 100)->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'cheque', 'dd', 'online']);
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->date('payment_date');
            $table->timestamp('transaction_date')->useCurrent();
            $table->string('reference_number', 100)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('cheque_number', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('student_id');
            $table->index('transaction_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees_transactions');
    }
};
