<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte - {{ $entityTypeLabel }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #6366f1;
        }
        .header h1 {
            font-size: 24px;
            color: #6366f1;
            margin-bottom: 10px;
        }
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        .info-section {
            margin-bottom: 25px;
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
        }
        .info-section h2 {
            font-size: 16px;
            color: #333;
            margin-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 5px;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .info-label {
            display: table-cell;
            width: 150px;
            font-weight: bold;
            color: #555;
        }
        .info-value {
            display: table-cell;
            color: #333;
        }
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .summary-card {
            display: table-cell;
            width: 24%;
            background-color: #f3f4f6;
            padding: 15px;
            margin-right: 1%;
            border-radius: 5px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .summary-card:last-child {
            margin-right: 0;
        }
        .summary-card h3 {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .summary-card .value {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        thead {
            background-color: #f9fafb;
        }
        th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            font-size: 11px;
            text-transform: uppercase;
        }
        th.text-right {
            text-align: right;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        td.text-right {
            text-align: right;
        }
        tbody tr:hover {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de {{ $entityTypeLabel }}</h1>
        <div class="subtitle">
            Período: {{ \Illuminate\Support\Carbon::parse($reportData['start_date'])->translatedFormat('d/m/Y') }} al 
            {{ \Illuminate\Support\Carbon::parse($reportData['end_date'])->translatedFormat('d/m/Y') }}
        </div>
        <div class="subtitle" style="margin-top: 5px;">
            Generado el: {{ now()->translatedFormat('d/m/Y H:i:s') }}
        </div>
    </div>

    @if(isset($reportData['summary']))
        <div class="summary-cards">
            @foreach($reportData['summary'] as $key => $value)
                @if(is_numeric($value))
                    <div class="summary-card">
                        <h3>{{ app(\App\Services\ReportService::class)->translateSummaryKey($key) }}</h3>
                        <div class="value">
                            @if(str_contains($key, 'revenue') || str_contains($key, 'amount') || str_contains($key, 'sales') || str_contains($key, 'cash') || str_contains($key, 'transfer') || str_contains($key, 'debt') || str_contains($key, 'deposit') || str_contains($key, 'pending'))
                                ${{ number_format($value, 2, ',', '.') }}
                            @else
                                {{ number_format($value, 0, ',', '.') }}
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    @if(!empty($reportData['grouped']))
        <div class="info-section">
            <h2>Datos Agrupados</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Cantidad</th>
                        @if($entityType === 'sales' && isset($groupBy) && $groupBy === 'receptionist')
                            <th class="text-right">Efectivo</th>
                            <th class="text-right">Transferencia</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['grouped'] as $item)
                        <tr>
                            <td>{{ $item['name'] ?? $item['id'] ?? 'N/D' }}</td>
                            <td class="text-right">
                                @if(isset($item['total']) || isset($item['total_amount']) || isset($item['total_sales']))
                                    ${{ number_format($item['total'] ?? $item['total_amount'] ?? $item['total_sales'] ?? 0, 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right">{{ $item['count'] ?? $item['sales_count'] ?? '-' }}</td>
                            @if($entityType === 'sales' && isset($groupBy) && $groupBy === 'receptionist')
                                <td class="text-right">${{ number_format($item['cash'] ?? 0, 2, ',', '.') }}</td>
                                <td class="text-right">${{ number_format($item['transfer'] ?? 0, 2, ',', '.') }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($entityType === 'receptionists' && !empty($reportData['grouped']))
        <div class="info-section">
            <h2>Detalle por Recepcionista</h2>
            <table>
                <thead>
                    <tr>
                        <th>Recepcionista</th>
                        <th class="text-right">Total Ventas</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Efectivo</th>
                        <th class="text-right">Transferencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['grouped'] as $receptionist)
                        <tr>
                            <td>{{ $receptionist['name'] }}</td>
                            <td class="text-right">${{ number_format($receptionist['total_sales'] ?? 0, 2, ',', '.') }}</td>
                            <td class="text-right">{{ $receptionist['sales_count'] ?? 0 }}</td>
                            <td class="text-right">${{ number_format($receptionist['cash'] ?? 0, 2, ',', '.') }}</td>
                            <td class="text-right">${{ number_format($receptionist['transfer'] ?? 0, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        <p>Hotel San Pedro - Sistema de Gestión</p>
        <p>Página 1</p>
    </div>
</body>
</html>

