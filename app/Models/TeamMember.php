<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'email', 'phone', 'position', 'photo', 'bio', 'skills', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Request yang sedang/pernah ditangani anggota ini.
    public function serviceRequests(): BelongsToMany
    {
        return $this->belongsToMany(ServiceRequest::class, 'request_team_member')->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Dipakai di halaman detail request untuk kasih saran siapa yang paling
     * relevan garap request tersebut, berdasarkan cocoknya skill dengan
     * jenis website yang diminta (mis. skill "E-Commerce" untuk request
     * bertipe E-Commerce). Bukan aturan kaku — cuma sorting bantu admin.
     */
    public static function suggestedFor(string $websiteType)
    {
        return static::active()->get()->sortByDesc(function (self $member) use ($websiteType) {
            $skills = $member->skills ?? [];

            return collect($skills)->contains(
                fn ($skill) => str_contains(strtolower($skill), strtolower($websiteType))
                    || str_contains(strtolower($websiteType), strtolower($skill))
            ) ? 1 : 0;
        })->values();
    }
}
