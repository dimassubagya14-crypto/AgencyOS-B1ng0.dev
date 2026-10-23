<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name', 'contact_person', 'phone', 'email', 'internal_notes',
    ];

    // Satu client bisa punya banyak request (PRD bab 5 — Client & Request dipisah).
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function requestCountAttribute(): int
    {
        return $this->serviceRequests()->count();
    }
}
