<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DianLegalOrganization extends Model
{
    protected $fillable = ['code', 'name'];
    
    public function taxProfiles()
    {
        return $this->hasMany(CustomerTaxProfile::class, 'legal_organization_id');
    }
}
