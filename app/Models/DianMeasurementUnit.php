<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DianMeasurementUnit extends Model
{
    protected $table = 'dian_measurement_units';

    protected $fillable = [
        'factus_id',
        'code',
        'name',
    ];

    protected $casts = [
        'factus_id' => 'integer',
    ];

    public function electronicInvoiceItems()
    {
        return $this->hasMany(ElectronicInvoiceItem::class, 'unit_measure_id', 'factus_id');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('code', 'LIKE', "%{$term}%");
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }
}
