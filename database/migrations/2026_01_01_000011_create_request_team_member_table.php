<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Pivot: satu request bisa dikerjakan lebih dari satu anggota tim,
// dan satu anggota tim bisa pegang beberapa request sekaligus.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_team_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_member_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['service_request_id', 'team_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_team_member');
    }
};
