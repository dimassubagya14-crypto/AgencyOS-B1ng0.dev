<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PRD bab 6 (Client CRUD) & bab 5: Client dan Request WAJIB dipisah,
     * satu client bisa punya banyak service_requests.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
