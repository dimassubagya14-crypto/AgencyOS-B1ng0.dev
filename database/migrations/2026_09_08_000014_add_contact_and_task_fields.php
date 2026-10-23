<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->string('phone', 30)->nullable()->after('email');
        });

        Schema::table('request_team_member', function (Blueprint $table) {
            $table->text('task')->nullable()->after('team_member_id');
            $table->date('deadline')->nullable()->after('task');
            $table->string('task_status')->default('assigned')->after('deadline');
            $table->timestamp('whatsapp_opened_at')->nullable()->after('task_status');
            $table->timestamp('email_sent_at')->nullable()->after('whatsapp_opened_at');
        });
    }

    public function down(): void
    {
        Schema::table('request_team_member', function (Blueprint $table) {
            $table->dropColumn(['task', 'deadline', 'task_status', 'whatsapp_opened_at', 'email_sent_at']);
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn(['email', 'phone']);
        });
    }
};
