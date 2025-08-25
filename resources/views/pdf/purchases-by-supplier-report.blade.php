<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Compras por Proveedor - Sistema de Inventario</title>
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
            border-bottom: 2px solid #8b5cf6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #8b5cf6;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .supplier-info {
            background-color: #faf5ff;
            border: 1px solid #e9d5ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .supplier-info h3 {
            margin: 0 0 15px 0;
            color: #7c3aed;
            font-size: 16px;
        }
        .supplier-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .supplier-detail {
            padding: 10px;
            background-color: white;
            border-radius: 6px;
            border: 1px solid #e9d5ff;
        }
        .supplier-detail-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        .supplier-detail-value {
            font-size: 14px;
            font-weight: bold;
            color: #7c3aed;
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
            color: #8b5cf6;
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
            background-color: #8b5cf6;
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
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-completed {
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
        .no-purchases {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 REPORTE DE COMPRAS POR PROVEEDOR</h1>
        <p><strong>Sistema de Inventario</strong></p>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="supplier-info">
        <h3>🏢 Información del Proveedor</h3>
        <div class="supplier-details">
            <div class="supplier-detail">
                <div class="supplier-detail-label">Nombre</div>
                <div class="supplier-detail-value">{{ $supplier->name }}</div>
            </div>
            <div class="supplier-detail">
                <div class="supplier-detail-label">Email</div>
                <div class="supplier-detail-value">{{ $supplier->email }}</div>
            </div>
            <div class="supplier-detail">
                <div class="supplier-detail-label">Teléfono</div>
                <div class="supplier-detail-value">{{ $supplier->phone ?? 'N/A' }}</div>
            </div>
            <div class="supplier-detail">
                <div class="supplier-detail-label">Estado</div>
                <div class="supplier-detail-value">{{ $supplier->is_active ? 'Activo' : 'Inactivo' }}</div>
            </div>
        </div>
    </div>

    <div class="summary">
        <h3>📈 Resumen de Compras</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ $totalPurchases }}</div>
                <div class="summary-label">Total Compras</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $pendingPurchases }}</div>
                <div class="summary-label">Pendientes</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $completedPurchases }}</div>
                <div class="summary-label">Completadas</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">${{ number_format($totalAmount, 2) }}</div>
                <div class="summary-label">Monto Total</div>
            </div>
        </div>
    </div>

    <div class="table-container">
        <h3>📋 Listado de Compras</h3>
        
        @if($purchases && $purchases->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Compra ID</th>
                    <th>Usuario</th>
                    <th>Fecha</th>
                    <th>Productos</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchases as $index => $purchase)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>#{{ $purchase->id }}</strong></td>
                    <td>
                        <div>
                            <strong>{{ $purchase->user->name ?? 'N/A' }}</strong><br>
                            <small>{{ ucfirst($purchase->user->role ?? 'N/A') }}</small>
                        </div>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</td>
                    <td>{{ $purchase->details ? $purchase->details->count() : 0 }} producto(s)</td>
                    <td><strong>${{ number_format($purchase->total_amount ?? 0, 2) }}</strong></td>
                    <td>
                        <span class="status-badge 
                            @if($purchase->status === 'pending') status-pending
                            @elseif($purchase->status === 'completed') status-completed
                            @else status-cancelled @endif">
                            @if($purchase->status === 'pending')
                                Pendiente
                            @elseif($purchase->status === 'completed')
                                Completada
                            @else
                                Cancelada
                            @endif
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-purchases">
            <h4>No se encontraron compras para este proveedor</h4>
            <p>Este proveedor no tiene registros de compras en el sistema.</p>
        </div>
        @endif
    </div>

    @if($purchases && $purchases->count() > 0)
    <div class="table-container">
        <h3>🛍️ Detalle de Productos Comprados</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Compra ID</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $detailIndex = 1; @endphp
                @foreach($purchases as $purchase)
                    @if($purchase->details)
                        @foreach($purchase->details as $detail)
                        <tr>
                            <td>{{ $detailIndex++ }}</td>
                            <td><strong>#{{ $purchase->id }}</strong></td>
                            <td><strong>{{ $detail->product->name ?? 'N/A' }}</strong></td>
                            <td>{{ $detail->quantity ?? 0 }} unidad(es)</td>
                            <td>${{ number_format($detail->purchase_price ?? 0, 2) }}</td>
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
        <p>Este documento contiene información confidencial de las compras del proveedor especificado</p>
        <p>Página 1 de 1</p>
    </div>
</body>
</html> 