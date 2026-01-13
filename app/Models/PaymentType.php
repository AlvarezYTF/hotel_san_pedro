<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    protected $table = 'payment_types';

    protected $fillable = [
        'code',
        'name',
    ];

    public $timestamps = true;

    /**
     * Get all payments of this type.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
