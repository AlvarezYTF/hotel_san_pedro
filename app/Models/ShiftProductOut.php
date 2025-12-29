<?php

namespace App\Models;

use App\Enums\ShiftType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftProductOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_handover_id',
        'user_id',
        'product_id',
        'quantity',
        'reason',
        'observations',
        'shift_type',
        'shift_date',
    ];

    protected $casts = [
        'shift_type' => ShiftType::class,
        'shift_date' => 'date',
        'quantity' => 'decimal:2',
    ];

    public function shiftHandover(): BelongsTo
    {
        return $this->belongsTo(ShiftHandover::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get human-readable reason.
     */
    public function getReadableReasonAttribute(): string
    {
        return match ($this->reason) {
            'merma' => 'Merma / Deterioro',
            'consumo_interno' => 'Consumo Interno',
            'perdida' => 'Pérdida / Robo',
            'donacion' => 'Donación',
            'ajuste_inventario' => 'Ajuste de Inventario',
            default => ucfirst(str_replace('_', ' ', $this->reason)),
        };
    }
}

