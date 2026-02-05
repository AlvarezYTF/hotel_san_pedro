<?php

namespace App\Console\Commands;

use App\Services\FactusApiService;
use App\Services\ElectronicInvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanPendingFactusBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'factus:clean-pending {--create-invoices : Crear facturas pendientes en la base de datos primero} {reference-codes?* : Códigos de referencia específicos a eliminar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eliminar facturas pendientes de Factus API';

    /**
     * Execute the console command.
     */
    public function handle(FactusApiService $factusApi, ElectronicInvoiceService $invoiceService)
    {
        $this->info('🧹 Limpiando facturas pendientes de Factus API...');

        // Códigos de referencia de los logs
        $defaultCodes = [
            'INV-20260205-69842F8276885',
            'INV-20260205-69842CB75560A',
            'INV-20260205-69842EC23932F',
            'INV-20260205-69842ED297233'
        ];

        $referenceCodes = $this->argument('reference-codes') ?: $defaultCodes;

        if (empty($referenceCodes)) {
            $this->error('No se proporcionaron códigos de referencia.');
            return 1;
        }

        $this->info('📋 Códigos a procesar:');
        foreach ($referenceCodes as $code) {
            $this->line("  - {$code}");
        }

        // Si se solicita, crear facturas pendientes primero
        if ($this->option('create-invoices')) {
            $this->newLine();
            $this->info('📝 Creando facturas pendientes en la base de datos...');
            
            $createResults = $invoiceService->createPendingInvoicesFromReferences($referenceCodes);
            
            $this->newLine();
            $this->info('📊 Resultados de creación:');
            
            foreach ($createResults as $code => $result) {
                if ($result['success']) {
                    $this->info("  ✅ {$code}: {$result['message']} (ID: {$result['invoice_id']})");
                } else {
                    $this->error("  ❌ {$code}: {$result['message']}");
                }
            }
        }

        $this->newLine();
        if (!$this->confirm('¿Desea continuar con la eliminación de Factus API?')) {
            $this->info('❌ Operación cancelada.');
            return 0;
        }

        try {
            $results = $factusApi->deletePendingBills($referenceCodes);

            $this->newLine();
            $this->info('📊 Resultados de eliminación:');

            $successCount = 0;
            $errorCount = 0;

            foreach ($results as $code => $result) {
                if ($result['success']) {
                    $this->info("  ✅ {$code}: {$result['message']}");
                    $successCount++;
                } else {
                    $this->error("  ❌ {$code}: {$result['message']}");
                    $errorCount++;
                }
            }

            $this->newLine();
            $this->info("📈 Resumen: {$successCount} eliminadas, {$errorCount} errores");

            if ($successCount > 0) {
                $this->newLine();
                $this->info('🎉 Ahora puedes intentar crear nuevas facturas electrónicas.');
                $this->info('💡 Usa el filtro "Pendientes" en la tabla para ver las facturas y eliminarlas desde la vista.');
            }

            return $errorCount > 0 ? 1 : 0;

        } catch (\Exception $e) {
            $this->error('❌ Error al limpiar facturas: ' . $e->getMessage());
            Log::error('Error en comando factus:clean-pending', [
                'error' => $e->getMessage(),
                'codes' => $referenceCodes
            ]);
            return 1;
        }
    }
}
