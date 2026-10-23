<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laravel bawaan sudah bikin tabel `users`, `password_reset_tokens`, dan
 * `sessions` lewat migration default (0001_01_01_...). Jadi di sini kita
 * cuma TAMBAH kolom yang dibutuhkan sistem ini, bukan bikin ulang tabelnya
 * — supaya tidak bentrok dengan migration bawaan.
 *
 * PRD bab 6 (Authentication) & bab 10 (users). Kolom `role` disiapkan
 * sebagai fondasi permission untuk V2/V3 (Project Manager/Staff), MVP
 * hanya memakai role 'admin'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'staff'])->default('admin')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
