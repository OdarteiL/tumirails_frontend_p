<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hardware', function (Blueprint $table) {
            $table->string('owner_type')->nullable()->after('provider_id');
            $table->unsignedBigInteger('owner_id')->nullable()->after('owner_type');
            $table->index(['owner_type', 'owner_id']);
        });

        // Backfill existing providers into users + provider_details and assign ownership
        if (Schema::hasTable('providers') && Schema::hasTable('hardware')) {
            $providers = DB::table('providers')->get();

            foreach ($providers as $provider) {
                // Create a user to represent this provider company
                $email = Str::slug($provider->company_name).
                    sprintf('+provider%d@tumi.local', $provider->id);

                $userId = DB::table('users')->insertGetId([
                    'first_name' => $provider->company_name,
                    'last_name' => null,
                    'other_names' => null,
                    'email' => $email,
                    'password' => bcrypt(Str::random(24)),
                    'phone' => null,
                    'address' => null,
                    'role' => 'provider',
                    'status' => $provider->status ?? 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create provider detail linked to the user
                if (Schema::hasTable('provider_details')) {
                    DB::table('provider_details')->insert([
                        'user_id' => $userId,
                        'company_name' => $provider->company_name,
                        'business_registration' => $provider->business_registration,
                        'service_areas' => json_encode([]),
                        'certifications' => json_encode([]),
                        'rating' => $provider->rating ?? 0.0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Assign hardware rows to this new user owner
                DB::table('hardware')->where('provider_id', $provider->id)
                    ->update([
                        'owner_type' => 'App\\Models\\User',
                        'owner_id' => $userId,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hardware', function (Blueprint $table) {
            $table->dropIndex(['owner_type', 'owner_id']);
            $table->dropColumn(['owner_type', 'owner_id']);
        });
    }
};
