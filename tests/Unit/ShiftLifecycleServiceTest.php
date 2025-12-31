<?php

namespace Tests\Unit;

use App\Enums\ShiftStatus;
use App\Enums\ShiftType;
use App\Exceptions\ShiftRuleViolation;
use App\Models\Shift;
use App\Models\User;
use App\Services\ShiftLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftLifecycleServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): ShiftLifecycleService
    {
        return $this->app->make(ShiftLifecycleService::class);
    }

    private function baseSnapshot(float $base = 1000.0): array
    {
        return [
            'shift' => ['type' => ShiftType::DAY->value, 'date' => now()->toDateString()],
            'cash' => [
                'base_inicial' => $base,
                'entradas_efectivo' => 0.0,
                'entradas_transferencia' => 0.0,
                'salidas' => 0.0,
                'base_esperada' => $base,
            ],
            'meta' => ['captured_at' => now()->toIso8601String()],
        ];
    }

    public function test_open_fresh_enforces_single_operational_shift(): void
    {
        $user = User::factory()->create();

        $service = $this->service();
        $first = $service->openFresh($user, ShiftType::DAY, $this->baseSnapshot());

        $this->assertEquals(ShiftStatus::OPEN, $first->status);
        $this->assertEquals(1, Shift::count());

        $this->expectException(ShiftRuleViolation::class);
        $service->openFresh($user, ShiftType::DAY, $this->baseSnapshot());
    }

    public function test_open_fresh_allows_admin_shift_alongside_operational(): void
    {
        $user = User::factory()->create();

        $service = $this->service();
        $service->openFresh($user, ShiftType::DAY, $this->baseSnapshot());

        // Admin shift should not violate operational uniqueness rule
        $adminShift = $service->openFresh($user, ShiftType::ADMIN, $this->baseSnapshot());

        $this->assertEquals(ShiftType::ADMIN, $adminShift->type);
        $this->assertEquals(2, Shift::count());
    }

    public function test_close_requires_closing_snapshot(): void
    {
        $user = User::factory()->create();
        $service = $this->service();
        $shift = $service->openFresh($user, ShiftType::DAY, $this->baseSnapshot());

        $this->expectException(ShiftRuleViolation::class);
        $service->closeWithSnapshot($shift, $user, []);
    }

    public function test_close_with_snapshot_sets_state_and_timestamps(): void
    {
        $user = User::factory()->create();
        $service = $this->service();
        $shift = $service->openFresh($user, ShiftType::DAY, $this->baseSnapshot());

        $closingSnapshot = $this->baseSnapshot();
        $closed = $service->closeWithSnapshot($shift, $user, $closingSnapshot);

        $this->assertEquals(ShiftStatus::CLOSED, $closed->status);
        $this->assertNotNull($closed->closed_at);
        $this->assertEquals($closingSnapshot, $closed->closing_snapshot);
    }

    public function test_open_from_previous_requires_closed_shift_with_snapshot(): void
    {
        $user = User::factory()->create();
        $service = $this->service();
        $shift = $service->openFresh($user, ShiftType::DAY, $this->baseSnapshot());

        $this->expectException(ShiftRuleViolation::class);
        $service->openFromPrevious($shift, $user);
    }

    public function test_open_from_previous_uses_closing_snapshot_as_base(): void
    {
        $user = User::factory()->create();
        $service = $this->service();
        $shift = $service->openFresh($user, ShiftType::DAY, $this->baseSnapshot());

        $closingSnapshot = $this->baseSnapshot(1234.56);
        $closed = $service->closeWithSnapshot($shift, $user, $closingSnapshot);

        $next = $service->openFromPrevious($closed, $user);

        $this->assertEquals(ShiftStatus::OPEN, $next->status);
        $this->assertEquals($closingSnapshot, $next->base_snapshot);
        $this->assertEquals($closed->type, $next->type);
    }
}
