<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Models\Room;
use App\Models\User;
use App\Models\Customer;
use App\Models\Category;
use App\Models\DianDocumentType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

final class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

    /**
     * Display a listing of available reports.
     */
    public function index(): View
    {
        return view('reports.index');
    }

    /**
     * Get available reports based on user permissions.
     */
    public function getAvailableReports(): array
    {
        $reports = [];
        
        if (auth()->user()->can('view_sales')) {
            $reports['sales'] = [
                'label' => 'Ventas',
                'icon' => 'fa-shopping-cart',
                'description' => 'Reportes de ventas y transacciones',
                'permission' => 'view_sales'
            ];
        }
        
        if (auth()->user()->can('view_reservations')) {
            $reports['rooms'] = [
                'label' => 'Habitaciones',
                'icon' => 'fa-door-open',
                'description' => 'Estado y ocupación de habitaciones',
                'permission' => 'view_reservations'
            ];
            
            $reports['reservations'] = [
                'label' => 'Reservas',
                'icon' => 'fa-calendar-check',
                'description' => 'Reportes de reservaciones y pagos',
                'permission' => 'view_reservations'
            ];
            
            $reports['cleaning'] = [
                'label' => 'Limpieza',
                'icon' => 'fa-broom',
                'description' => 'Habitaciones pendientes de limpieza',
                'permission' => 'view_reservations'
            ];
        }   
        
        if (auth()->user()->can('view_sales')) {
            $reports['receptionists'] = [
                'label' => 'Recepcionistas',
                'icon' => 'fa-user-tie',
                'description' => 'Rendimiento de recepcionistas',
                'permission' => 'view_sales'
            ];
        }
        
        if (auth()->user()->can('view_customers')) {
            $reports['customers'] = [
                'label' => 'Clientes',
                'icon' => 'fa-users',
                'description' => 'Análisis de clientes y reservaciones',
                'permission' => 'view_customers'
            ];
        }
        
        if (auth()->user()->can('view_categories')) {
            $reports['products'] = [
                'label' => 'Productos',
                'icon' => 'fa-box',
                'description' => 'Inventario y consumo de productos',
                'permission' => 'view_categories'
            ];
        }
        
        if (auth()->user()->can('generate_invoices')) {
            $reports['electronic_invoices'] = [
                'label' => 'Facturas Electrónicas',
                'icon' => 'fa-file-invoice',
                'description' => 'Reportes de facturación electrónica DIAN',
                'permission' => 'generate_invoices'
            ];
        }
        
        return $reports;
    }

    /**
     * Get filter data for reports.
     */
    public function getFilterData(): JsonResponse
    {
        return response()->json([
            'rooms' => Room::orderBy('room_number')->get(['id', 'room_number', 'status']),
            'receptionists' => User::role(['Recepcionista Día', 'Recepcionista Noche'])
                ->orderBy('name')
                ->get(['id', 'name']),
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'documentTypes' => DianDocumentType::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Get filter label translation.
     */
    public function getFilterLabel(string $key): string
    {
        return match($key) {
            'receptionist_id' => 'Recepcionista',
            'room_id' => 'Habitación',
            'customer_id' => 'Cliente',
            'group' => 'Grupo',
            'status' => 'Estado',
            'payment_method' => 'Método de Pago',
            'payment_status' => 'Estado de Pago',
            'debt_status' => 'Estado de Deuda',
            'is_active' => 'Estado Activo',
            'low_stock' => 'Stock Bajo',
            'requires_electronic_invoice' => 'Facturación Electrónica',
            'category_id' => 'Categoría',
            'document_type_id' => 'Tipo de Documento',
            default => $key,
        };
    }

    /**
     * Get formatted filter value.
     */
    public function getFilterValue(string $key, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        
        return match($key) {
            'receptionist_id' => User::find($value)?->name ?? '',
            'room_id' => $value === 'none' ? 'Venta Normal (Sin Hab.)' : 'Habitación ' . (Room::find($value)?->room_number ?? ''),
            'customer_id' => Customer::find($value)?->name ?? '',
            'category_id' => Category::find($value)?->name ?? '',
            'document_type_id' => DianDocumentType::find($value)?->name ?? '',
            'group' => $value === 'aseo' ? 'Insumos de Aseo' : 'Productos de Venta',
            'status' => match($value) {
                'libre' => 'Libre',
                'ocupada' => 'Ocupada',
                'reservada' => 'Reservada',
                'limpieza' => 'En Limpieza',
                'sucia' => 'Sucia',
                'mantenimiento' => 'Mantenimiento',
                'active' => 'Activo',
                'inactive' => 'Inactivo',
                'pending' => 'Pendiente',
                'validated' => 'Validada',
                'rejected' => 'Rechazada',
                default => $value,
            },
            'payment_method' => match($value) {
                'efectivo' => 'Efectivo',
                'transferencia' => 'Transferencia',
                'ambos' => 'Ambos',
                'pendiente' => 'Pendiente',
                default => $value,
            },
            'payment_status' => match($value) {
                'paid' => 'Pagado',
                'partially_paid' => 'Parcialmente Pagado',
                'unpaid' => 'No Pagado',
                default => $value,
            },
            'debt_status' => $value === 'pagado' ? 'Pagado' : 'Pendiente',
            'is_active' => $value === 'true' || $value === true ? 'Activos' : 'Inactivos',
            'low_stock' => $value === 'true' || $value === true ? 'Solo Bajo Stock' : '',
            'requires_electronic_invoice' => $value === 'true' || $value === true ? 'Con Facturación' : 'Sin Facturación',
            default => (string)$value,
        };
    }

    /**
     * Get preset label translation.
     */
    public function getPresetLabel(string $preset): string
    {
        return match($preset) {
            'today' => 'Hoy (' . now()->translatedFormat('d/m/Y') . ')',
            'yesterday' => 'Ayer (' . now()->subDay()->translatedFormat('d/m/Y') . ')',
            'this_week' => 'Esta Semana (' . now()->startOfWeek()->translatedFormat('d/m') . ' - ' . now()->endOfWeek()->translatedFormat('d/m/Y') . ')',
            'this_month' => 'Este Mes (' . now()->translatedFormat('F Y') . ')',
            'last_month' => 'Mes Anterior (' . now()->subMonth()->translatedFormat('F Y') . ')',
            'this_year' => 'Este Año (' . now()->year . ')',
            default => $preset,
        };
    }

    /**
     * Generate PDF report.
     */
    public function generatePDF(Request $request)
    {
        $entityType = $request->input('entity_type', 'sales');
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $groupBy = $request->input('group_by');
        $filters = $request->input('filters', []);

        $reportData = $this->reportService->generateReport(
            $entityType,
            Carbon::parse($startDate),
            Carbon::parse($endDate),
            $groupBy,
            $filters ?: []
        );

        $entityTypeLabel = $this->reportService->translateEntityType($entityType);
        $groupByLabel = $groupBy ? $this->reportService->translateGroupingOption($groupBy) : null;

        $fileName = 'reporte-' . $entityType . '-' . $startDate . '-' . $endDate . '.pdf';

        $pdf = Pdf::loadView('reports.pdf', [
            'reportData' => $reportData,
            'entityType' => $entityType,
            'entityTypeLabel' => $entityTypeLabel,
            'groupBy' => $groupBy,
            'groupByLabel' => $groupByLabel,
        ]);

        return $pdf->download($fileName);
    }

    /**
     * Generate PDF for a single item.
     */
    public function generateSinglePDF(string $type, int $id)
    {
        $reportsManager = new \App\Livewire\ReportsManager();
        $reportsManager->selectedDetailType = $type;
        $reportsManager->selectedDetailId = $id;
        $data = $reportsManager->getDetailData();

        if (!$data) {
            abort(404, 'No se encontraron datos detallados para este registro.');
        }

        $title = str_replace(' ', '_', $data['title']);
        $pdf = Pdf::loadView('reports.single-pdf', ['data' => $data, 'type' => $type]);
        
        // Configurar papel horizontal para reportes muy detallados si es necesario
        if (in_array($type, ['sale', 'reservation'])) {
            $pdf->setPaper('a4', 'portrait');
        }

        return $pdf->download("{$title}.pdf");
    }
}
