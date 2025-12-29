<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Turno #{{ $handover->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .muted { color: #6b7280; }
        .title { font-size: 18px; font-weight: 800; margin-bottom: 6px; }
        .subtitle { font-size: 12px; margin-bottom: 14px; }
        .card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px; margin-bottom: 12px; }
        .grid { width: 100%; }
        .grid td { vertical-align: top; padding: 4px 6px; }
        .k { font-size: 10px; text-transform: uppercase; letter-spacing: .08em; color: #6b7280; font-weight: 700; }
        .v { font-size: 13px; font-weight: 800; }
        .v-red { color: #b91c1c; }
        .v-green { color: #047857; }
        .v-blue { color: #1d4ed8; }
        .v-indigo { color: #4338ca; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th, table.data td { border-bottom: 1px solid #e5e7eb; padding: 8px 6px; }
        table.data th { background: #f9fafb; text-transform: uppercase; font-size: 10px; letter-spacing: .08em; text-align: left; }
        .right { text-align: right; }
        .center { text-align: center; }
        .pill { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 10px; font-weight: 800; text-transform: uppercase; background: #dbeafe; color: #1d4ed8; }
        .footer { margin-top: 16px; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="title">Detalle de Turno #{{ $handover->id }}</div>
    <div class="subtitle muted">
        Fecha: <strong>{{ $handover->shift_date->format('d/m/Y') }}</strong> |
        Tipo: <strong style="text-transform: uppercase">{{ $handover->shift_type->value }}</strong> |
        Estado: <span class="pill">{{ $handover->status->value }}</span>
    </div>

    <div class="card">
        <table class="grid">
            <tr>
                <td style="width: 33%">
                    <div class="k">Base Inicial</div>
                    <div class="v">${{ number_format($handover->base_inicial ?? 0, 2, ',', '.') }}</div>
                </td>
                <td style="width: 33%">
                    <div class="k">Ventas Efectivo</div>
                    <div class="v v-green">${{ number_format($handover->total_entradas_efectivo ?? 0, 2, ',', '.') }}</div>
                </td>
                <td style="width: 33%">
                    <div class="k">Ventas Transferencia</div>
                    <div class="v v-blue">${{ number_format($handover->total_entradas_transferencia ?? 0, 2, ',', '.') }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="k">Total Salidas</div>
                    <div class="v v-red">${{ number_format($handover->total_salidas ?? 0, 2, ',', '.') }}</div>
                </td>
                <td>
                    <div class="k">Base Esperada</div>
                    <div class="v v-indigo">${{ number_format($handover->base_esperada ?? 0, 2, ',', '.') }}</div>
                </td>
                <td>
                    <div class="k">Base Recibida</div>
                    @php $diff = (float) ($handover->diferencia ?? 0); @endphp
                    <div class="v {{ abs($diff) > ($tolerance ?? 0) ? 'v-red' : 'v-green' }}">
                        ${{ number_format($handover->base_recibida ?? 0, 2, ',', '.') }}
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="k">Entregado por</div>
                    <div class="v">{{ $handover->entregadoPor->name ?? 'N/A' }}</div>
                </td>
                <td>
                    <div class="k">Recibido por</div>
                    <div class="v">{{ $handover->recibidoPor->name ?? 'Pendiente' }}</div>
                </td>
                <td>
                    <div class="k">Diferencia</div>
                    <div class="v {{ abs($diff) > ($tolerance ?? 0) ? 'v-red' : 'v-indigo' }}">
                        ${{ number_format($diff, 2, ',', '.') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="card">
        <div class="k">Observaciones (Entrega)</div>
        <div style="margin-top: 6px">{{ $handover->observaciones_entrega ?: 'Sin observaciones' }}</div>
        <div style="height: 10px"></div>
        <div class="k">Observaciones (Recepción)</div>
        <div style="margin-top: 6px">{{ $handover->observaciones_recepcion ?: 'Sin observaciones' }}</div>
    </div>

    @if(($handover->cashOuts ?? collect())->count() > 0)
        <div class="card">
            <div class="k">Retiros de Caja</div>
            <table class="data">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Concepto</th>
                        <th class="right">Monto</th>
                        <th class="center">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($handover->cashOuts as $co)
                        <tr>
                            <td>#{{ $co->id }}</td>
                            <td>{{ $co->concept }}</td>
                            <td class="right">${{ number_format($co->amount, 2, ',', '.') }}</td>
                            <td class="center">{{ optional($co->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(($handover->productOuts ?? collect())->count() > 0)
        <div class="card">
            <div class="k">Salidas de Productos (Mermas / Consumo)</div>
            <table class="data">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Motivo</th>
                        <th class="center">Cantidad</th>
                        <th class="center">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($handover->productOuts as $po)
                        <tr>
                            <td>{{ $po->product->name }}</td>
                            <td>{{ $po->reason->label() }}</td>
                            <td class="center">{{ number_format($po->quantity, 0, ',', '.') }}</td>
                            <td class="center">{{ optional($po->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        Generado: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>


