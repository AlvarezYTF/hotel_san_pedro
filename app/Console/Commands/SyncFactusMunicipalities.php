<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FactusMunicipalityService;

class SyncFactusMunicipalities extends Command
{
    protected $signature = 'factus:sync-municipalities';
    protected $description = 'Sincronizar municipios desde Factus API';

    public function handle(FactusMunicipalityService $service): int
    {
        $this->info('Iniciando sincronización de municipios desde Factus...');

        try {
            $count = $service->sync();
            $this->info("✓ Sincronizados {$count} municipios exitosamente.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error al sincronizar municipios: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
