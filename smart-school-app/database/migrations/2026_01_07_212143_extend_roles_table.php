<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Prompt 12: Extend Roles Table with display_name, description, and is_active fields
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->string('display_name', 100)->nullable()->after('name');
            $table->text('description')->nullable()->after('display_name');
            $table->boolean('is_active')->default(true)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->dropColumn(['display_name', 'description', 'is_active']);
        });
    }
};
