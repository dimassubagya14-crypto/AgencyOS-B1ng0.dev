<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Audit trail (PRD bab 8). Field minimal: user_id, action, module, record_id,
// description, IP, timestamp. Tidak ada endpoint update/delete untuk tabel ini
// di role non-superadmin — enforce di policy/route, bukan skema.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');   // create | update | delete | login | logout | status_change
            $table->string('module');   // service_requests | clients | portfolios | ...
            $table->unsignedBigInteger('record_id')->nullable();
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['module', 'record_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
