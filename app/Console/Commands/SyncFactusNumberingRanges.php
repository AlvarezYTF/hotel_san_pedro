<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FactusNumberingRangeService;

class SyncFactusNumberingRanges extends Command
{
    protected $signature = 'factus:sync-numbering-ranges';
    protected $description = 'Sincronizar rangos de numeración desde Factus API';

    public function handle(FactusNumberingRangeService $service): int
    {
        $this->info('Iniciando sincronización de rangos de numeración desde Factus...');

        try {
            $count = $service->sync();
            $this->info("✓ Sincronizados {$count} rangos de numeración exitosamente.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error al sincronizar rangos de numeración: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
