<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, make owner_id and owner_type nullable for public catalog items
        Schema::table('appliances', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->nullable()->change();
            $table->string('owner_type')->nullable()->change();
        });

        // Add new columns
        Schema::table('appliances', function (Blueprint $table) {
            $table->decimal('default_usage_hours', 5, 2)->nullable()->after('default_wattage');
            $table->json('metadata')->nullable()->after('default_usage_hours');
            $table->boolean('is_public')->default(false)->after('metadata');
            $table->boolean('is_active')->default(true)->after('is_public');

            $table->index(['is_public', 'is_active']);
        });

        // Update existing appliances to be public catalog items
        DB::table('appliances')->update([
            'is_public' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appliances', function (Blueprint $table) {
            $table->dropIndex(['is_public', 'is_active']);
            $table->dropColumn([
                'default_usage_hours',
                'metadata',
                'is_public',
                'is_active',
            ]);
        });
    }
};
