<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('appliances', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('appliances', function (Blueprint $table) {
            $table->string('owner_type')->default('App\\Models\\User');
        });

        Schema::table('appliances', function (Blueprint $table) {
            $table->renameColumn('user_id', 'owner_id');
        });
    }

    public function down(): void
    {
        Schema::table('appliances', function (Blueprint $table) {
            $table->renameColumn('owner_id', 'user_id');
        });

        Schema::table('appliances', function (Blueprint $table) {
            $table->dropColumn('owner_type');
        });

        Schema::table('appliances', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
