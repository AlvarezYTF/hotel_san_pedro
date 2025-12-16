<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DianDocumentType extends Model
{
    protected $fillable = ['code', 'name'];
    
    public function electronicInvoices()
    {
        return $this->hasMany(ElectronicInvoice::class, 'document_type_id');
    }
}
