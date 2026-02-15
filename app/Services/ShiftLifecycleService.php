<?php

namespace App\Services;

use App\Enums\ShiftHandoverStatus;
use App\Enums\ShiftStatus;
use App\Enums\ShiftType;
use App\Exceptions\ShiftRuleViolation;
use App\Models\Shift;
use App\Models\ShiftHandover;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ShiftLifecycleService
{
    public function openFresh(User $user, ShiftType $type, array $baseSnapshot): Shift
    {
        return DB::transaction(function () use ($user, $type, $baseSnapshot) {
            $this->assertNoOpenOperationalShift($type);

            return Shift::create([
                'type' => $type,
                'status' => ShiftStatus::OPEN,
                'opened_at' => CarbonImmutable::now(),
                'opened_by' => $user->id,
                'base_snapshot' => $baseSnapshot,
            ]);
        });
    }

    public function openFromPrevious(Shift $previous, User $user, ?ShiftType $type = null): Shift
    {
        return DB::transaction(function () use ($previous, $user, $type) {
            if ($previous->status !== ShiftStatus::CLOSED) {
                throw new ShiftRuleViolation('El turno anterior debe estar cerrado antes de abrir el siguiente.');
            }

            if (!$previous->closing_snapshot) {
                throw new ShiftRuleViolation('El turno anterior no tiene closing_snapshot; no se puede iniciar el siguiente.');
            }

            $nextType = $type ?? $previous->type;
            $this->assertNoOpenOperationalShift($nextType);
            $delivererId = $previous->closed_by ?? $previous->opened_by;
            $delivererName = User::query()
                ->whereKey($delivererId)
                ->value('name');

            $next = Shift::create([
                'type' => $nextType,
                'status' => ShiftStatus::OPEN,
                'opened_at' => CarbonImmutable::now(),
                'opened_by' => $user->id,
                'base_snapshot' => $previous->closing_snapshot,
            ]);

            // Link handover record for audit trace if exists
            ShiftHandover::create([
                'entregado_por' => $delivererId,
                'receptionist_name' => $delivererName,
                'recibido_por' => $user->id,
                'shift_type' => $nextType->value,
                'shift_date' => CarbonImmutable::today(),
                'started_at' => $next->opened_at,
                'status' => 'entregado',
                'from_shift_id' => $previous->id,
                'to_shift_id' => $next->id,
                'summary' => $previous->closing_snapshot,
            ]);

            return $next;
        });
    }

    public function closeWithSnapshot(Shift $shift, User $user, array $closingSnapshot): Shift
    {
        return DB::transaction(function () use ($shift, $user, $closingSnapshot) {
            if ($shift->status !== ShiftStatus::OPEN) {
                throw new ShiftRuleViolation('Solo se puede cerrar un turno abierto.');
            }

            if (empty($closingSnapshot)) {
                throw new ShiftRuleViolation('Se requiere closing_snapshot para cerrar el turno.');
            }

            $shift->closing_snapshot = $closingSnapshot;
            $shift->closed_at = CarbonImmutable::now();
            $shift->closed_by = $user->id;
            $shift->status = ShiftStatus::CLOSED;
            $shift->save();

            return $shift;
        });
    }

    private function assertNoOpenOperationalShift(ShiftType $type): void
    {
        if (!in_array($type, [ShiftType::DAY, ShiftType::NIGHT], true)) {
            return; // admin shifts do not count for uniqueness
        }

        $exists = Shift::openOperational()->exists();
        if ($exists) {
            throw new ShiftRuleViolation('Ya existe un turno operativo abierto.');
        }
    }

    public function forceCloseOperational(?int $adminId = null, string $reason = 'Cierre forzado por administrador'): int
    {
        $admin = $adminId ? User::find($adminId) : null;
        $now = CarbonImmutable::now();

        return DB::transaction(function () use ($admin, $now, $reason) {
            $count = 0;

            Shift::openOperational()
                ->lockForUpdate()
                ->get()
                ->each(function (Shift $shift) use ($admin, $now, $reason, &$count) {
                    $closingSnapshot = $shift->closing_snapshot ?? $shift->base_snapshot ?? [
                        'shift' => [
                            'type' => $shift->type->value,
                            'date' => $shift->opened_at?->toDateString(),
                        ],
                        'meta' => [
                            'forced_reason' => $reason,
                        ],
                    ];

                    $shift->closing_snapshot = $closingSnapshot;
                    $shift->status = ShiftStatus::CLOSED;
                    $shift->closed_at = $now;
                    if ($admin) {
                        $shift->closed_by = $admin->id;
                    }
                    $shift->save();

                    // Cerrar también el handover activo vinculado para evitar estados colgados.
                    ShiftHandover::query()
                        ->where("from_shift_id", $shift->id)
                        ->where("status", ShiftHandoverStatus::ACTIVE)
                        ->lockForUpdate()
                        ->get()
                        ->each(function (ShiftHandover $handover) use (
                            $now,
                            $reason,
                            $admin,
                            $shift,
                            $closingSnapshot,
                        ) {
                            $handover->updateTotals();
                            $handover->ended_at = $handover->ended_at ?: $now;
                            $handover->base_final = $handover->base_final ??
                                $handover->base_esperada ??
                                $handover->base_inicial;
                            $handover->status = ShiftHandoverStatus::CLOSED;
                            $handover->validated_by = $admin?->id;
                            $handover->from_shift_id = $handover->from_shift_id ?: $shift->id;
                            $handover->summary = $handover->summary ?: $closingSnapshot;

                            $forcedNote = "[FORZADO ADMIN {$now->format('Y-m-d H:i')}] {$reason}";
                            $existing = trim((string) ($handover->observaciones_entrega ?? ""));
                            $handover->observaciones_entrega = $existing !== ""
                                ? "{$existing}\n{$forcedNote}"
                                : $forcedNote;

                            $handover->save();
                        });

                    $count++;
                });

            return $count;
        });
    }

    /**
     * Reset shift chain when opening is disabled:
     * - close open operational shifts
     * - close delivered handovers pending reception
     * - close orphan active handovers
     */
    public function resetOperationalChain(
        ?int $adminId = null,
        string $reason = "Reinicio administrativo de turnos",
    ): array {
        $admin = $adminId ? User::find($adminId) : null;
        $now = CarbonImmutable::now();

        return DB::transaction(function () use ($admin, $adminId, $reason, $now) {
            $operationalShiftsClosed = $this->forceCloseOperational(
                $adminId,
                $reason,
            );

            $resetNote = "[RESET ADMIN {$now->format('Y-m-d H:i')}] {$reason}";

            $deliveredClosed = 0;
            ShiftHandover::query()
                ->where("status", ShiftHandoverStatus::DELIVERED)
                ->lockForUpdate()
                ->get()
                ->each(function (ShiftHandover $handover) use (
                    $admin,
                    $now,
                    $resetNote,
                    &$deliveredClosed,
                ) {
                    $handover->ended_at = $handover->ended_at ?: $now;
                    $handover->validated_by = $admin?->id;
                    $handover->status = ShiftHandoverStatus::CLOSED;

                    $existing = trim(
                        (string) ($handover->observaciones_recepcion ?? ""),
                    );
                    $handover->observaciones_recepcion = $existing !== ""
                        ? "{$existing}\n{$resetNote}"
                        : $resetNote;

                    $handover->save();
                    $deliveredClosed++;
                });

            $activeOrphansClosed = 0;
            ShiftHandover::query()
                ->where("status", ShiftHandoverStatus::ACTIVE)
                ->lockForUpdate()
                ->get()
                ->each(function (ShiftHandover $handover) use (
                    $admin,
                    $now,
                    $resetNote,
                    &$activeOrphansClosed,
                ) {
                    $handover->updateTotals();
                    $handover->ended_at = $handover->ended_at ?: $now;
                    $handover->base_final = $handover->base_final ??
                        $handover->base_esperada ??
                        $handover->base_inicial;
                    $handover->validated_by = $admin?->id;
                    $handover->status = ShiftHandoverStatus::CLOSED;

                    $existing = trim(
                        (string) ($handover->observaciones_entrega ?? ""),
                    );
                    $handover->observaciones_entrega = $existing !== ""
                        ? "{$existing}\n{$resetNote}"
                        : $resetNote;

                    $handover->save();
                    $activeOrphansClosed++;
                });

            return [
                "operational_shifts_closed" => $operationalShiftsClosed,
                "delivered_closed" => $deliveredClosed,
                "active_orphans_closed" => $activeOrphansClosed,
            ];
        });
    }
}
