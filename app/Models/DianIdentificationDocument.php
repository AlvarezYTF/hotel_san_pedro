<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DianIdentificationDocument extends Model
{
    protected $fillable = ['code', 'name', 'requires_dv'];
    
    protected $casts = ['requires_dv' => 'boolean'];
    
    public function taxProfiles()
    {
        return $this->hasMany(CustomerTaxProfile::class, 'identification_document_id');
    }
}
