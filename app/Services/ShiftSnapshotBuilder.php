<?php

namespace App\Services;

use App\Models\ShiftHandover;
use Carbon\CarbonInterface;

class ShiftSnapshotBuilder
{
    public function baseFromHandover(ShiftHandover $handover): array
    {
        return [
            'shift' => [
                'handover_id' => $handover->id,
                'type' => $handover->shift_type?->value,
                'date' => $handover->shift_date?->toDateString(),
            ],
            'cash' => [
                'base_inicial' => (float) $handover->base_inicial,
                'entradas_efectivo' => (float) $handover->total_entradas_efectivo,
                'entradas_transferencia' => (float) $handover->total_entradas_transferencia,
                'salidas' => (float) $handover->total_salidas,
                'base_esperada' => (float) $handover->base_esperada,
            ],
            'meta' => [
                'captured_at' => $this->now()->toIso8601String(),
            ],
        ];
    }

    public function closingFromHandover(ShiftHandover $handover): array
    {
        return [
            'shift' => [
                'handover_id' => $handover->id,
                'type' => $handover->shift_type?->value,
                'date' => $handover->shift_date?->toDateString(),
            ],
            'cash' => [
                'base_final' => (float) $handover->base_final,
                'base_recibida' => (float) $handover->base_recibida,
                'entradas_efectivo' => (float) $handover->total_entradas_efectivo,
                'entradas_transferencia' => (float) $handover->total_entradas_transferencia,
                'salidas' => (float) $handover->total_salidas,
                'base_esperada' => (float) $handover->base_esperada,
                'diferencia' => (float) $handover->diferencia,
            ],
            'meta' => [
                'captured_at' => $this->now()->toIso8601String(),
                'ended_at' => $handover->ended_at?->toIso8601String(),
                'received_at' => $handover->received_at?->toIso8601String(),
            ],
        ];
    }

    private function now(): CarbonInterface
    {
        return now();
    }
}
