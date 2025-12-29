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
        if (Schema::hasColumn('hardware', 'provider_id')) {
            Schema::table('hardware', function (Blueprint $table) {
                try {
                    // Some DB engines auto-drop foreign keys when dropping columns,
                    // and constraint names may vary. Attempt to drop the column
                    // directly and ignore any errors to keep the migration idempotent.
                    $table->dropColumn('provider_id');
                } catch (\Throwable $e) {
                    // ignore any errors during drop (e.g., missing constraint)
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hardware', function (Blueprint $table) {
            $table->unsignedBigInteger('provider_id')->nullable()->after('id');
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('set null');
        });
    }
};
