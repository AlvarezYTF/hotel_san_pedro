<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DianCustomerTribute extends Model
{
    protected $fillable = ['code', 'name'];
    
    public function taxProfiles()
    {
        return $this->hasMany(CustomerTaxProfile::class, 'tribute_id');
    }
    
    public function invoiceItems()
    {
        return $this->hasMany(ElectronicInvoiceItem::class, 'tribute_id');
    }
}
