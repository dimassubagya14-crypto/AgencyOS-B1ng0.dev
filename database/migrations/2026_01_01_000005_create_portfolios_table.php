<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('tagline')->nullable();      // satu baris untuk hero microsite
            $table->string('client_name')->nullable();
            $table->string('category');
            $table->text('description');
            $table->text('problem')->nullable();
            $table->text('solution')->nullable();
            $table->text('results')->nullable();          // dampak/metrics setelah proyek jalan
            $table->json('features')->nullable();
            $table->json('technologies')->nullable();
            $table->string('duration')->nullable();       // mis. "6 minggu"
            $table->string('project_year')->nullable();
            $table->text('testimonial_text')->nullable();
            $table->string('testimonial_author')->nullable();
            $table->string('banner')->nullable();          // gambar hero microsite
            $table->string('thumbnail')->nullable();       // gambar kartu di listing
            $table->json('gallery')->nullable();
            $table->string('url')->nullable();
            $table->enum('status', ['draft', 'publish'])->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};
