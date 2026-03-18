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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // FK to users - who performed action
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');

            // polymorphic - what was changed
            $table->morphs('auditable');

            // action enum
            $table->enum('action', [
                'created',
                'updated',
                'deleted',
                'status_changed',
                'assigned',
                'unassigned',
            ])->index();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('reason')->nullable(); // For status changes

            $table->timestamps();

            // Indexes for time-based queries
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
