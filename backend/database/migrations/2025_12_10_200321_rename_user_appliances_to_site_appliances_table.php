<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // Drop foreign key BEFORE renaming table (constraint name is based on old table name)
        Schema::table('user_appliances', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        // Rename table
        Schema::rename('user_appliances', 'site_appliances');

        // Add the new column
        Schema::table('site_appliances', function (Blueprint $table) {
            $table->string('added_by_type')->default('App\\Models\\User');
        });

        // Rename column
        Schema::table('site_appliances', function (Blueprint $table) {
            $table->renameColumn('user_id', 'added_by_id');
        });
    }

    public function down(): void
    {
        // Rename column back
        Schema::table('site_appliances', function (Blueprint $table) {
            $table->renameColumn('added_by_id', 'user_id');
        });

        // Remove polymorphic type column
        Schema::table('site_appliances', function (Blueprint $table) {
            $table->dropColumn('added_by_type');
        });

        // Rename table back
        Schema::rename('site_appliances', 'user_appliances');

        // Re-add foreign key AFTER renaming table back
        Schema::table('user_appliances', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
