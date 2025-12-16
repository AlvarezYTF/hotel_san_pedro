<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DianPaymentMethod extends Model
{
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = ['code', 'name'];
    
    public function electronicInvoices()
    {
        return $this->hasMany(ElectronicInvoice::class, 'payment_method_code', 'code');
    }
}
