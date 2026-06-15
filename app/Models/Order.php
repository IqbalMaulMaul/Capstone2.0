<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'room_id',
        'guest_name',
        'status',
        'subtotal',
        'tax',
        'total',
        'notes',
        'estimated_delivery',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'estimated_delivery' => 'datetime',
        ];
    }

    // ─── Constants ───────────────────────────────────────

    const STATUS_PENDING_PAYMENT = 'pending_payment';
    const STATUS_PAID = 'paid';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_PROCESSING = 'processing';
    const STATUS_READY = 'ready';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const STATUS_LABELS = [
        'pending_payment' => 'Menunggu Pembayaran',
        'paid' => 'Sudah Dibayar',
        'accepted' => 'Diterima Kitchen',
        'processing' => 'Sedang Diproses',
        'ready' => 'Siap Diantar',
        'delivered' => 'Sedang Diantar',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    // ─── Boot ────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $prefix = config('hotel.order_prefix', 'ORD');
                $order->order_number = $prefix . '-' . strtoupper(Str::random(8));
            }
        });
    }

    // ─── Relationships ───────────────────────────────────

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    // ─── Scopes ──────────────────────────────────────────

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePaid($query)
    {
        return $query->whereNotIn('status', ['pending_payment', 'cancelled']);
    }

    public function scopeKitchenActive($query)
    {
        return $query->whereIn('status', ['paid', 'accepted', 'processing', 'ready']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // ─── Helpers ─────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp' . number_format($this->total, 0, ',', '.');
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $taxRate = config('hotel.tax_rate', 11) / 100;
        $tax = $subtotal * $taxRate;

        $this->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT;
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [
            self::STATUS_PAID,
            self::STATUS_ACCEPTED,
            self::STATUS_PROCESSING,
            self::STATUS_READY,
            self::STATUS_DELIVERED,
            self::STATUS_COMPLETED,
        ]);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_PAYMENT,
        ]);
    }
}
