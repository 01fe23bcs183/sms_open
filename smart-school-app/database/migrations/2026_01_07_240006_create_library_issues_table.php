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
        Schema::create('library_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('library_books')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('library_members')->onDelete('cascade');
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->boolean('fine_paid')->default(false);
            $table->text('remarks')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('returned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('book_id');
            $table->index('member_id');
            $table->index('issue_date');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_issues');
    }
};
