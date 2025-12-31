<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string|null $code_reference
 * @property string|null $description
 * @property int|null $standard_code_id
 * @property int $unit_measure_id
 * @property int|null $tribute_id
 * @property float $price
 * @property float $tax_rate
 * @property bool $is_active
 * @property-read DianProductStandard|null $standardCode
 * @property-read DianMeasurementUnit $unitMeasure
 * @property-read DianCustomerTribute|null $tribute
 *
 * @mixin Builder
 */
class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code_reference',
        'description',
        'standard_code_id',
        'unit_measure_id',
        'tribute_id',
        'price',
        'tax_rate',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function standardCode(): BelongsTo
    {
        return $this->belongsTo(DianProductStandard::class, 'standard_code_id');
    }

    public function unitMeasure(): BelongsTo
    {
        return $this->belongsTo(DianMeasurementUnit::class, 'unit_measure_id', 'factus_id');
    }

    public function tribute(): BelongsTo
    {
        return $this->belongsTo(DianCustomerTribute::class, 'tribute_id');
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('code_reference', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Convert service to invoice item array format.
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public function toInvoiceItem(array $overrides = []): array
    {
        return array_merge([
            'service_id' => $this->id,
            'code_reference' => $this->code_reference,
            'name' => $this->name,
            'quantity' => 1,
            'price' => $this->price,
            'tax_rate' => $this->tax_rate,
            'standard_code_id' => $this->standard_code_id,
            'unit_measure_id' => $this->unit_measure_id,
            'tribute_id' => $this->tribute_id,
        ], $overrides);
    }
}

