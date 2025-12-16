<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas</title>
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
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">MovilTech</div>
        <div class="report-title">Reporte de Ventas</div>
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
            @if(isset($filters['customer_id']) && $filters['customer_id'])
                <strong>Cliente específico</strong> |
            @endif
            @if(isset($filters['product_id']) && $filters['product_id'])
                <strong>Producto específico</strong> |
            @endif
            @if(!isset($filters['date_from']) && !isset($filters['date_to']) && !isset($filters['customer_id']) && !isset($filters['product_id']))
                <strong>Sin filtros aplicados - Mostrando todas las ventas</strong>
            @endif
        </p>
    </div>

    <!-- Resumen -->
    <div class="summary">
        <div class="summary-item">
            <div class="summary-value">${{ number_format($totalSales, 2) }}</div>
            <div class="summary-label">Total de Ventas</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $totalCount }}</div>
            <div class="summary-label">Número de Ventas</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">${{ $totalCount > 0 ? number_format($totalSales / $totalCount, 2) : '0.00' }}</div>
            <div class="summary-label">Venta Promedio</div>
        </div>
    </div>

    <!-- Tabla de ventas -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Productos</th>
                <th>Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
            <tr>
                <td class="text-center">#{{ $sale->id }}</td>
                <td>{{ $sale->customer->name }}</td>
                <td class="text-center">{{ $sale->sale_date->format('d/m/Y') }}</td>
                <td>
                    @foreach($sale->saleItems as $item)
                        {{ $item->product->name }} ({{ $item->quantity }}x)@if(!$loop->last), @endif
                    @endforeach
                </td>
                <td class="text-right font-bold">${{ number_format($sale->total, 2) }}</td>
                <td class="text-center">{{ ucfirst($sale->status) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="color: #666;">
                    No se encontraron ventas con los filtros aplicados
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>MovilTech - Sistema de Gestión | Reporte generado automáticamente</p>
    </div>
</body>
</html>
