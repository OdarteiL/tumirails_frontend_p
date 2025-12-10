<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('organisation_provider_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->onDelete('cascade');
            $table->string('business_registration')->unique();
            $table->json('service_areas'); // Array of regions/cities
            $table->json('certifications')->nullable(); // Array of certification names/types
            $table->decimal('rating', 3, 2)->default(0.00); // 0.00 to 5.00
            $table->timestamps();

            $table->index('organisation_id');
            $table->index('business_registration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_provider_details');
    }
};
