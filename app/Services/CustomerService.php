<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\DianMunicipality;
use App\Models\CompanyTaxSetting;
use Illuminate\Support\Facades\Log;

class CustomerService
{
    /**
     * Calcular dígito de verificación (DV) para NIT colombiano
     * Utiliza el algoritmo de módulo 11 con pesos específicos
     *
     * @param string $nit NIT sin dígito de verificación
     * @return string Dígito de verificación (0-9)
     */
    public function calculateVerificationDigit(string $nit): string
    {
        // Remover caracteres no numéricos
        $nit = preg_replace('/\D/', '', $nit);
        
        // Pesos utilizados en el algoritmo de verificación del NIT
        $weights = [71, 67, 59, 53, 47, 43, 41, 37, 29, 23, 19, 17, 13, 7, 3];
        $sum = 0;
        $nitLength = strlen($nit);

        // Multiplicar cada dígito del NIT por su peso correspondiente
        for ($i = 0; $i < $nitLength; $i++) {
            $sum += (int)$nit[$nitLength - 1 - $i] * $weights[$i];
        }

        // Calcular el residuo de la división por 11
        $remainder = $sum % 11;
        
        // Si el residuo es menor a 2, el DV es el residuo mismo
        if ($remainder < 2) {
            return (string)$remainder;
        }

        // De lo contrario, el DV es 11 menos el residuo
        return (string)(11 - $remainder);
    }

    /**
     * Verificar si una identificación ya existe en el sistema
     *
     * @param string $identification Número de identificación
     * @return bool True si ya existe
     */
    public function checkIdentificationExists(string $identification): bool
    {
        return Customer::withoutGlobalScopes()
            ->whereHas('taxProfile', function ($query) use ($identification) {
                $query->where('identification', $identification);
            })
            ->exists();
    }

    /**
     * Obtener mensaje de validación de identificación
     *
     * @param string $identification Número de identificación
     * @return array ['exists' => bool, 'message' => string]
     */
    public function getIdentificationValidationMessage(string $identification): array
    {
        if (empty($identification)) {
            return [
                'exists' => false,
                'message' => ''
            ];
        }

        $exists = $this->checkIdentificationExists($identification);

        return [
            'exists' => $exists,
            'message' => $exists 
                ? 'Esta identificación ya está registrada.'
                : 'Identificación disponible.'
        ];
    }

    /**
     * Crear un nuevo cliente con perfil tributario
     *
     * @param array $customerData Datos del cliente (name, phone, email, address, identification)
     * @param array $taxProfileData Datos tributarios (identification, dv, identification_document_id, etc.)
     * @param bool $requiresElectronicInvoice Si requiere facturación electrónica
     * @return Customer El cliente creado
     * @throws \Exception
     */
    public function createCustomer(
        array $customerData,
        array $taxProfileData,
        bool $requiresElectronicInvoice = false
    ): Customer
    {
        Log::info('📝 CREANDO CLIENTE CON PERFIL TRIBUTARIO', [
            'name' => $customerData['name'] ?? null,
            'identification' => $taxProfileData['identification'] ?? null,
        ]);

        try {
            // Verificar que la identificación no exista
            if ($this->checkIdentificationExists($taxProfileData['identification'])) {
                throw new \Exception('Esta identificación ya está registrada.');
            }

            // Crear el cliente
            $customer = Customer::create([
                'name' => mb_strtoupper($customerData['name']),
                'phone' => $customerData['phone'] ?? null,
                'email' => $customerData['email'] ?? null,
                'address' => $customerData['address'] ?? null,
                'identification_number' => $taxProfileData['identification'] ?? null,
                'identification_type_id' => $customerData['identification_type_id'] ?? null,
                'is_active' => true,
                'requires_electronic_invoice' => $requiresElectronicInvoice,
            ]);

            // Preparar datos de perfil tributario con valores por defecto
            $municipalityId = $this->getDefaultMunicipalityId($requiresElectronicInvoice, $taxProfileData);

            $profileData = [
                'identification' => $taxProfileData['identification'],
                'dv' => $taxProfileData['dv'] ?? null,
                'identification_document_id' => $taxProfileData['identification_document_id'] ?? 3, // CC por defecto
                'legal_organization_id' => $taxProfileData['legal_organization_id'] ?? 2, // Persona Natural
                'tribute_id' => $taxProfileData['tribute_id'] ?? 21, // No responsable de IVA
                'municipality_id' => $municipalityId,
                'company' => $taxProfileData['company'] ?? null,
                'trade_name' => $taxProfileData['trade_name'] ?? null,
            ];

            // Crear perfil tributario
            $customer->taxProfile()->create($profileData);

            Log::info('✅ CLIENTE CREADO EXITOSAMENTE', [
                'id' => $customer->id,
                'name' => $customer->name
            ]);

            return $customer;

        } catch (\Exception $e) {
            Log::error('❌ ERROR CREANDO CLIENTE:', [
                'message' => $e->getMessage(),
                'data' => $customerData,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Obtener el ID de municipio por defecto
     *
     * @param bool $requiresElectronicInvoice Si requiere facturación electrónica
     * @param array $taxProfileData Datos tributarios (puede contener municipalityId)
     * @return int ID del municipio
     */
    private function getDefaultMunicipalityId(bool $requiresElectronicInvoice, array $taxProfileData): int
    {
        if ($requiresElectronicInvoice && !empty($taxProfileData['municipality_id'])) {
            return $taxProfileData['municipality_id'];
        }

        // Intentar obtener del config de empresa
        $defaultMunicipality = CompanyTaxSetting::first()?->municipality_id;
        if ($defaultMunicipality) {
            return $defaultMunicipality;
        }

        // Intentar obtener el primer municipio de la BD
        $firstMunicipality = DianMunicipality::first()?->factus_id;
        if ($firstMunicipality) {
            return $firstMunicipality;
        }

        // Fallback a Bogotá (Factus ID)
        return 149;
    }

    /**
     * Obtener información completa del cliente con perfil tributario
     *
     * @param int $customerId ID del cliente
     * @return array Datos del cliente con perfil
     */
    public function getCustomerWithProfile(int $customerId): ?array
    {
        $customer = Customer::withoutGlobalScopes()
            ->with('taxProfile')
            ->find($customerId);

        if (!$customer) {
            return null;
        }

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone ?? 'S/N',
            'email' => $customer->email,
            'address' => $customer->address,
            'taxProfile' => [
                'identification' => $customer->taxProfile?->identification ?? 'S/N',
                'dv' => $customer->taxProfile?->dv,
                'company' => $customer->taxProfile?->company,
                'tradeName' => $customer->taxProfile?->trade_name,
            ]
        ];
    }
}
