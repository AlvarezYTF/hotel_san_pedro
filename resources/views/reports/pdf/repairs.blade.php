<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Reparaciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .report-title {
            font-size: 18px;
            margin-top: 10px;
            color: #666;
        }
        .filters {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .filters h3 {
            margin-top: 0;
            color: #333;
        }
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
        }
        .summary-label {
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', monospace; }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-in_progress { background-color: #d4edda; color: #155724; }
        .status-completed { background-color: #d1ecf1; color: #0c5460; }
        .status-delivered { background-color: #e2e3e5; color: #383d41; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">MovilTech</div>
        <div class="report-title">Reporte de Reparaciones</div>
        <div style="margin-top: 10px; color: #666;">
            Generado el {{ date('d/m/Y H:i') }}
        </div>
    </div>

    <!-- Filtros aplicados -->
    <div class="filters">
        <h3>Filtros Aplicados:</h3>
        <p>
            @if(isset($filters['date_from']) && $filters['date_from'])
                <strong>Fecha desde:</strong> {{ \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') }} |
            @endif
            @if(isset($filters['date_to']) && $filters['date_to'])
                <strong>Fecha hasta:</strong> {{ \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') }} |
            @endif
            @if(isset($filters['repair_status']) && $filters['repair_status'])
                <strong>Estado:</strong> 
                @switch($filters['repair_status'])
                    @case('pending') Pendiente @break
                    @case('in_progress') En Progreso @break
                    @case('completed') Completado @break
                    @case('delivered') Entregado @break
                @endswitch |
            @endif
            @if(isset($filters['customer_id']) && $filters['customer_id'])
                <strong>Cliente específico</strong> |
            @endif
            @if(!isset($filters['date_from']) && !isset($filters['date_to']) && !isset($filters['repair_status']) && !isset($filters['customer_id']))
                <strong>Sin filtros aplicados - Mostrando todas las reparaciones</strong>
            @endif
        </p>
    </div>

    <!-- Resumen -->
    <div class="summary">
        <div class="summary-item">
            <div class="summary-value">${{ number_format($totalRevenue, 2) }}</div>
            <div class="summary-label">Ingresos Totales</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $totalCount }}</div>
            <div class="summary-label">Total Reparaciones</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">${{ $totalCount > 0 ? number_format($totalRevenue / $totalCount, 2) : '0.00' }}</div>
            <div class="summary-label">Precio Promedio</div>
        </div>
    </div>

    <!-- Tabla de reparaciones -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th>IMEI</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Costo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($repairs as $repair)
            <tr>
                <td class="text-center">#{{ $repair->id }}</td>
                <td>{{ $repair->customer->name }}</td>
                <td>{{ $repair->phone_model }}</td>
                <td class="font-mono">{{ $repair->imei }}</td>
                <td class="text-center">{{ $repair->repair_date->format('d/m/Y') }}</td>
                <td class="text-center status-{{ $repair->repair_status }}">
                    @switch($repair->repair_status)
                        @case('pending') Pendiente @break
                        @case('in_progress') En Progreso @break
                        @case('completed') Completado @break
                        @case('delivered') Entregado @break
                    @endswitch
                </td>
                <td class="text-right font-bold">${{ number_format($repair->repair_cost, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="color: #666;">
                    No se encontraron reparaciones con los filtros aplicados
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Detalle de problemas más frecuentes (si hay datos) -->
    @if($repairs->count() > 0)
    <div style="margin-top: 30px;">
        <h3 style="color: #333; border-bottom: 1px solid #dee2e6; padding-bottom: 5px;">
            Distribución por Estado
        </h3>
        <table style="width: 50%;">
            <tr>
                <td><strong>Pendientes:</strong></td>
                <td class="text-right">{{ $repairs->where('repair_status', 'pending')->count() }}</td>
            </tr>
            <tr>
                <td><strong>En Progreso:</strong></td>
                <td class="text-right">{{ $repairs->where('repair_status', 'in_progress')->count() }}</td>
            </tr>
            <tr>
                <td><strong>Completadas:</strong></td>
                <td class="text-right">{{ $repairs->where('repair_status', 'completed')->count() }}</td>
            </tr>
            <tr>
                <td><strong>Entregadas:</strong></td>
                <td class="text-right">{{ $repairs->where('repair_status', 'delivered')->count() }}</td>
            </tr>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>MovilTech - Sistema de Gestión | Reporte generado automáticamente</p>
    </div>
</body>
</html>
