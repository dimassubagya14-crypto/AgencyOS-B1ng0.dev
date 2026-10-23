<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false; // hanya pakai created_at (lihat migration)

    protected $fillable = [
        'user_id', 'action', 'module', 'record_id', 'description', 'ip_address', 'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper dipakai di seluruh controller admin supaya format log konsisten.
     * Contoh: ActivityLog::record('update', 'service_requests', $request->id, "Status diubah ke Disetujui");
     */
    public static function record(string $action, string $module, ?int $recordId, string $description): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'description' => $description,
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }
}
