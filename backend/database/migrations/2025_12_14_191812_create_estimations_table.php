<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('estimations', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic ownership
            $table->unsignedBigInteger('owner_id');
            $table->string('owner_type');
            
            // Site and versioning
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('previous_estimation_id')->nullable()->constrained('estimations')->onDelete('set null');
            
            // Calculation results
            $table->decimal('total_watts', 10, 2);
            $table->decimal('daily_kwh', 10, 2);
            $table->decimal('monthly_kwh', 10, 2);
            $table->decimal('estimated_monthly_cost', 10, 2);
            
            // Tariff information
            $table->foreignId('tariff_structure_id')->nullable()->constrained('tariff_structures')->onDelete('set null');
            $table->decimal('power_factor_applied', 3, 2)->nullable();
            $table->decimal('seasonal_multiplier', 3, 2)->nullable();
            
            // Snapshots and metadata
            $table->json('appliances_snapshot');
            $table->json('calculation_metadata')->nullable();
            
            // Audit
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['owner_id', 'owner_type']);
            $table->index('site_id');
            $table->index('version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimations');
    }
};
