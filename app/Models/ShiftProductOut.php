<?php

namespace App\Models;

use App\Enums\ShiftProductOutReason;
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
        'reason' => ShiftProductOutReason::class,
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
     * Scope to filter by shift handover.
     */
    public function scopeByShiftHandover($query, $shiftHandoverId)
    {
        return $query->where('shift_handover_id', $shiftHandoverId);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}

