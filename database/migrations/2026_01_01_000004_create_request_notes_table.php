<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Catatan internal admin per request — terpisah dari activity_logs
// supaya activity_logs tetap murni audit trail sistem (bukan diskusi bebas).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_notes');
    }
};
