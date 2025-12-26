<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('provider_details') && ! Schema::hasColumn('provider_details', 'verified')) {
            Schema::table('provider_details', function (Blueprint $table) {
                $table->boolean('verified')->default(false)->after('rating');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('provider_details') && Schema::hasColumn('provider_details', 'verified')) {
            Schema::table('provider_details', function (Blueprint $table) {
                $table->dropColumn('verified');
            });
        }
    }
};
