<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        // PostgreSQL doesn't support ALTER COLUMN on enums directly.
        // We drop the check constraint and re-add it with the new values.
        DB::statement('ALTER TABLE organisation_members DROP CONSTRAINT IF EXISTS organisation_members_role_check');
        DB::statement("ALTER TABLE organisation_members ADD CONSTRAINT organisation_members_role_check CHECK (role IN ('owner', 'admin', 'member'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE organisation_members DROP CONSTRAINT IF EXISTS organisation_members_role_check');
        DB::statement("ALTER TABLE organisation_members ADD CONSTRAINT organisation_members_role_check CHECK (role IN ('owner', 'admin', 'installer', 'provider', 'customer'))");
    }
};
