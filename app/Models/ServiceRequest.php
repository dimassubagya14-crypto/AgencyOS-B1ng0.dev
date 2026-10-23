<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = [
        'Baru', 'Diperiksa', 'Konsultasi', 'Proposal Dibuat',
        'Proposal Dikirim', 'Negosiasi', 'Disetujui', 'Ditolak', 'Batal',
    ];

    public const WEBSITE_TYPES = [
        'Company Profile', 'Portfolio', 'E-Commerce', 'Sistem Informasi',
        'Online Ordering', 'Booking', 'Dashboard', 'Custom Web Application', 'Lainnya',
    ];

    protected $fillable = [
        'number', 'company_name', 'contact_name', 'phone', 'email',
        'project_name', 'website_type', 'description',
        'functional_requirements', 'other_requirements',
        'status', 'client_id', 'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'functional_requirements' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ServiceRequest $request) {
            if (empty($request->number)) {
                $request->number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $prefix = 'REQ-'.now()->format('Ym').'-';
        $lastSeq = static::withTrashed()
            ->where('number', 'like', $prefix.'%')
            ->count();

        return $prefix.str_pad((string) ($lastSeq + 1), 4, '0', STR_PAD_LEFT);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(RequestNote::class);
    }

    public function generatedMessages(): HasMany
    {
        return $this->hasMany(GeneratedMessage::class);
    }

    // Anggota tim yang ditugaskan garap request ini (bisa lebih dari satu).
    public function teamMembers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TeamMember::class, 'request_team_member')
            ->withPivot(['task', 'deadline', 'task_status', 'whatsapp_opened_at', 'email_sent_at'])
            ->withTimestamps();
    }
}
