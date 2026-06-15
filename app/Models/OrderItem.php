<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'menu_id',
        'menu_name',
        'menu_price',
        'quantity',
        'subtotal',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'menu_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'quantity' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    // ─── Relationships ───────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    // ─── Accessors ───────────────────────────────────────

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp' . number_format($this->menu_price, 0, ',', '.');
    }
}
