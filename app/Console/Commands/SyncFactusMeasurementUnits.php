<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FactusMeasurementUnitService;

class SyncFactusMeasurementUnits extends Command
{
    protected $signature = 'factus:sync-measurement-units';
    protected $description = 'Sincronizar unidades de medida desde Factus API';

    public function handle(FactusMeasurementUnitService $service): int
    {
        $this->info('Iniciando sincronización de unidades de medida desde Factus...');

        try {
            $count = $service->sync();
            $this->info("✓ Sincronizadas {$count} unidades de medida exitosamente.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error al sincronizar unidades de medida: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
