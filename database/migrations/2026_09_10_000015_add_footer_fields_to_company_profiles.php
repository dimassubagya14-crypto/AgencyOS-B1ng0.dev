<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('footer_title')->nullable()->after('contact_address');
            $table->text('footer_description')->nullable()->after('footer_title');
            $table->string('footer_whatsapp', 30)->nullable()->after('footer_description');
            $table->string('footer_email')->nullable()->after('footer_whatsapp');
            $table->string('footer_address', 500)->nullable()->after('footer_email');
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'footer_title',
                'footer_description',
                'footer_whatsapp',
                'footer_email',
                'footer_address',
            ]);
        });
    }
};
