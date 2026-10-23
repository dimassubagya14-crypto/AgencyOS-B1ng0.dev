<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Status workflow sesuai SDLC fase 3:
 * Baru -> Diperiksa -> Konsultasi -> Proposal Dibuat -> Proposal Dikirim
 * -> Negosiasi -> Disetujui / Ditolak / Batal
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // REQ-YYYYMM-0001

            // Data mentah dari form publik — belum tentu match client yang sudah ada.
            $table->string('company_name');
            $table->string('contact_name');
            $table->string('phone');
            $table->string('email');

            $table->string('project_name');
            $table->enum('website_type', [
                'Company Profile', 'Portfolio', 'E-Commerce', 'Sistem Informasi',
                'Online Ordering', 'Booking', 'Dashboard', 'Custom Web Application', 'Lainnya',
            ]);
            $table->text('description');
            $table->json('functional_requirements')->nullable();
            $table->text('other_requirements')->nullable();

            $table->enum('status', [
                'Baru', 'Diperiksa', 'Konsultasi', 'Proposal Dibuat',
                'Proposal Dikirim', 'Negosiasi', 'Disetujui', 'Ditolak', 'Batal',
            ])->default('Baru');

            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
