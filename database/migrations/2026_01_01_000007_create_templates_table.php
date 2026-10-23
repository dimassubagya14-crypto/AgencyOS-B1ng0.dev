<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Template proposal/email/WhatsApp. Placeholder di 'content' memakai format
// {nama_client}, {jenis_website}, {kebutuhan}, {nama_agency} — diisi otomatis
// saat admin generate pesan dari sebuah service_request (PRD bab 7).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['proposal', 'email', 'whatsapp', 'follow_up', 'confirmation'])->default('proposal');
            $table->string('category')->nullable();
            $table->text('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
