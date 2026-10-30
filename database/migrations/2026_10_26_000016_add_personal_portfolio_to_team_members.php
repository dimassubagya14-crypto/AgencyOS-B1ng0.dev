<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('headline')->nullable()->after('position');
            $table->string('location')->nullable()->after('headline');
            $table->text('about')->nullable()->after('bio');
            $table->json('social_links')->nullable();          // {github, linkedin, instagram, website}
            $table->json('certificates')->nullable();          // [{name, issuer, year, url}]
            $table->json('projects')->nullable();              // [{title, description, tech, year, url}]
            $table->json('agency_portfolio_ids')->nullable();  // id portfolio agency yang ditampilkan
            $table->boolean('show_contact')->default(false);
            $table->boolean('portfolio_published')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'slug', 'headline', 'location', 'about', 'social_links', 'certificates',
                'projects', 'agency_portfolio_ids', 'show_contact', 'portfolio_published',
            ]);
        });
    }
};
