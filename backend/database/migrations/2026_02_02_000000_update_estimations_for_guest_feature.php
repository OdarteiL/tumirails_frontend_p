<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('estimations', function (Blueprint $table) {
            // Make ownership and site nullable
            $table->unsignedBigInteger('owner_id')->nullable()->change();
            $table->string('owner_type')->nullable()->change();
            $table->foreignId('site_id')->nullable()->change();
            $table->foreignId('created_by')->nullable()->change();

            // Add guest estimation fields
            $table->string('reference_code')->nullable()->unique();
            $table->timestamp('expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estimations', function (Blueprint $table) {
            // Revert guest estimation fields
            $table->dropColumn('reference_code');
            $table->dropColumn('expires_at');

            // Revert ownership and site to not nullable
            // Note: This assumes there are no guest estimations in the table.
            // A more robust down migration would handle this case.
            $table->unsignedBigInteger('owner_id')->nullable(false)->change();
            $table->string('owner_type')->nullable(false)->change();
            $table->foreignId('site_id')->nullable(false)->change();
            $table->foreignId('created_by')->nullable(false)->change();
        });
    }
};
