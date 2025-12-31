<?php

namespace App\Console\Commands;

use App\Enums\ShiftStatus;
use App\Models\Shift;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class ForceCloseOperationalShifts extends Command
{
    protected $signature = 'shifts:force-close {--admin_id=} {--reason=Manual admin closure}';

    protected $description = 'Force close all open operational shifts (day/night) and mark them closed when the chain is stuck.';

    public function handle(): int
    {
        $adminId = $this->option('admin_id');
        $reason = (string) $this->option('reason');
        $admin = $adminId ? User::find($adminId) : null;

        $now = CarbonImmutable::now();

        $count = 0;
        Shift::openOperational()->get()->each(function (Shift $shift) use ($admin, $now, $reason, &$count) {
            // If there is no closing snapshot, preserve base_snapshot to avoid nulls
            $closingSnapshot = $shift->closing_snapshot ?? $shift->base_snapshot ?? [
                'shift' => ['type' => $shift->type->value, 'date' => $shift->opened_at?->toDateString()],
                'meta' => ['forced_reason' => $reason],
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

        $this->info("Closed {$count} operational shift(s).");
        return Command::SUCCESS;
    }
}