<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        // Update existing invitation roles to align with member roles
        DB::statement("UPDATE organisation_invitations SET role = 'member' WHERE role IN ('installer', 'provider', 'customer')");

        DB::statement('ALTER TABLE organisation_invitations DROP CONSTRAINT IF EXISTS organisation_invitations_role_check');
        DB::statement("ALTER TABLE organisation_invitations ADD CONSTRAINT organisation_invitations_role_check CHECK (role IN ('admin', 'member'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE organisation_invitations DROP CONSTRAINT IF EXISTS organisation_invitations_role_check');
        DB::statement("ALTER TABLE organisation_invitations ADD CONSTRAINT organisation_invitations_role_check CHECK (role IN ('admin', 'installer', 'provider', 'customer'))");
    }
};
