<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Prompt 13: Extend Permissions Table with display_name, module, and description fields
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->string('display_name', 255)->nullable()->after('name');
            $table->string('module', 50)->nullable()->after('display_name');
            $table->text('description')->nullable()->after('module');

            $table->index('module');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->dropIndex(['module']);
            $table->dropColumn(['display_name', 'module', 'description']);
        });
    }
};
