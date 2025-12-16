<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerTaxProfile extends Model
{
    protected $fillable = [
        'customer_id',
        'identification_document_id',
        'identification',
        'dv',
        'legal_organization_id',
        'company',
        'trade_name',
        'names',
        'address',
        'email',
        'phone',
        'tribute_id',
        'municipality_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function identificationDocument()
    {
        return $this->belongsTo(DianIdentificationDocument::class, 'identification_document_id');
    }

    public function legalOrganization()
    {
        return $this->belongsTo(DianLegalOrganization::class, 'legal_organization_id');
    }

    public function tribute()
    {
        return $this->belongsTo(DianCustomerTribute::class, 'tribute_id');
    }

    public function municipality()
    {
        return $this->belongsTo(DianMunicipality::class, 'municipality_id', 'factus_id');
    }

    public function requiresDV(): bool
    {
        return $this->identificationDocument?->requires_dv ?? false;
    }

    public function isJuridicalPerson(): bool
    {
        return $this->identificationDocument?->code === 'NIT';
    }
}
