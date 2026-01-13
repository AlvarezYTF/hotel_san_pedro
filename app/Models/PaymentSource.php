<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSource extends Model
{
    protected $table = 'payment_sources';

    protected $fillable = [
        'code',
        'name',
    ];

    public $timestamps = true;

    /**
     * Get all payments from this source.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
