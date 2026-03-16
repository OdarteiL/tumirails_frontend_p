<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    public function up(): void
    {
        // PostgreSQL/MySQL only — SQLite has no CHECK constraint enforcement to update.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE organisation_members DROP CONSTRAINT IF EXISTS organisation_members_role_check');
        DB::statement("ALTER TABLE organisation_members ADD CONSTRAINT organisation_members_role_check CHECK (role IN ('owner', 'admin', 'member'))");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE organisation_members DROP CONSTRAINT IF EXISTS organisation_members_role_check');
        DB::statement("ALTER TABLE organisation_members ADD CONSTRAINT organisation_members_role_check CHECK (role IN ('owner', 'admin', 'installer', 'provider', 'customer'))");
    }
};
