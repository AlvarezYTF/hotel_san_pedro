<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'reservation_id',
        'amount',
        'payment_method_id',
        'payment_type_id',
        'source_id',
        'reference',
        'bank_name',
        'paid_at',
        'created_by',
        'notes',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public $timestamps = true;

    /**
     * Get the reservation that owns the payment.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the payment method.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the payment type.
     */
    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class);
    }

    /**
     * Get the payment source.
     */
    public function source()
    {
        return $this->belongsTo(PaymentSource::class);
    }

    /**
     * Get the user who created the payment.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Backward-compatible alias used in RoomManager and other views.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
