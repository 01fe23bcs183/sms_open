<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Prompt 11: Extend Users Table with additional fields for all 6 user roles
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->string('first_name', 100)->after('uuid');
            $table->string('last_name', 100)->after('first_name');
            $table->string('phone', 20)->unique()->nullable()->after('email');
            $table->string('username', 50)->unique()->nullable()->after('phone');
            $table->string('avatar')->nullable()->after('password');
            $table->date('date_of_birth')->nullable()->after('avatar');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->text('address')->nullable()->after('gender');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('state', 100)->nullable()->after('city');
            $table->string('country', 100)->default('India')->after('state');
            $table->string('postal_code', 20)->nullable()->after('country');
            $table->boolean('is_active')->default(true)->after('postal_code');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->softDeletes();

            $table->index('phone');
            $table->index('username');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['phone']);
            $table->dropIndex(['username']);
            $table->dropIndex(['is_active']);
            $table->dropColumn([
                'uuid',
                'first_name',
                'last_name',
                'phone',
                'username',
                'avatar',
                'date_of_birth',
                'gender',
                'address',
                'city',
                'state',
                'country',
                'postal_code',
                'is_active',
                'last_login_at',
            ]);
        });
    }
};
