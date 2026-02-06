<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kardex de Producto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4472C4;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 5px 0;
            color: #4472C4;
            font-size: 18px;
        }
        .header .date {
            color: #666;
            font-size: 9px;
        }
        .product-info {
            margin: 15px 0;
            padding: 15px;
            background-color: #f0f5ff;
            border-radius: 5px;
            border-left: 4px solid #4472C4;
        }
        .product-info table {
            width: 100%;
            border: none;
        }
        .product-info td {
            padding: 5px;
            border: none;
            font-size: 10px;
        }
        .product-info strong {
            color: #4472C4;
        }
        table.kardex {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table.kardex th {
            background-color: #4472C4;
            color: white;
            padding: 8px 5px;
            text-align: center;
            font-size: 9px;
        }
        table.kardex td {
            border-bottom: 1px solid #ddd;
            padding: 6px 5px;
            font-size: 9px;
        }
        table.kardex tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .tipo-entrada {
            color: #28a745;
            font-weight: bold;
        }
        .tipo-salida {
            color: #dc3545;
            font-weight: bold;
        }
        .tipo-ajuste {
            color: #ffc107;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 KARDEX DE PRODUCTO</h1>
        <div class="date">Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</div>
        @if($fechaDesde || $fechaHasta)
            <div class="date">
                Período: 
                {{ $fechaDesde ? \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') : 'Inicio' }}
                - 
                {{ $fechaHasta ? \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') : 'Hoy' }}
            </div>
        @endif
    </div>

    <div class="product-info">
        <table>
            <tr>
                <td style="width: 20%;"><strong>Código:</strong></td>
                <td style="width: 30%;">{{ $producto->codigo }}</td>
                <td style="width: 20%;"><strong>Sección:</strong></td>
                <td style="width: 30%;">{{ $producto->section->nombre }}</td>
            </tr>
            <tr>
                <td><strong>Producto:</strong></td>
                <td colspan="3">{{ $producto->nombre }}</td>
            </tr>
            <tr>
                <td><strong>Tipo de Stock:</strong></td>
                <td>{{ $producto->section->stockType->nombre }}</td>
                <td><strong>Unidad:</strong></td>
                <td>{{ $producto->unidad_medida }}</td>
            </tr>
            <tr>
                <td><strong>Stock Actual:</strong></td>
                <td><strong style="font-size: 14px; color: #4472C4;">{{ $producto->stock_actual }}</strong> {{ $producto->unidad_medida }}</td>
                <td><strong>Stock Mínimo:</strong></td>
                <td>{{ $producto->stock_minimo }} {{ $producto->unidad_medida }}</td>
            </tr>
            @if($producto->tiene_vencimiento)
            <tr>
                <td><strong>Fecha Vencimiento:</strong></td>
                <td>{{ $producto->fecha_vencimiento ? $producto->fecha_vencimiento->format('d/m/Y') : 'N/A' }}</td>
                <td colspan="2"></td>
            </tr>
            @endif
        </table>
    </div>

    <table class="kardex">
        <thead>
            <tr>
                <th style="width: 12%;">Fecha</th>
                <th style="width: 10%;">Tipo</th>
                <th style="width: 8%;">Entrada</th>
                <th style="width: 8%;">Salida</th>
                <th style="width: 8%;">Ajuste</th>
                <th style="width: 8%;">St. Ant.</th>
                <th style="width: 8%;">St. Post.</th>
                <th style="width: 15%;">Área</th>
                <th style="width: 23%;">Motivo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientos as $mov)
            <tr>
                <td>{{ \Carbon\Carbon::parse($mov->created_at)->format('d/m/Y H:i') }}</td>
                <td class="text-center">
                    @if($mov->tipo === 'ENTRADA')
                        <span class="tipo-entrada">📥 ENTRADA</span>
                    @elseif($mov->tipo === 'SALIDA')
                        <span class="tipo-salida">📤 SALIDA</span>
                    @else
                        <span class="tipo-ajuste">⚙️ AJUSTE</span>
                    @endif
                </td>
                <td class="text-right tipo-entrada">
                    {{ $mov->tipo === 'ENTRADA' ? $mov->cantidad : '' }}
                </td>
                <td class="text-right tipo-salida">
                    {{ $mov->tipo === 'SALIDA' ? $mov->cantidad : '' }}
                </td>
                <td class="text-right tipo-ajuste">
                    {{ $mov->tipo === 'AJUSTE' ? ($mov->cantidad >= 0 ? '+' : '') . $mov->cantidad : '' }}
                </td>
                <td class="text-right">{{ $mov->stock_anterior }}</td>
                <td class="text-right"><strong>{{ $mov->stock_posterior }}</strong></td>
                <td>{{ $mov->area ? $mov->area->nombre : '-' }}</td>
                <td>{{ $mov->motivo }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema de Almacén UTP - Reporte generado automáticamente</p>
        <p><strong>Total de movimientos:</strong> {{ $movimientos->count() }}</p>
    </div>
</body>
</html>
