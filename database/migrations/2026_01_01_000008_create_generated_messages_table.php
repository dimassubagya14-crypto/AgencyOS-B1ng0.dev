<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Versi proposal/pesan yang sudah di-generate & disimpan admin (PRD bab 7:
// "simpan versi proposal/message"). Sengaja masuk P0 karena generator-nya
// P1, tapi tabel penyimpanan hasil generate cukup murah untuk disiapkan dari awal.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('templates')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('channel')->default('manual'); // email | whatsapp | manual/copy
            $table->text('content'); // hasil akhir setelah placeholder diisi + edit admin
            $table->string('status')->default('draft'); // draft | sent
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_messages');
    }
};
