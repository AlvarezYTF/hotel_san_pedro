<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payments_methods';

    protected $fillable = [
        'code',
        'name',
    ];

    public $timestamps = true;

    /**
     * Get all payments using this payment method.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
