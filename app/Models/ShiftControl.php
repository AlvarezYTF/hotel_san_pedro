<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftControl extends Model
{
    protected $fillable = [
        "operational_enabled",
        "updated_by",
        "note",
    ];

    protected $casts = [
        "operational_enabled" => "boolean",
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "updated_by");
    }
}

