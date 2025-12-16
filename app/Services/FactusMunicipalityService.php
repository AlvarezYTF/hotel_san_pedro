<?php

namespace App\Services;

use App\Models\DianMunicipality;
use Illuminate\Support\Facades\Log;

class FactusMunicipalityService
{
    private FactusApiService $factusApi;

    public function __construct(FactusApiService $factusApi)
    {
        $this->factusApi = $factusApi;
    }

    public function sync(): int
    {
        try {
            $response = $this->factusApi->get('/v1/municipalities');
            
            $data = $response['data'] ?? [];
            
            if (empty($data)) {
                Log::warning('Factus API devolvió datos vacíos para municipalities');
                return 0;
            }

            $synced = 0;
            
            foreach ($data as $municipality) {
                DianMunicipality::updateOrCreate(
                    ['factus_id' => $municipality['id']],
                    [
                        'code' => $municipality['code'] ?? '',
                        'name' => $municipality['name'] ?? '',
                        'department' => $municipality['department'] ?? '',
                    ]
                );
                $synced++;
            }

            Log::info("Municipalities sincronizados desde Factus", ['count' => $synced]);
            
            return $synced;
        } catch (\Exception $e) {
            Log::error('Error al sincronizar municipalities desde Factus', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
