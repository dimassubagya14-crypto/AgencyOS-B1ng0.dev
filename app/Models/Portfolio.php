<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Portfolio extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug', 'name', 'tagline', 'client_name', 'category', 'description', 'problem',
        'solution', 'results', 'features', 'technologies', 'duration', 'project_year',
        'testimonial_text', 'testimonial_author', 'banner', 'thumbnail', 'gallery',
        'url', 'status', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'technologies' => 'array',
            'gallery' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Portfolio $portfolio) {
            if (empty($portfolio->slug)) {
                $portfolio->slug = static::uniqueSlug($portfolio->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'publish');
    }
}
