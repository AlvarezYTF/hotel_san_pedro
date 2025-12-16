<?php

namespace App\Http\Controllers;

use App\Models\CompanyTaxSetting;
use App\Http\Requests\StoreCompanyTaxSettingRequest;
use Illuminate\Http\Request;

class CompanyTaxSettingController extends Controller
{
    /**
     * Show the form for editing company tax settings.
     */
    public function edit()
    {
        $company = CompanyTaxSetting::with('municipality')->first();
        $municipalities = \App\Models\DianMunicipality::orderBy('department')->orderBy('name')->get();
        
        return view('company-tax-settings.edit', compact('company', 'municipalities'));
    }

    /**
     * Update company tax settings.
     */
    public function update(StoreCompanyTaxSettingRequest $request)
    {
        $company = CompanyTaxSetting::getInstance();
        
        if ($company) {
            $company->update($request->validated());
            $message = 'Configuración fiscal actualizada exitosamente.';
        } else {
            CompanyTaxSetting::create($request->validated());
            $message = 'Configuración fiscal creada exitosamente.';
        }

        return redirect()->route('company-tax-settings.edit')
            ->with('success', $message);
    }
}
