<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('address');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('timezone');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE sites ADD CONSTRAINT check_latitude CHECK (latitude >= -90 AND latitude <= 90)');
        DB::statement('ALTER TABLE sites ADD CONSTRAINT check_longitude CHECK (longitude >= -180 AND longitude <= 180)');
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
