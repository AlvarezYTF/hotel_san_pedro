<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashOutflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_handover_id',
        'amount',
        'reason',
        'date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'datetime',
    ];

    /**
     * Get the user who registered the outflow.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shiftHandover(): BelongsTo
    {
        return $this->belongsTo(ShiftHandover::class);
    }
}

