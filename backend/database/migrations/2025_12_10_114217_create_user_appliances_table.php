<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('user_appliances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appliance_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('daily_usage_hours', 4, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'appliance_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_appliances');
    }
};
