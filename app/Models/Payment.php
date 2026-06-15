<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'transaction_id',
        'snap_token',
        'method',
        'status',
        'amount',
        'midtrans_response',
        'paid_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'midtrans_response' => 'array',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    // ─── Constants ───────────────────────────────────────

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REFUNDED = 'refunded';

    const STATUS_LABELS = [
        'pending' => 'Menunggu Pembayaran',
        'success' => 'Berhasil',
        'failed' => 'Gagal',
        'expired' => 'Kadaluarsa',
        'refunded' => 'Dikembalikan',
    ];

    const METHOD_LABELS = [
        'qris' => 'QRIS',
        'bank_transfer' => 'Transfer Bank',
        'e_wallet' => 'E-Wallet',
    ];

    // ─── Relationships ───────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    // ─── Scopes ──────────────────────────────────────────

    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // ─── Helpers ─────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHOD_LABELS[$this->method] ?? $this->method ?? '-';
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp' . number_format($this->amount, 0, ',', '.');
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
