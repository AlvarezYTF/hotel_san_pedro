<div wire:key="reports-content-root-{{ $entity_type }}">
    @if(!$pausePolling)
        <div wire:poll.visible.15s="loadReport" class="hidden"></div>
    @endif

    <!-- Report Content -->
    <div class="transition-opacity duration-200">
        @if(!empty($reportData))
            <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6" wire:key="report-content-{{ $entity_type }}">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Reporte de {{ $entityTypeLabel }} -
                        {{ \Illuminate\Support\Carbon::parse($startDate)->translatedFormat('d/m/Y') }} al
                        {{ \Illuminate\Support\Carbon::parse($endDate)->translatedFormat('d/m/Y') }}
                    </h2>

                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('reports.pdf') }}" target="_blank" class="inline">
                            @csrf
                            <input type="hidden" name="entity_type" value="{{ $entity_type }}">
                            <input type="hidden" name="start_date" value="{{ $startDate }}">
                            <input type="hidden" name="end_date" value="{{ $endDate }}">
                            @if($groupBy)
                                <input type="hidden" name="group_by" value="{{ $groupBy }}">
                            @endif
                            @foreach($filters as $key => $value)
                                <input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">
                            @endforeach
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors">
                                <i class="fas fa-file-pdf mr-2"></i>
                                Exportar PDF
                            </button>
                        </form>
                    </div>
                </div>

                @if(isset($reportData['summary']))
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" wire:key="summary-cards-{{ $entity_type }}">
                        @foreach($reportData['summary'] as $key => $value)
                            @if(is_numeric($value) && !is_array($value))
                                <div class="bg-gradient-to-br from-violet-50 to-violet-100 rounded-xl p-4 border border-violet-200 hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-xs font-semibold text-violet-700 uppercase tracking-wider">{{ app(\App\Services\ReportService::class)->translateSummaryKey($key) }}</p>
                                        @if(str_contains($key, 'total') || str_contains($key, 'amount') || str_contains($key, 'sales') || str_contains($key, 'revenue'))
                                            <i class="fas fa-dollar-sign text-violet-600 text-xs"></i>
                                        @elseif(str_contains($key, 'count'))
                                            <i class="fas fa-hashtag text-violet-600 text-xs"></i>
                                        @endif
                                    </div>
                                    <p class="text-2xl font-bold text-violet-900 mt-1">
                                        @if(str_contains($key, 'revenue') || str_contains($key, 'amount') || str_contains($key, 'cash') || str_contains($key, 'transfer') || str_contains($key, 'debt') || str_contains($key, 'deposit') || str_contains($key, 'pending') ||
                                            (str_contains($key, 'total') && !str_contains($key, 'count') && !str_contains($key, 'products') && !str_contains($key, 'rooms') && !str_contains($key, 'reservations') && !str_contains($key, 'receptionists') && !str_contains($key, 'customers')) ||
                                            (str_contains($key, 'sales') && !str_contains($key, 'count')))
                                            ${{ number_format($value, 2, ',', '.') }}
                                        @else
                                            {{ number_format($value, 0, ',', '.') }}
                                        @endif
                                    </p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                <!-- Charts -->
                @if(!empty($reportData) && (isset($reportData['grouped']) || isset($reportData['summary']) || isset($reportData['detailed_data'])))
                    <div class="mb-6" wire:key="charts-section-{{ $entity_type }}">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center">
                            <i class="fas fa-chart-bar mr-2 text-violet-600"></i>
                            Visualización Gráfica
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @if($entity_type === 'sales')
                                <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
                                    <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                        <i class="fas fa-chart-pie mr-1 text-violet-600"></i>Distribución de Pagos
                                    </h4>
                                    <div style="height: 180px;" wire:ignore>
                                        <canvas id="salesPaymentChart"></canvas>
                                    </div>
                                </div>
                                <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
                                    <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                        <i class="fas fa-chart-doughnut mr-1 text-violet-600"></i>Tipo de Venta
                                    </h4>
                                    <div style="height: 180px;" wire:ignore>
                                        <canvas id="salesTypeChart"></canvas>
                                    </div>
                                </div>
                                <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
                                    <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                        <i class="fas fa-chart-bar mr-1 text-violet-600"></i>Distribución de Ventas
                                    </h4>
                                    <div style="height: 180px;" wire:ignore>
                                        <canvas id="groupedChart-sales"></canvas>
                                    </div>
                                </div>
                            @endif

                            @if($entity_type === 'rooms')
                                <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
                                    <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                        <i class="fas fa-door-open mr-1 text-violet-600"></i>Estado de Habitaciones
                                    </h4>
                                    <div style="height: 180px;" wire:ignore>
                                        <canvas id="summaryPieChart"></canvas>
                                    </div>
                                </div>
                                <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
                                    <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                        <i class="fas fa-chart-bar mr-1 text-violet-600"></i>Reservas por Habitación
                                    </h4>
                                    <div style="height: 180px;" wire:ignore>
                                        <canvas id="groupedChart-rooms"></canvas>
                                    </div>
                                </div>
                            @endif

                            @if($entity_type === 'reservations')
                                <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
                                    <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                        <i class="fas fa-chart-pie mr-1 text-violet-600"></i>Estado de Pagos
                                    </h4>
                                    <div style="height: 180px;" wire:ignore>
                                        <canvas id="pieChart-reservations"></canvas>
                                    </div>
                                </div>
                                <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
                                    <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                        <i class="fas fa-chart-bar mr-1 text-violet-600"></i>Montos por Cliente
                                    </h4>
                                    <div style="height: 180px;" wire:ignore>
                                        <canvas id="groupedChart-reservations"></canvas>
                                    </div>
                                </div>
                            @endif

                            @if($entity_type === 'receptionists')
                                <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
                                    <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                        <i class="fas fa-user-tie mr-1 text-violet-600"></i>Rendimiento por Recepcionista
                                    </h4>
                                    <div style="height: 180px;" wire:ignore>
                                        <canvas id="groupedChart-receptionists"></canvas>
                                    </div>
                                </div>
                            @endif

                            @if($entity_type === 'customers')
                                <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
                                    <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                        <i class="fas fa-users mr-1 text-violet-600"></i>Inversión Total por Cliente
                                    </h4>
                                    <div style="height: 180px;" wire:ignore>
                                        <canvas id="groupedChart-customers"></canvas>
                                    </div>
                                </div>
                            @endif

                            @if($entity_type === 'products')
                                <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
                                    <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                        <i class="fas fa-box mr-1 text-violet-600"></i>Valor de Inventario por Producto
                                    </h4>
                                    <div style="height: 180px;" wire:ignore>
                                        <canvas id="groupedChart-products"></canvas>
                                    </div>
                                </div>
                            @endif

                            @if($entity_type === 'electronic_invoices')
                                <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
                                    <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2 flex items-center">
                                        <i class="fas fa-file-invoice mr-1 text-violet-600"></i>Distribución de Facturas
                                    </h4>
                                    <div style="height: 180px;" wire:ignore>
                                        <canvas id="groupedChart-electronic_invoices"></canvas>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Desglose Detallado -->
                @if($entity_type === 'sales' && !empty($reportData['data'] ?? []))
                    <div class="mb-6" wire:key="table-sales">
                        <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Ventas</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Fecha</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Recepcionista</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tipo</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Habitación</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Método de Pago</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Efectivo</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Transferencia</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData['data'] as $sale)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($sale->sale_date)->translatedFormat('d/m/Y') }}
                                                <span class="text-[10px] text-gray-400 block">{{ $sale->created_at->format('H:i') }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                                {{ $sale->user->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if($sale->room_id)
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        Habitación
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                                        Venta Normal
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                @if($sale->room_id && $sale->room)
                                                    Habitación {{ $sale->room->room_number }}
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if($sale->payment_method === 'efectivo')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        Efectivo
                                                    </span>
                                                @elseif($sale->payment_method === 'transferencia')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        Transferencia
                                                    </span>
                                                @elseif($sale->payment_method === 'ambos')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                        Ambos
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Pendiente
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                                                ${{ number_format($sale->total, 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-green-600 text-right">
                                                ${{ number_format($sale->cash_amount ?? 0, 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-blue-600 text-right">
                                                ${{ number_format($sale->transfer_amount ?? 0, 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if($sale->debt_status === 'pagado')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        Pagado
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                        Pendiente
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-center">
                                                <button type="button"
                                                        wire:click="openDetails('sale', {{ $sale->id }})"
                                                        class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                    <i class="fas fa-eye mr-1"></i> Ver Detalles
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="5" class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">Total:</td>
                                        <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">
                                            ${{ number_format(collect($reportData['data'])->sum('total'), 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-bold text-green-600 text-right">
                                            ${{ number_format(collect($reportData['data'])->sum('cash_amount'), 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-bold text-blue-600 text-right">
                                            ${{ number_format(collect($reportData['data'])->sum('transfer_amount'), 2, ',', '.') }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @elseif($entity_type === 'sales' && (empty($reportData['data'] ?? []) || count($reportData['data']) === 0))
                    <div class="mb-6 bg-white rounded-lg border-2 border-gray-300 p-8 text-center" wire:key="empty-sales">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                            <i class="fas fa-shopping-cart text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay ventas</h3>
                        <p class="text-sm text-gray-600">No se encontraron ventas en el período seleccionado.</p>
                    </div>
                @endif

                @if($entity_type === 'rooms' && !empty($reportData['detailed_data'] ?? []) && count($reportData['detailed_data']) > 0)
                    <div class="mb-6" wire:key="table-rooms">
                        <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Habitaciones</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Habitación</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Cliente Actual</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Check-in</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Check-out</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Pagado</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Pendiente</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData['detailed_data'] as $roomData)
                                        @php
                                            $currentReservation = collect($roomData['reservations_history'] ?? [])
                                                ->filter(fn($r) => $r['date'] === $endDate)
                                                ->first()['reservation'] ?? null;
                                            if (!$currentReservation && !empty($roomData['reservations_history'])) {
                                                $currentReservation = collect($roomData['reservations_history'])->last()['reservation'] ?? null;
                                            }
                                        @endphp
                                        <tr class="hover:bg-gray-50 cursor-pointer"
                                            wire:click="openDetails('room', {{ $roomData['id'] }})">
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                                Habitación {{ $roomData['room_number'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-violet-100 text-violet-800">
                                                    {{ $roomData['status_label'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                {{ $currentReservation['customer_name'] ?? 'Disponible' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $currentReservation['check_in_date'] ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $currentReservation['check_out_date'] ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                                                @if($currentReservation)
                                                    ${{ number_format($currentReservation['total_amount'], 2, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-green-600 text-right font-semibold">
                                                @if($currentReservation)
                                                    ${{ number_format($currentReservation['deposit'], 2, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-red-600 text-right font-semibold">
                                                @if($currentReservation)
                                                    ${{ number_format($currentReservation['pending_amount'], 2, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-center">
                                                <button type="button"
                                                        wire:click.stop="openDetails('room', {{ $roomData['id'] }})"
                                                        class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                    <i class="fas fa-eye mr-1"></i> Ver Detalles
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif($entity_type === 'rooms' && (empty($reportData['detailed_data'] ?? []) || count($reportData['detailed_data']) === 0))
                    <div class="mb-6 bg-white rounded-lg border-2 border-gray-300 p-8 text-center" wire:key="empty-rooms">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                            <i class="fas fa-door-open text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay habitaciones</h3>
                        <p class="text-sm text-gray-600">No se encontraron habitaciones en el período seleccionado.</p>
                    </div>
                @endif

                @if($entity_type === 'cleaning' && !empty($reportData['detailed_data'] ?? []))
                    <div class="mb-6" wire:key="table-cleaning">
                        <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Habitaciones en Limpieza</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Habitación</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Cliente Actual</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Check-in</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Check-out</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Pagado</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Pendiente</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData['detailed_data'] as $roomData)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                                Habitación {{ $roomData['room_number'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $roomData['status'] === 'limpieza' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $roomData['status_label'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                {{ $roomData['current_customer'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $roomData['check_in_date'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $roomData['check_out_date'] }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                                                @if($roomData['total_amount'] > 0)
                                                    ${{ number_format($roomData['total_amount'], 2, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-green-600 text-right font-semibold">
                                                @if($roomData['deposit'] > 0)
                                                    ${{ number_format($roomData['deposit'], 2, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-red-600 text-right font-semibold">
                                                @if($roomData['pending_amount'] > 0)
                                                    ${{ number_format($roomData['pending_amount'], 2, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-center">
                                                <button type="button"
                                                        wire:click="openDetails('room', {{ $roomData['id'] }})"
                                                        class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                    <i class="fas fa-eye mr-1"></i> Ver Detalles
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif($entity_type === 'cleaning' && empty($reportData['detailed_data'] ?? []))
                    <div class="mb-6 bg-white rounded-lg border-2 border-gray-300 p-8 text-center" wire:key="empty-cleaning">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                            <i class="fas fa-broom text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay habitaciones en limpieza</h3>
                        <p class="text-sm text-gray-600">No se encontraron habitaciones que requieran limpieza en el período seleccionado.</p>
                    </div>
                @endif

                @if($entity_type === 'reservations' && !empty($reportData['detailed_data'] ?? []))
                    <div class="mb-6" wire:key="table-reservations">
                        <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Reservaciones</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Cliente</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Teléfono</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Habitación</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Check-in</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Check-out</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Huéspedes</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Pagado</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Pendiente</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Método Pago</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData['detailed_data'] as $reservation)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $reservation['customer_name'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $reservation['customer_phone'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">Habitación {{ $reservation['room_number'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $reservation['check_in_date'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $reservation['check_out_date'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-center">{{ $reservation['guests_count'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">${{ number_format($reservation['total_amount'], 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-green-600 text-right font-semibold">${{ number_format($reservation['deposit'], 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-red-600 text-right font-semibold">${{ number_format($reservation['pending_amount'], 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ ucfirst($reservation['payment_method']) }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                @if($reservation['payment_status'] === 'paid')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Pagado</span>
                                                @elseif($reservation['payment_status'] === 'partially_paid')
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Parcial</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Pendiente</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-center">
                                                <button type="button"
                                                        wire:click="openDetails('reservation', {{ $reservation['id'] }})"
                                                        class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                    <i class="fas fa-eye mr-1"></i> Ver Detalles
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif($entity_type === 'reservations' && empty($reportData['detailed_data'] ?? []))
                    <div class="mb-6 bg-white rounded-lg border-2 border-gray-300 p-8 text-center" wire:key="empty-reservations">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                            <i class="fas fa-calendar-check text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay reservaciones</h3>
                        <p class="text-sm text-gray-600">No se encontraron reservaciones en el período seleccionado.</p>
                    </div>
                @endif

                @if($entity_type === 'customers' && !empty($reportData['data'] ?? []))
                    <div class="mb-6" wire:key="table-customers">
                        <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Clientes</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Cliente</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Teléfono</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Facturación Electrónica</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Reservaciones</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Fecha Registro</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData['data'] as $customer)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $customer->name }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $customer->email ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $customer->phone ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $customer->is_active ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                @if($customer->requires_electronic_invoice)
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-violet-100 text-violet-800">Sí</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">No</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">{{ $customer->reservations_count ?? 0 }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $customer->created_at->translatedFormat('d/m/Y') }}</td>
                                            <td class="px-4 py-3 text-sm text-center">
                                                <button type="button"
                                                        wire:click="openDetails('customer', {{ $customer->id }})"
                                                        class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                    <i class="fas fa-eye mr-1"></i> Ver Detalles
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($entity_type === 'products' && !empty($reportData['data'] ?? []))
                    <div class="mb-6" wire:key="table-products">
                        <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Productos</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Producto</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">SKU</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Categoría</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Precio</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Valor Total</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData['data'] as $product)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $product->name }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $product->sku ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $product->category->name ?? 'Sin categoría' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                                                <span class="{{ $product->hasLowStock() ? 'text-orange-600' : ($product->quantity == 0 ? 'text-red-600' : 'text-gray-900') }}">
                                                    {{ $product->quantity }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right">${{ number_format($product->price, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">${{ number_format($product->quantity * $product->price, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($product->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-center">
                                                <button type="button"
                                                        wire:click="openDetails('product', {{ $product->id }})"
                                                        class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                    <i class="fas fa-eye mr-1"></i> Ver Detalles
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($entity_type === 'receptionists' && !empty($reportData['detailed_data'] ?? []))
                    <div class="mb-6" wire:key="table-receptionists">
                        <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Recepcionistas</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Recepcionista</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total Ventas</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Cantidad Ventas</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Efectivo</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Transferencia</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData['detailed_data'] as $receptionist)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $receptionist['name'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">${{ number_format($receptionist['total_sales'], 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ $receptionist['sales_count'] }}</td>
                                            <td class="px-4 py-3 text-sm text-green-600 text-right">${{ number_format($receptionist['cash'], 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-blue-600 text-right">${{ number_format($receptionist['transfer'], 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-center">
                                                <button type="button"
                                                        wire:click="openDetails('receptionist', {{ $receptionist['id'] }})"
                                                        class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                    <i class="fas fa-eye mr-1"></i> Ver Detalles
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif($entity_type === 'receptionists' && empty($reportData['detailed_data'] ?? []))
                    <div class="mb-6 bg-white rounded-lg border-2 border-gray-300 p-8 text-center" wire:key="empty-receptionists">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                            <i class="fas fa-user-tie text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay datos de recepcionistas</h3>
                        <p class="text-sm text-gray-600">No se encontraron ventas realizadas por recepcionistas en el período seleccionado.</p>
                    </div>
                @endif

                @if($entity_type === 'electronic_invoices' && !empty($reportData['data'] ?? []))
                    <div class="mb-6" wire:key="table-invoices">
                        <h3 class="text-md font-semibold text-gray-900 mb-3">Desglose Detallado de Facturas Electrónicas</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Cliente</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tipo Documento</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Impuestos</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">CUFE</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Fecha</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData['data'] as $invoice)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $invoice->customer->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $invoice->documentType->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $invoice->status === 'validated' ? 'bg-green-100 text-green-800' : ($invoice->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ ucfirst($invoice->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">${{ number_format($invoice->total, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-right">${{ number_format($invoice->tax_amount, 2, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500 font-mono text-xs">{{ $invoice->cufe ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $invoice->created_at->translatedFormat('d/m/Y') }}</td>
                                            <td class="px-4 py-3 text-sm text-center">
                                                @if($invoice->sale_id)
                                                    <button type="button"
                                                            wire:click="openDetails('sale', {{ $invoice->sale_id }})"
                                                            class="text-violet-600 hover:text-violet-800 font-semibold text-xs">
                                                        <i class="fas fa-eye mr-1"></i> Ver Venta
                                                    </button>
                                                @else
                                                    <span class="text-gray-400 text-xs">Sin Venta Asociada</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if(!empty($reportData['grouped']) && !empty($groupBy))
                    <div class="mb-6" wire:key="table-grouped">
                        <h3 class="text-md font-semibold text-gray-900 mb-3">Resumen Agrupado por {{ $groupByLabel ?? $groupBy }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nombre</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($reportData['grouped'] as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $item['name'] ?? ($item['date'] ?? 'N/A') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                                                @if(isset($item['total']))
                                                    ${{ number_format($item['total'], 2, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ $item['count'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6" wire:key="no-report-data">
                <p class="text-gray-500 text-center py-8">Seleccione un tipo de reporte y las fechas para ver los datos.</p>
            </div>
        @endif
    </div>

    <!-- Datos para JavaScript -->
    @php
        $reportDataHash = md5(json_encode([
            'entity_type' => $entity_type,
            'groupBy' => $groupBy ?? '',
            'reportData' => $reportData ?? [],
        ]));
    @endphp
    <div id="reports-data-container"
         style="display: none;"
         data-report-data='@json($reportData ?? [])'
         data-entity-type="{{ $entity_type }}"
         data-group-by="{{ $groupBy ?? '' }}"
         data-hash="{{ $reportDataHash }}">
    </div>
</div>


