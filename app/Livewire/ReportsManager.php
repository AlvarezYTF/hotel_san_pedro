<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use App\Services\ReportService;
use App\Models\Room;
use App\Models\User;
use App\Models\Customer;
use App\Models\Category;
use App\Models\DianDocumentType;
use App\Models\Sale;
use App\Models\Reservation;
use Carbon\Carbon;

final class ReportsManager extends Component
{
    #[Url(except: 'sales')]
    public string $entity_type = 'sales';

    #[Url(except: '')]
    public string $startDate = '';

    #[Url(except: '')]
    public string $endDate = '';

    #[Url(except: '')]
    public ?string $groupBy = null;

    public array $filters = [];
    public string $searchQuery = '';
    public ?int $selectedDetailId = null;
    public ?string $selectedDetailType = null;
    public ?float $minAmount = null;
    public ?float $maxAmount = null;
    public ?int $minCount = null;
    public ?int $maxCount = null;
    public ?string $activePreset = null;
    
    public array $reportData = [];
    public array $groupingOptions = [];
    public array $filterOptions = [];

    public string $applied_entity_type = 'sales';
    public string $applied_startDate = '';
    public string $applied_endDate = '';
    public ?string $applied_groupBy = null;
    public array $applied_filters = [];
    public ?float $applied_minAmount = null;
    public ?float $applied_maxAmount = null;
    public ?int $applied_minCount = null;
    public ?int $applied_maxCount = null;
    public int $appliedRevision = 0;

    public function mount(): void
    {
        $this->assertEntityTypeIsAllowed($this->entity_type);

        if ($this->startDate === '') {
            $this->startDate = now()->startOfMonth()->format('Y-m-d');
        }
        if ($this->endDate === '') {
            $this->endDate = now()->format('Y-m-d');
        }
        $this->updateOptions();

        $this->applied_entity_type = $this->entity_type;
        $this->applied_startDate = $this->startDate;
        $this->applied_endDate = $this->endDate;
        $this->applied_groupBy = $this->groupBy;
        $this->applied_filters = $this->filters;
        $this->applied_minAmount = $this->minAmount;
        $this->applied_maxAmount = $this->maxAmount;
        $this->applied_minCount = $this->minCount;
        $this->applied_maxCount = $this->maxCount;
    }

    public function updatedEntityType(): void
    {
        $this->applyEntityTypeChange();
    }

    public function setEntityType(string $entityType): void
    {
        $this->entity_type = $entityType;
        $this->applyEntityTypeChange();
    }

    private function applyEntityTypeChange(): void
    {
        $this->entity_type = trim($this->entity_type);
        $this->assertEntityTypeIsAllowed($this->entity_type);

        $this->groupBy = null;
        $this->filters = [];
        $this->minAmount = null;
        $this->maxAmount = null;
        $this->minCount = null;
        $this->maxCount = null;
        $this->reportData = [];
        $this->selectedDetailId = null;
        $this->selectedDetailType = null;
        $this->updateOptions();
        $this->applyFilters();
    }

    #[On('report-details')]
    public function onReportDetails(string $type, int $id): void
    {
        $allowedTypes = ['sale', 'reservation', 'room', 'customer', 'product', 'receptionist'];
        if ($type === '' || !in_array($type, $allowedTypes, true) || $id <= 0) {
            throw new \DomainException('Invalid detail request.');
        }

        $this->showDetails($type, $id);
    }

    public function applyFilters(): void
    {
        $this->assertEntityTypeIsAllowed($this->entity_type);

        $this->applied_entity_type = $this->entity_type;
        $this->applied_startDate = $this->startDate;
        $this->applied_endDate = $this->endDate;
        $this->applied_groupBy = $this->groupBy;
        $this->applied_filters = $this->filters;
        $this->applied_minAmount = $this->minAmount;
        $this->applied_maxAmount = $this->maxAmount;
        $this->applied_minCount = $this->minCount;
        $this->applied_maxCount = $this->maxCount;
        $this->appliedRevision++;
    }

    public function hasPendingChanges(): bool
    {
        return $this->getDraftHash() !== $this->getAppliedHash();
    }

    private function getDraftHash(): string
    {
        return $this->makeStateHash(
            $this->entity_type,
            $this->startDate,
            $this->endDate,
            $this->groupBy,
            $this->filters,
            $this->minAmount,
            $this->maxAmount,
            $this->minCount,
            $this->maxCount,
            null
        );
    }

    private function getAppliedHash(): string
    {
        return $this->makeStateHash(
            $this->applied_entity_type,
            $this->applied_startDate,
            $this->applied_endDate,
            $this->applied_groupBy,
            $this->applied_filters,
            $this->applied_minAmount,
            $this->applied_maxAmount,
            $this->applied_minCount,
            $this->applied_maxCount,
            $this->appliedRevision
        );
    }

    private function makeStateHash(
        string $entity_type,
        string $startDate,
        string $endDate,
        ?string $groupBy,
        array $filters,
        ?float $minAmount,
        ?float $maxAmount,
        ?int $minCount,
        ?int $maxCount,
        ?int $revision
    ): string {
        $normalizedFilters = $filters;
        ksort($normalizedFilters);

        return md5(json_encode([
            'entity_type' => $entity_type,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'groupBy' => $groupBy ?? '',
            'filters' => $normalizedFilters,
            'minAmount' => $minAmount,
            'maxAmount' => $maxAmount,
            'minCount' => $minCount,
            'maxCount' => $maxCount,
            'revision' => $revision,
        ]));
    }

    public function updatedGroupBy(): void
    {
        $this->applyFilters();
    }

    public function updatedStartDate(): void
    {
        $this->activePreset = null;
        $this->applyFilters();
    }

    public function updatedEndDate(): void
    {
        $this->activePreset = null;
        $this->applyFilters();
    }

    public function updatedFilters(): void
    {
        $this->applyFilters();
    }

    public function updatedMinAmount(): void
    {
        $this->applyFilters();
    }

    public function updatedMaxAmount(): void
    {
        $this->applyFilters();
    }

    public function updatedMinCount(): void
    {
        $this->applyFilters();
    }

    public function updatedMaxCount(): void
    {
        $this->applyFilters();
    }

    public function updateOptions(): void
    {
        $reportService = app(ReportService::class);
        $this->groupingOptions = $reportService->getGroupingOptions($this->entity_type);
        $this->filterOptions = $reportService->getFilterOptions($this->entity_type);
    }

    private function assertEntityTypeIsAllowed(string $entityType): void
    {
        $availableReports = $this->getAvailableReports();
        if (!array_key_exists($entityType, $availableReports)) {
            throw new \DomainException("Invalid report module: {$entityType}");
        }
    }

    public function loadReport(): void
    {
        $reportService = app(ReportService::class);
        
        $filters = $this->filters;
        if ($this->minAmount !== null) $filters['min_amount'] = $this->minAmount;
        if ($this->maxAmount !== null) $filters['max_amount'] = $this->maxAmount;
        if ($this->minCount !== null) $filters['min_count'] = $this->minCount;
        if ($this->maxCount !== null) $filters['max_count'] = $this->maxCount;
        
        $this->reportData = $reportService->generateReport(
            $this->entity_type,
            Carbon::parse($this->startDate),
            Carbon::parse($this->endDate),
            $this->groupBy,
            $filters
        );
        $this->dispatch('report-refreshed');
    }

    public function getRoomsProperty()
    {
        return Room::orderBy('room_number')->get(['id', 'room_number', 'status']);
    }

    public function getReceptionistsProperty()
    {
        return User::role(['Recepcionista Día', 'Recepcionista Noche'])->orderBy('name')->get(['id', 'name']);
    }

    public function getCustomersProperty()
    {
        return Customer::orderBy('name')->get(['id', 'name']);
    }

    public function getCategoriesProperty()
    {
        return Category::orderBy('name')->get(['id', 'name']);
    }

    public function getDocumentTypesProperty()
    {
        return DianDocumentType::orderBy('name')->get(['id', 'name']);
    }

    public function clearFilters(): void
    {
        $this->filters = [];
        $this->minAmount = null;
        $this->maxAmount = null;
        $this->minCount = null;
        $this->maxCount = null;
        $this->loadReport();
    }

    public function removeFilter(string $key): void
    {
        unset($this->filters[$key]);
        $this->loadReport();
    }

    public function getActiveFiltersCount(): int
    {
        $count = count(array_filter($this->filters, fn($value) => $value !== null && $value !== ''));
        if ($this->minAmount !== null) $count++;
        if ($this->maxAmount !== null) $count++;
        if ($this->minCount !== null) $count++;
        if ($this->maxCount !== null) $count++;
        return $count;
    }

    public function getFilterLabel(string $key): string
    {
        $controller = app(\App\Http\Controllers\ReportController::class);
        return $controller->getFilterLabel($key);
    }

    public function getFilterValue(string $key): string
    {
        $value = $this->filters[$key] ?? null;
        $controller = app(\App\Http\Controllers\ReportController::class);
        return $controller->getFilterValue($key, $value);
    }

    public function applyPreset(string $preset): void
    {
        $this->activePreset = $preset;
        match($preset) {
            'today' => [
                $this->startDate = now()->format('Y-m-d'),
                $this->endDate = now()->format('Y-m-d'),
            ],
            'yesterday' => [
                $this->startDate = now()->subDay()->format('Y-m-d'),
                $this->endDate = now()->subDay()->format('Y-m-d'),
            ],
            'this_week' => [
                $this->startDate = now()->startOfWeek()->format('Y-m-d'),
                $this->endDate = now()->endOfWeek()->format('Y-m-d'),
            ],
            'this_month' => [
                $this->startDate = now()->startOfMonth()->format('Y-m-d'),
                $this->endDate = now()->endOfMonth()->format('Y-m-d'),
            ],
            'last_month' => [
                $this->startDate = now()->subMonth()->startOfMonth()->format('Y-m-d'),
                $this->endDate = now()->subMonth()->endOfMonth()->format('Y-m-d'),
            ],
            'this_year' => [
                $this->startDate = now()->startOfYear()->format('Y-m-d'),
                $this->endDate = now()->endOfYear()->format('Y-m-d'),
            ],
            default => null,
        };
        $this->loadReport();
    }

    public function getPresetLabel(string $preset): string
    {
        $controller = app(\App\Http\Controllers\ReportController::class);
        return $controller->getPresetLabel($preset);
    }

    public function getAvailableReports(): array
    {
        $controller = app(\App\Http\Controllers\ReportController::class);
        return $controller->getAvailableReports();
    }

    public function updatedSearchQuery(): void
    {
        // Intentionally left blank: report loading is handled by the content subcomponent polling.
    }

    public function showDetails(string $type, int $id): void
    {
        $this->selectedDetailType = $type;
        $this->selectedDetailId = $id;
    }

    public function closeDetails(): void
    {
        $this->selectedDetailId = null;
        $this->selectedDetailType = null;
    }

    public function getDetailData(): ?array
    {
        if (!$this->selectedDetailType || !$this->selectedDetailId) {
            return null;
        }

        return match ($this->selectedDetailType) {
            'sale' => $this->getSaleDetail(),
            'reservation' => $this->getReservationDetail(),
            'room' => $this->getRoomDetail(),
            'customer' => $this->getCustomerDetail(),
            'product' => $this->getProductDetail(),
            'receptionist' => $this->getReceptionistDetail(),
            default => null,
        };
    }

    public function getSaleDetail(): ?array
    {
        $sale = Sale::with(['user', 'room', 'items.product', 'electronicInvoice'])
            ->find($this->selectedDetailId);

        if (!$sale) return null;

        $reservation = null;
        $customer = null;
        if ($sale->room_id) {
            $reservation = Reservation::where('room_id', $sale->room_id)
                ->where('check_in_date', '<=', $sale->sale_date)
                ->where('check_out_date', '>=', $sale->sale_date)
                ->with(['customer.taxProfile'])
                ->first();
            $customer = $reservation->customer ?? null;
        }

        $movements = \App\Models\InventoryMovement::where('reason', 'like', "%Venta #{$sale->id}%")
            ->with('product')
            ->get();

        return [
            'type' => 'Venta',
            'title' => "Expediente de Venta #{$sale->id}",
            'basic_info' => [
                'ID Registro' => $sale->id,
                'Fecha Contable' => Carbon::parse($sale->sale_date)->translatedFormat('d \d\e F, Y'),
                'Hora Registro' => $sale->created_at->format('H:i:s'),
                'Registrado por' => $sale->user->name ?? 'Sistema',
                'Email Recepcionista' => $sale->user->email ?? 'N/A',
                'Tipo de Operación' => $sale->room_id ? 'Venta a Habitación' : 'Venta Directa (Sin Hab.)',
            ],
            'financial_info' => [
                'Monto Bruto' => $sale->total,
                'Método de Pago' => ucfirst($sale->payment_method),
                'Efectivo Recibido' => $sale->cash_amount ?? 0,
                'Transferencia Recibida' => $sale->transfer_amount ?? 0,
                'Estado de Cobro' => ucfirst($sale->debt_status),
                'Saldo Pendiente' => ($sale->debt_status === 'pendiente') ? $sale->total : 0,
            ],
            'room_context' => $sale->room_id ? [
                'Habitación' => "Habitación " . ($sale->room->room_number ?? 'N/A'),
                'Tipo' => $sale->room->type ?? 'Estándar',
                'Estado Actual' => $sale->room->status->label() ?? 'N/A',
            ] : null,
            'customer_info' => $customer ? [
                'Nombre Completo' => $customer->name,
                'Documento' => $customer->taxProfile->identification ?? 'N/A',
                'Teléfono' => $customer->phone ?? 'N/A',
                'Email Contacto' => $customer->email ?? 'N/A',
                'Facturación Electrónica' => $customer->requires_electronic_invoice ? 'SÍ' : 'NO',
            ] : null,
            'electronic_invoice' => $sale->electronicInvoice ? [
                'Número Factura' => $sale->electronicInvoice->document,
                'Estado DIAN' => ucfirst($sale->electronicInvoice->status),
                'CUFE' => $sale->electronicInvoice->cufe ?? 'Pendiente',
                'Fecha Emisión' => $sale->electronicInvoice->created_at->format('d/m/Y H:i'),
            ] : null,
            'items' => $sale->items->map(fn($item) => [
                'name' => $item->product->name ?? 'Producto Eliminado',
                'sku' => $item->product->sku ?? 'N/A',
                'quantity' => $item->quantity,
                'price' => $item->unit_price,
                'total' => $item->total,
            ])->toArray(),
            'inventory_impact' => $movements->map(fn($m) => [
                'product' => $m->product->name ?? 'N/A',
                'change' => $m->quantity,
                'stock_before' => $m->previous_stock,
                'stock_after' => $m->current_stock,
            ])->toArray(),
            'history' => [],
            'notes' => $sale->notes,
            'total' => $sale->total,
        ];
    }

    public function getReservationDetail(): ?array
    {
        $res = Reservation::with(['customer.taxProfile', 'room'])
            ->find($this->selectedDetailId);
        
        if (!$res) return null;

        $sales = Sale::where('room_id', $res->room_id)
            ->whereBetween('sale_date', [$res->check_in_date, $res->check_out_date])
            ->with('items.product')
            ->get();

        return [
            'type' => 'Reservación',
            'title' => "Expediente de Reserva #{$res->id}",
            'stay_info' => [
                'ID Reserva' => $res->id,
                'Huésped Principal' => $res->customer->name ?? 'N/A',
                'Habitación Asignada' => "Habitación " . ($res->room->room_number ?? 'N/A'),
                'Fecha Ingreso' => Carbon::parse($res->check_in_date)->translatedFormat('d \d\e F, Y'),
                'Fecha Salida' => Carbon::parse($res->check_out_date)->translatedFormat('d \d\e F, Y'),
                'Noches de Estancia' => Carbon::parse($res->check_in_date)->diffInDays(Carbon::parse($res->check_out_date)),
                'Huéspedes' => $res->guests_count ?? 1,
            ],
            'financial_summary' => [
                'Costo Hospedaje' => $res->total_amount,
                'Depósito Recibido' => $res->deposit,
                'Saldo Pendiente Hospedaje' => $res->total_amount - $res->deposit,
                'Total Consumos Extra' => $sales->sum('total'),
                'GRAN TOTAL OPERACIÓN' => $res->total_amount + $sales->sum('total'),
            ],
            'customer_profile' => [
                'Nombre' => $res->customer->name ?? 'N/A',
                'Documento' => $res->customer->taxProfile->identification ?? 'N/A',
                'Teléfono' => $res->customer->phone ?? 'N/A',
                'Email' => $res->customer->email ?? 'N/A',
                'Facturación Electrónica' => ($res->customer->requires_electronic_invoice ?? false) ? 'SÍ' : 'NO',
            ],
            'sales_history' => $sales->map(fn($s) => [
                'id' => $s->id,
                'date' => $s->sale_date,
                'total' => $s->total,
                'status' => $s->debt_status,
                'items' => $s->items->map(fn($i) => ($i->product->name ?? 'N/A') . " (x{$i->quantity})")->implode(', '),
            ])->toArray(),
            'history' => [],
            'notes' => $res->notes,
            'total' => $res->total_amount,
        ];
    }

    public function getRoomDetail(): ?array
    {
        $room = Room::with(['rates'])->find($this->selectedDetailId);
        if (!$room) return null;

        $reservations = Reservation::where('room_id', $room->id)
            ->with('customer')
            ->latest()
            ->limit(20)
            ->get();

        $sales = Sale::where('room_id', $room->id)
            ->latest()
            ->limit(20)
            ->get();

        return [
            'type' => 'Habitación',
            'title' => "Maestro Habitación {$room->room_number}",
            'specs' => [
                'Número' => $room->room_number,
                'Beds' => $room->beds_count,
                'Estado Actual' => $room->status->label(),
                'Capacidad Máxima' => $room->max_capacity ?? 'N/A',
                'Precio Base' => $room->price_per_night,
            ],
            'performance_metrics' => [
                'Total Reservas Históricas' => Reservation::where('room_id', $room->id)->count(),
                'Total Ventas Históricas' => Sale::where('room_id', $room->id)->count(),
                'Ingresos Totales Generados' => Reservation::where('room_id', $room->id)->sum('total_amount') + Sale::where('room_id', $room->id)->sum('total'),
            ],
            'recent_reservations' => $reservations->map(fn($r) => [
                'id' => $r->id,
                'customer' => $r->customer->name ?? 'Anónimo',
                'date' => $r->check_in_date,
                'total' => $r->total_amount,
            ])->toArray(),
            'recent_activity' => $sales->map(fn($s) => [
                'id' => $s->id,
                'date' => $s->sale_date,
                'total' => $s->total,
            ])->toArray(),
        ];
    }

    public function getCustomerDetail(): ?array
    {
        $customer = Customer::with(['taxProfile', 'reservations.room'])
            ->find($this->selectedDetailId);
        
        if (!$customer) return null;

        // Sumar todas las ventas asociadas a las habitaciones durante sus estancias
        $totalSales = 0;
        foreach ($customer->reservations as $res) {
            $totalSales += Sale::where('room_id', $res->room_id)
                ->whereBetween('sale_date', [$res->check_in_date, $res->check_out_date])
                ->sum('total');
        }

        return [
            'type' => 'Cliente',
            'title' => "Perfil de Cliente: {$customer->name}",
            'contact_info' => [
                'Nombre Completo' => $customer->name,
                'Email' => $customer->email ?? 'N/A',
                'Teléfono' => $customer->phone ?? 'N/A',
                'Dirección' => $customer->address ?? 'N/A',
                'Ciudad' => $customer->city ?? 'N/A',
            ],
            'tax_info' => [
                'Identificación' => $customer->taxProfile ? "{$customer->taxProfile->identification}" : 'N/A',
                'Perfil Fiscal' => $customer->taxProfile ? 'Habilitado' : 'No configurado',
                'Requiere Factura Electrónica' => $customer->requires_electronic_invoice ? 'SÍ' : 'NO',
            ],
            'performance_stats' => [
                'Total Visitas' => $customer->reservations->count(),
                'Inversión en Hospedaje' => $customer->reservations->sum('total_amount'),
                'Inversión en Consumos' => $totalSales,
                'TOTAL CLIENTE' => $customer->reservations->sum('total_amount') + $totalSales,
            ],
            'recent_reservations' => $customer->reservations->map(fn($r) => [
                'id' => $r->id,
                'hab' => $r->room->room_number ?? 'N/A',
                'date' => $r->check_in_date,
                'total' => $r->total_amount,
            ])->toArray(),
        ];
    }

    public function getProductDetail(): ?array
    {
        $product = \App\Models\Product::with(['category'])->find($this->selectedDetailId);
        if (!$product) return null;

        $movements = \App\Models\InventoryMovement::where('product_id', $product->id)
            ->with('user')
            ->latest()
            ->limit(50)
            ->get();

        return [
            'type' => 'Producto',
            'title' => "Ficha Técnica: {$product->name}",
            'specs' => [
                'ID Interno' => $product->id,
                'Nombre' => $product->name,
                'SKU' => $product->sku ?? 'N/A',
                'Categoría' => $product->category->name ?? 'Sin Categoría',
                'Grupo' => str_contains(strtolower($product->category->name ?? ''), 'aseo') ? 'Insumos de Aseo' : 'Productos de Venta',
                'Stock Actual' => $product->quantity,
                'Precio Venta' => $product->price,
                'Estado' => ucfirst($product->status),
            ],
            'movements' => $movements->map(fn($m) => [
                'date' => $m->created_at->format('d/m/Y H:i'),
                'type' => strtoupper($m->type),
                'qty' => $m->quantity,
                'reason' => $m->reason,
                'user' => $m->user->name ?? 'N/A',
                'balance' => $m->current_stock,
            ])->toArray(),
        ];
    }

    public function getReceptionistDetail(): ?array
    {
        $user = User::withCount('sales')->find($this->selectedDetailId);
        if (!$user) return null;

        $lastSales = Sale::where('user_id', $user->id)->latest()->limit(20)->get();

        return [
            'type' => 'Staff',
            'title' => "Ficha de Staff: {$user->name}",
            'staff_profile' => [
                'ID Usuario' => $user->id,
                'Nombre Completo' => $user->name,
                'Email Corporativo' => $user->email,
                'Rol Asignado' => $user->getRoleNames()->first() ?? 'Staff',
                'Fecha Vinculación' => $user->created_at->format('d/m/Y'),
            ],
            'performance_metrics' => [
                'Ventas Totales Registradas' => $user->sales_count,
                'Volumen de Recaudación' => $user->sales()->sum('total'),
                'Ventas en Efectivo' => $user->sales()->where('payment_method', 'efectivo')->sum('total'),
                'Ventas por Transferencia' => $user->sales()->where('payment_method', 'transferencia')->sum('total'),
            ],
            'recent_activity' => $lastSales->map(fn($s) => [
                'id' => $s->id,
                'date' => $s->sale_date,
                'total' => $s->total,
            ])->toArray(),
        ];
    }

    public function downloadSinglePdf()
    {
        if (!$this->selectedDetailType || !$this->selectedDetailId) return;

        return redirect()->route('reports.single.pdf', [
            'type' => $this->selectedDetailType,
            'id' => $this->selectedDetailId
        ]);
    }

    public function render()
    {
        $reportService = app(ReportService::class);
        
        return view('livewire.reports-manager', [
            'rooms' => $this->rooms,
            'receptionists' => $this->receptionists,
            'customers' => $this->customers,
            'categories' => $this->categories,
            'documentTypes' => $this->documentTypes,
            'entityTypeLabel' => $reportService->translateEntityType($this->entity_type),
            'groupByLabel' => $this->groupBy ? $reportService->translateGroupingOption($this->groupBy) : null,
            'availableReports' => $this->getAvailableReports(),
            'filterOptions' => $this->filterOptions,
            'groupingOptions' => $this->groupingOptions,
        ]);
    }
}
