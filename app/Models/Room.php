<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'floor',
        'is_active',
        'qr_code_path',
        'qr_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'floor' => 'integer',
        ];
    }

    // ─── Boot ────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($room) {
            if (empty($room->qr_token)) {
                $room->qr_token = Str::uuid()->toString();
            }
        });
    }

    // ─── Relationships ───────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ─── Scopes ──────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Accessors ───────────────────────────────────────

    public function getQrUrlAttribute(): string
    {
        return url("/room/{$this->qr_token}");
    }
}
