<?php

namespace App\Models;

use App\Enums\ShiftStatus;
use App\Enums\ShiftType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'status',
        'opened_at',
        'closed_at',
        'opened_by',
        'closed_by',
        'base_snapshot',
        'closing_snapshot',
    ];

    protected $casts = [
        'type' => ShiftType::class,
        'status' => ShiftStatus::class,
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'base_snapshot' => 'array',
        'closing_snapshot' => 'array',
    ];

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function outgoingHandovers(): HasMany
    {
        return $this->hasMany(ShiftHandover::class, 'from_shift_id');
    }

    public function incomingHandovers(): HasMany
    {
        return $this->hasMany(ShiftHandover::class, 'to_shift_id');
    }

    public function scopeOpenOperational($query)
    {
        return $query->whereIn('type', [ShiftType::DAY, ShiftType::NIGHT])->where('status', ShiftStatus::OPEN);
    }

    public function isOpen(): bool
    {
        return $this->status === ShiftStatus::OPEN;
    }

    public function isOperational(): bool
    {
        return in_array($this->type, [ShiftType::DAY, ShiftType::NIGHT], true);
    }
}
