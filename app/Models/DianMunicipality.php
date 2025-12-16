<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DianMunicipality extends Model
{
    protected $table = 'dian_municipalities';

    protected $fillable = [
        'factus_id',
        'code',
        'name',
        'department',
    ];

    protected $casts = [
        'factus_id' => 'integer',
    ];

    public function companyTaxSettings()
    {
        return $this->hasMany(CompanyTaxSetting::class, 'municipality_id', 'factus_id');
    }

    public function customerTaxProfiles()
    {
        return $this->hasMany(CustomerTaxProfile::class, 'municipality_id', 'factus_id');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('department', 'LIKE', "%{$term}%")
                    ->orWhere('code', 'LIKE', "%{$term}%");
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} – {$this->department}";
    }
}
