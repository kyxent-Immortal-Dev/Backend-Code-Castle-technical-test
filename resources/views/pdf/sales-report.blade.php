<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas - Sistema de Inventario</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #10b981;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #10b981;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .date-range {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            text-align: center;
        }
        .date-range h3 {
            margin: 0 0 10px 0;
            color: #059669;
            font-size: 16px;
        }
        .date-range p {
            margin: 5px 0;
            font-size: 14px;
            color: #047857;
        }
        .summary {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .summary h3 {
            margin: 0 0 15px 0;
            color: #1e40af;
            font-size: 16px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        .summary-item {
            text-align: center;
            padding: 10px;
            background-color: white;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #10b981;
        }
        .summary-label {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .table-container {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #10b981;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }
        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .page-break {
            page-break-before: always;
        }
        .no-sales {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 REPORTE DE VENTAS POR FECHAS</h1>
        <p><strong>Sistema de Inventario</strong></p>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="date-range">
        <h3>📅 Período del Reporte</h3>
        <p><strong>Desde:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</p>
        <p><strong>Hasta:</strong> {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    <div class="summary">
        <h3>📈 Resumen del Período</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ $totalSales }}</div>
                <div class="summary-label">Total Ventas</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $activeSales }}</div>
                <div class="summary-label">Ventas Activas</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($totalRevenue, 2) }}</div>
                <div class="summary-label">Ingresos Totales</div>
            </div>
        </div>
    </div>

    <div class="table-container">
        <h3>📋 Listado de Ventas</h3>
        
        @if($sales && $sales->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Venta ID</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Fecha</th>
                    <th>Productos</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $index => $sale)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>#{{ $sale->id }}</strong></td>
                    <td>
                        <div>
                            <strong>{{ $sale->client->name ?? 'N/A' }}</strong><br>
                            <small>{{ $sale->client->email ?? 'N/A' }}</small>
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong>{{ $sale->user->name ?? 'N/A' }}</strong><br>
                            <small>{{ ucfirst($sale->user->role ?? 'N/A') }}</small>
                        </div>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
                    <td>{{ $sale->saleDetails ? $sale->saleDetails->count() : 0 }} producto(s)</td>
                    <td><strong>${{ number_format($sale->total_amount ?? 0, 2) }}</strong></td>
                    <td>
                        <span class="status-badge 
                            @if($sale->status === 'active') status-active @else status-cancelled @endif">
                            {{ $sale->status === 'active' ? 'Activa' : 'Cancelada' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-sales">
            <h4>No se encontraron ventas en el período seleccionado</h4>
            <p>No hay registros de ventas entre las fechas especificadas.</p>
        </div>
        @endif
    </div>

    @if($sales && $sales->count() > 0)
    <div class="table-container">
        <h3>🛍️ Detalle de Productos Vendidos</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Venta ID</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $detailIndex = 1; @endphp
                @foreach($sales as $sale)
                                    @if($sale->saleDetails)
                    @foreach($sale->saleDetails as $detail)
                    <tr>
                        <td>{{ $detailIndex++ }}</td>
                        <td><strong>#{{ $sale->id }}</strong></td>
                        <td><strong>{{ $detail->product->name ?? 'N/A' }}</strong></td>
                        <td>{{ $detail->quantity ?? 0 }} unidad(es)</td>
                        <td>${{ number_format($detail->sale_price ?? 0, 2) }}</td>
                        <td><strong>${{ number_format($detail->subtotal ?? 0, 2) }}</strong></td>
                    </tr>
                    @endforeach
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p><strong>Reporte generado automáticamente por el Sistema de Inventario</strong></p>
        <p>Este documento contiene información confidencial de las ventas del período especificado</p>
        <p>Página 1 de 1</p>
    </div>
</body>
</html> 