<?php

namespace App\Services;

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

            $next = Shift::create([
                'type' => $nextType,
                'status' => ShiftStatus::OPEN,
                'opened_at' => CarbonImmutable::now(),
                'opened_by' => $user->id,
                'base_snapshot' => $previous->closing_snapshot,
            ]);

            // Link handover record for audit trace if exists
            ShiftHandover::create([
                'entregado_por' => $previous->closed_by ?? $previous->opened_by,
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
        $count = 0;

        Shift::openOperational()->get()->each(function (Shift $shift) use ($admin, $now, $reason, &$count) {
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
            $count++;
        });

        return $count;
    }
}
