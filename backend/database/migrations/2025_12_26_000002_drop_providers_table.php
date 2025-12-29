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
        if (Schema::hasTable('providers')) {
            Schema::dropIfExists('providers');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('providers')) {
            Schema::create('providers', function (Blueprint $table) {
                $table->id();
                $table->string('company_name')->index();
                $table->string('business_registration')->nullable();
                $table->text('description')->nullable();
                $table->decimal('rating', 3, 2)->default(0.00);
                $table->boolean('verified')->default(false);
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }
    }
};
