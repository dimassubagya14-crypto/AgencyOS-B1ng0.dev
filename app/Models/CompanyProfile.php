<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'agency_name', 'about', 'vision_mission', 'services', 'advantages',
        'process', 'contact_email', 'contact_phone', 'contact_address',
        'footer_title', 'footer_description', 'footer_whatsapp', 'footer_email', 'footer_address',
    ];

    // Konten publik selalu satu baris — dibuat kalau belum ada, lalu selalu dipakai ulang.
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'agency_name' => config('app.name', 'B!ng0.dev'),
        ]);
    }
}
