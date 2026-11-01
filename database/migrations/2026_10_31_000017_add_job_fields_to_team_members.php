<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->boolean('open_to_work')->default(false);
            $table->string('desired_role')->nullable();
            $table->json('experiences')->nullable();   // [{role, company, period, description}]
            $table->json('education')->nullable();     // [{school, degree, period}]
            $table->json('languages')->nullable();     // ["Indonesia (Native)", ...]
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn(['open_to_work', 'desired_role', 'experiences', 'education', 'languages']);
        });
    }
};
