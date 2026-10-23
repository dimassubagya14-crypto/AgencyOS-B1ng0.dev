<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('active_session_id', 255)->nullable()->index()->after('remember_token');
            $table->string('active_session_ip', 45)->nullable()->after('active_session_id');
            $table->text('active_session_user_agent')->nullable()->after('active_session_ip');
            $table->timestamp('last_login_at')->nullable()->after('active_session_user_agent');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['active_session_id', 'active_session_ip', 'active_session_user_agent', 'last_login_at']);
        });
    }
};
