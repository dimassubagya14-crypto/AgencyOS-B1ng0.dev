<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'email', 'phone', 'position', 'photo', 'bio', 'skills', 'is_active', 'sort_order',
        'slug', 'headline', 'location', 'about', 'social_links', 'certificates', 'projects',
        'agency_portfolio_ids', 'show_contact', 'portfolio_published',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'is_active' => 'boolean',
            'social_links' => 'array',
            'certificates' => 'array',
            'projects' => 'array',
            'agency_portfolio_ids' => 'array',
            'show_contact' => 'boolean',
            'portfolio_published' => 'boolean',
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

    // ---- Portofolio pribadi (halaman publik /tim/{slug}) ----

    public function ensureSlug(): string
    {
        if ($this->slug) {
            return $this->slug;
        }

        $base = Str::slug($this->name) ?: 'anggota';
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $this->slug = $slug;
    }

    public function portfolioUrl(): ?string
    {
        return $this->slug ? route('team.portfolio', $this->slug) : null;
    }

    public function contactEmail(): ?string
    {
        return $this->email ?: optional($this->user)->email;
    }

    // Proyek agency yang dipilih untuk ditampilkan. Diambil langsung dari data portfolio
    // agency (hanya yang published), jadi otomatis ikut berubah kalau agency mengeditnya.
    public function agencyProjects()
    {
        $ids = $this->agency_portfolio_ids ?? [];
        if (empty($ids)) {
            return collect();
        }

        return Portfolio::published()->whereIn('id', $ids)->orderBy('sort_order')->orderBy('name')->get();
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
