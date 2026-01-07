<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Prompt 23: Create Students Table Migration
     * Purpose: Store all student information and academic records (40+ fields)
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            
            // User Link
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Academic Info
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->onDelete('cascade');
            $table->string('admission_number', 50)->unique();
            $table->string('roll_number', 20)->nullable();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->date('date_of_admission');
            
            // Personal Info
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('blood_group', 5)->nullable();
            $table->string('religion', 50)->nullable();
            $table->string('caste', 50)->nullable();
            $table->string('nationality', 50)->default('Indian');
            $table->string('mother_tongue', 50)->nullable();
            
            // Family Info - Father
            $table->string('father_name', 100)->nullable();
            $table->string('father_phone', 20)->nullable();
            $table->string('father_occupation', 100)->nullable();
            $table->string('father_email', 100)->nullable();
            $table->string('father_qualification', 100)->nullable();
            $table->decimal('father_annual_income', 12, 2)->nullable();
            
            // Family Info - Mother
            $table->string('mother_name', 100)->nullable();
            $table->string('mother_phone', 20)->nullable();
            $table->string('mother_occupation', 100)->nullable();
            $table->string('mother_email', 100)->nullable();
            $table->string('mother_qualification', 100)->nullable();
            $table->decimal('mother_annual_income', 12, 2)->nullable();
            
            // Family Info - Guardian
            $table->string('guardian_name', 100)->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->string('guardian_relation', 50)->nullable();
            $table->string('guardian_occupation', 100)->nullable();
            $table->string('guardian_email', 100)->nullable();
            $table->text('guardian_address')->nullable();
            
            // Address - Current
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->default('India');
            $table->string('postal_code', 20)->nullable();
            
            // Address - Permanent
            $table->text('permanent_address')->nullable();
            $table->string('permanent_city', 100)->nullable();
            $table->string('permanent_state', 100)->nullable();
            $table->string('permanent_country', 100)->default('India');
            $table->string('permanent_postal_code', 20)->nullable();
            
            // Previous School Info
            $table->string('previous_school_name', 255)->nullable();
            $table->string('previous_school_address', 255)->nullable();
            $table->string('previous_class', 50)->nullable();
            $table->string('transfer_certificate_number', 50)->nullable();
            $table->date('transfer_certificate_date')->nullable();
            
            // Admission Info
            $table->boolean('is_rte')->default(false);
            $table->enum('admission_type', ['new', 'transfer', 'readmission'])->default('new');
            $table->foreignId('category_id')->nullable()->constrained('student_categories')->onDelete('set null');
            
            // Emergency Contact
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relation', 50)->nullable();
            
            // Health Info
            $table->text('medical_notes')->nullable();
            $table->text('allergies')->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->text('identification_marks')->nullable();
            
            // Bank Details
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_ifsc_code', 20)->nullable();
            
            // Documents
            $table->string('photo', 255)->nullable();
            $table->string('birth_certificate', 255)->nullable();
            $table->string('aadhar_number', 20)->nullable();
            
            // System Fields
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('user_id');
            $table->index('academic_session_id');
            $table->index('class_id');
            $table->index('section_id');
            $table->index('admission_number');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
