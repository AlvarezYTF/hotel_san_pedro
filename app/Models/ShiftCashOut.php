<?php

namespace App\Models;

use App\Enums\ShiftType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftCashOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_handover_id',
        'user_id',
        'amount',
        'concept',
        'observations',
        'shift_type',
        'shift_date',
    ];

    protected $casts = [
        'shift_type' => ShiftType::class,
        'shift_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function shiftHandover(): BelongsTo
    {
        return $this->belongsTo(ShiftHandover::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePorTurno($query, ShiftType $type)
    {
        return $query->where('shift_type', $type);
    }

    public function scopePorRecepcionista($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}

