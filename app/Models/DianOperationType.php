<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DianOperationType extends Model
{
    protected $fillable = ['code', 'name'];
    
    public function electronicInvoices()
    {
        return $this->hasMany(ElectronicInvoice::class, 'operation_type_id');
    }
}
