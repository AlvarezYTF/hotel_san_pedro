<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectronicInvoiceItem extends Model
{
    protected $fillable = [
        'electronic_invoice_id', 'tribute_id', 'standard_code_id',
        'unit_measure_id',
        'code_reference', 'name',
        'quantity', 'price',
        'tax_rate', 'tax_amount',
        'discount_rate', 'is_excluded', 'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'is_excluded' => 'boolean',
        'total' => 'decimal:2',
    ];

    public function electronicInvoice()
    {
        return $this->belongsTo(ElectronicInvoice::class);
    }

    public function tribute()
    {
        return $this->belongsTo(DianCustomerTribute::class, 'tribute_id');
    }

    public function productStandard()
    {
        return $this->belongsTo(DianProductStandard::class, 'standard_code_id');
    }

    public function unitMeasure()
    {
        return $this->belongsTo(DianMeasurementUnit::class, 'unit_measure_id', 'factus_id');
    }
}
