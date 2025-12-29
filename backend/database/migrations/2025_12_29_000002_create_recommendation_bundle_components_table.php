<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_bundle_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_id')->constrained('recommendation_bundles')->cascadeOnDelete();
            $table->foreignId('hardware_id')->constrained();
            $table->string('role')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->text('rationale')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_bundle_components');
    }
};
