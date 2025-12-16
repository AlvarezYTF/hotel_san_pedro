<?php

namespace App\Services;

use App\Models\DianMeasurementUnit;
use Illuminate\Support\Facades\Log;

class FactusMeasurementUnitService
{
    private FactusApiService $factusApi;

    public function __construct(FactusApiService $factusApi)
    {
        $this->factusApi = $factusApi;
    }

    public function sync(): int
    {
        try {
            $response = $this->factusApi->get('/v1/measurement-units');
            
            $data = $response['data'] ?? [];
            
            if (empty($data)) {
                Log::warning('Factus API devolvió datos vacíos para measurement-units');
                return 0;
            }

            $synced = 0;
            
            foreach ($data as $unit) {
                DianMeasurementUnit::updateOrCreate(
                    ['factus_id' => $unit['id']],
                    [
                        'code' => $unit['code'] ?? '',
                        'name' => $unit['name'] ?? '',
                    ]
                );
                $synced++;
            }

            Log::info("Measurement units sincronizados desde Factus", ['count' => $synced]);
            
            return $synced;
        } catch (\Exception $e) {
            Log::error('Error al sincronizar measurement units desde Factus', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
