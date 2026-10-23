<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Anggota tim agency + keahlian (skills). Terpisah dari `users` karena
// tidak semua anggota tim butuh akun login ke admin panel — ini murni
// data profil tim yang juga dipakai untuk fitur assignment ke request.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // link opsional ke akun login
            $table->string('name');
            $table->string('position')->nullable(); // mis. "Backend Developer", "UI/UX Designer"
            $table->string('photo')->nullable();
            $table->text('bio')->nullable();
            $table->json('skills')->nullable(); // ["Laravel", "React", "UI Design"]
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
