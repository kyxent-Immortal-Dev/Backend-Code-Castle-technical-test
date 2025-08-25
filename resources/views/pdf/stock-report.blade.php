<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Stock - Sistema de Inventario</title>
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
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            color: #666;
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
            color: #2563eb;
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
            background-color: #2563eb;
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
        .stock-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }
        .stock-ok {
            background-color: #dcfce7;
            color: #166534;
        }
        .stock-low {
            background-color: #fef3c7;
            color: #92400e;
        }
        .stock-out {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-inactive {
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
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 REPORTE DE STOCK ACTUAL</h1>
        <p><strong>Sistema de Inventario</strong></p>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <h3>📈 Resumen del Inventario</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ $totalProducts }}</div>
                <div class="summary-label">Total Productos</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $activeProducts }}</div>
                <div class="summary-label">Productos Activos</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $lowStockProducts }}</div>
                <div class="summary-label">Stock Bajo</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $outOfStockProducts }}</div>
                <div class="summary-label">Sin Stock</div>
            </div>
        </div>
    </div>

    <div class="table-container">
        <h3>📋 Listado de Productos</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>Descripción</th>
                    <th>Precio Unit.</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Valor Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td>{{ Str::limit($product->description, 50) }}</td>
                    <td>${{ number_format($product->unit_price, 2) }}</td>
                    <td>
                        <span class="stock-status 
                            @if($product->stock === 0) stock-out
                            @elseif($product->stock <= 10) stock-low
                            @else stock-ok @endif">
                            {{ $product->stock }} unidades
                        </span>
                    </td>
                    <td>
                        <span class="stock-status 
                            @if($product->is_active) status-active @else status-inactive @endif">
                            {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td><strong>${{ number_format($product->stock * $product->unit_price, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p><strong>Reporte generado automáticamente por el Sistema de Inventario</strong></p>
        <p>Este documento contiene información confidencial del inventario</p>
        <p>Página 1 de 1</p>
    </div>
</body>
</html> 