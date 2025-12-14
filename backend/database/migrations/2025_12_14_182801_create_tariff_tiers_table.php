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
        Schema::create('tariff_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tariff_structure_id')->constrained()->onDelete('cascade');
            $table->decimal('min_kwh', 10, 2)->comment('Minimum kWh for this tier');
            $table->decimal('max_kwh', 10, 2)->nullable()->comment('Maximum kWh for this tier (null means unlimited)');
            $table->decimal('rate_per_kwh', 8, 4)->comment('Rate per kWh in the local currency');
            $table->unsignedInteger('order')->default(0)->comment('Display order of tiers');
            $table->timestamps();

            $table->index(['tariff_structure_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tariff_tiers');
    }
};
