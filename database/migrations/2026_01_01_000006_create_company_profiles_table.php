<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Single-row settings table untuk konten halaman company profile publik.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('agency_name')->default('B!ng0.dev');
            $table->text('about')->nullable();
            $table->text('vision_mission')->nullable();
            $table->text('services')->nullable();
            $table->text('advantages')->nullable();
            $table->text('process')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
