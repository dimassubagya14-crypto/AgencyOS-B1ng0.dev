<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Legacy placeholder.
 *
 * Laravel's default migration 0001_01_01_000000_create_users_table.php
 * already creates users, password_reset_tokens, and sessions.
 * This file is intentionally a no-op so the old duplicate users migration
 * cannot try to create the users table a second time.
 */
return new class extends Migration
{
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
