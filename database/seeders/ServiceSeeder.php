<?php

namespace Database\Seeders;

use App\Models\DianMeasurementUnit;
use App\Models\DianProductStandard;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Get default unit measure (Unidad - UN)
        $unitMeasure = DianMeasurementUnit::where('code', 'UN')->first();
        if (!$unitMeasure) {
            $unitMeasure = DianMeasurementUnit::first();
        }

        // Get standard code if available
        $standardCode = DianProductStandard::first();

        $services = [
            [
                'name' => 'Hospedaje (noche)',
                'code_reference' => 'HOSP-001',
                'description' => 'Servicio de hospedaje por noche',
                'standard_code_id' => $standardCode?->id,
                'unit_measure_id' => $unitMeasure?->factus_id,
                'price' => 50000.00,
                'tax_rate' => 19.00,
                'is_active' => true,
            ],
            [
                'name' => 'Servicio de Limpieza',
                'code_reference' => 'LIMP-001',
                'description' => 'Servicio de limpieza de habitación',
                'standard_code_id' => $standardCode?->id,
                'unit_measure_id' => $unitMeasure?->factus_id,
                'price' => 15000.00,
                'tax_rate' => 19.00,
                'is_active' => true,
            ],
            [
                'name' => 'Servicio de Lavandería',
                'code_reference' => 'LAV-001',
                'description' => 'Servicio de lavandería',
                'standard_code_id' => $standardCode?->id,
                'unit_measure_id' => $unitMeasure?->factus_id,
                'price' => 20000.00,
                'tax_rate' => 19.00,
                'is_active' => true,
            ],
            [
                'name' => 'Servicio de Restaurante',
                'code_reference' => 'REST-001',
                'description' => 'Servicio de restaurante',
                'standard_code_id' => $standardCode?->id,
                'unit_measure_id' => $unitMeasure?->factus_id,
                'price' => 25000.00,
                'tax_rate' => 19.00,
                'is_active' => true,
            ],
            [
                'name' => 'Servicio de Bar',
                'code_reference' => 'BAR-001',
                'description' => 'Servicio de bar',
                'standard_code_id' => $standardCode?->id,
                'unit_measure_id' => $unitMeasure?->factus_id,
                'price' => 15000.00,
                'tax_rate' => 19.00,
                'is_active' => true,
            ],
            [
                'name' => 'Servicio de Estacionamiento',
                'code_reference' => 'EST-001',
                'description' => 'Servicio de estacionamiento',
                'standard_code_id' => $standardCode?->id,
                'unit_measure_id' => $unitMeasure?->factus_id,
                'price' => 5000.00,
                'tax_rate' => 19.00,
                'is_active' => true,
            ],
            [
                'name' => 'Servicio de Internet/WiFi',
                'code_reference' => 'WIFI-001',
                'description' => 'Servicio de internet y WiFi',
                'standard_code_id' => $standardCode?->id,
                'unit_measure_id' => $unitMeasure?->factus_id,
                'price' => 10000.00,
                'tax_rate' => 19.00,
                'is_active' => true,
            ],
            [
                'name' => 'Servicio de TV por Cable',
                'code_reference' => 'TV-001',
                'description' => 'Servicio de televisión por cable',
                'standard_code_id' => $standardCode?->id,
                'unit_measure_id' => $unitMeasure?->factus_id,
                'price' => 8000.00,
                'tax_rate' => 19.00,
                'is_active' => true,
            ],
            [
                'name' => 'Servicio de Minibar',
                'code_reference' => 'MINI-001',
                'description' => 'Servicio de minibar',
                'standard_code_id' => $standardCode?->id,
                'unit_measure_id' => $unitMeasure?->factus_id,
                'price' => 12000.00,
                'tax_rate' => 19.00,
                'is_active' => true,
            ],
            [
                'name' => 'Servicio de Spa',
                'code_reference' => 'SPA-001',
                'description' => 'Servicio de spa y relajación',
                'standard_code_id' => $standardCode?->id,
                'unit_measure_id' => $unitMeasure?->factus_id,
                'price' => 80000.00,
                'tax_rate' => 19.00,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['code_reference' => $service['code_reference']],
                $service
            );
        }
    }
}

