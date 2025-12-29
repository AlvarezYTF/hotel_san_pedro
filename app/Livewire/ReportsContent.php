<?php

namespace App\Livewire;

use App\Services\ReportService;
use Carbon\Carbon;
use Livewire\Component;

final class ReportsContent extends Component
{
    public string $entity_type = 'sales';

    public string $startDate = '';

    public string $endDate = '';

    public ?string $groupBy = null;

    public array $filters = [];

    public ?float $minAmount = null;

    public ?float $maxAmount = null;

    public ?int $minCount = null;

    public ?int $maxCount = null;

    public array $reportData = [];

    public bool $pausePolling = false;

    public function openDetails(string $type, int $id): void
    {
        $allowedTypes = ['sale', 'reservation', 'room', 'customer', 'product', 'receptionist'];
        if ($type === '' || !in_array($type, $allowedTypes, true) || $id <= 0) {
            throw new \DomainException('Invalid detail request.');
        }

        $this->dispatch('report-details', type: $type, id: $id);
    }

    public function mount(
        string $entity_type,
        string $startDate,
        string $endDate,
        ?string $groupBy,
        array $filters,
        ?float $minAmount,
        ?float $maxAmount,
        ?int $minCount,
        ?int $maxCount,
        bool $pausePolling
    ): void {
        $this->entity_type = $entity_type;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->groupBy = $groupBy;
        $this->filters = $filters;
        $this->minAmount = $minAmount;
        $this->maxAmount = $maxAmount;
        $this->minCount = $minCount;
        $this->maxCount = $maxCount;
        $this->pausePolling = $pausePolling;

        $this->loadReport();
    }

    public function loadReport(): void
    {
        $reportService = app(ReportService::class);

        $filters = $this->filters;
        if ($this->minAmount !== null) {
            $filters['min_amount'] = $this->minAmount;
        }
        if ($this->maxAmount !== null) {
            $filters['max_amount'] = $this->maxAmount;
        }
        if ($this->minCount !== null) {
            $filters['min_count'] = $this->minCount;
        }
        if ($this->maxCount !== null) {
            $filters['max_count'] = $this->maxCount;
        }

        $this->reportData = $reportService->generateReport(
            $this->entity_type,
            Carbon::parse($this->startDate),
            Carbon::parse($this->endDate),
            $this->groupBy,
            $filters
        );

        $this->dispatch('report-refreshed');
    }

    public function render()
    {
        $reportService = app(ReportService::class);

        return view('livewire.reports-content', [
            'entityTypeLabel' => $reportService->translateEntityType($this->entity_type),
            'groupByLabel' => $this->groupBy ? $reportService->translateGroupingOption($this->groupBy) : null,
        ]);
    }
}


