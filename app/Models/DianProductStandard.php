<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DianProductStandard extends Model
{
    protected $fillable = ['name'];
    
    public function invoiceItems()
    {
        return $this->hasMany(ElectronicInvoiceItem::class, 'standard_code_id');
    }
}
