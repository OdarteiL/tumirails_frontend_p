<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('installer_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('company_name')->nullable();
            $table->string('license_number')->unique();
            $table->json('service_areas'); // Array of regions/cities
            $table->json('certifications')->nullable(); // Array of certification names
            $table->unsignedInteger('years_experience')->default(0);
            $table->decimal('rating', 3, 2)->default(0.00); // 0.00 to 5.00
            $table->timestamps();

            $table->index('user_id');
            $table->index('license_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installer_details');
    }
};
