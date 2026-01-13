<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * StayStatus Model
 *
 * Catálogo de estados para stays (ocupaciones).
 * Estados permitidos:
 * - active: ocupación en curso
 * - finished: checkout completado
 */
class StayStatus extends Model
{
    protected $table = 'stay_statuses';

    public $timestamps = true;

    protected $fillable = ['code', 'name'];

    // Status codes constants
    public const ACTIVE = 1;
    public const FINISHED = 2;

    /**
     * Get all stays with this status.
     */
    public function stays(): HasMany
    {
        return $this->hasMany(Stay::class, 'status_id');
    }

    /**
     * Scope: get status by code.
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }
}
