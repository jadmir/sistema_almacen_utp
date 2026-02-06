<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte por Sección</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #6f42c1;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 5px 0;
            color: #6f42c1;
            font-size: 18px;
        }
        .header .date {
            color: #666;
            font-size: 9px;
        }
        .section-header {
            background-color: #f0e6ff;
            padding: 10px;
            margin: 20px 0 10px 0;
            border-left: 4px solid #6f42c1;
            font-size: 12px;
            font-weight: bold;
        }
        .section-summary {
            background-color: #f8f9fa;
            padding: 8px;
            margin-bottom: 10px;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #6f42c1;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
        }
        td {
            border-bottom: 1px solid #ddd;
            padding: 6px 5px;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: black;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        .summary {
            margin: 15px 0;
            padding: 10px;
            background-color: #f0e6ff;
            border-radius: 5px;
        }
        .summary strong {
            color: #6f42c1;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📂 REPORTE POR SECCIÓN</h1>
        <div class="date">Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="summary">
        <strong>Total de productos:</strong> {{ $productos->count() }}
        | <strong>Secciones:</strong> {{ $productos->groupBy('section_id')->count() }}
        @if($productos->where('stock_actual', '<=', 'stock_minimo')->count() > 0)
            | <strong style="color: #dc3545;">Con stock bajo:</strong> {{ $productos->where('stock_actual', '<=', 'stock_minimo')->count() }}
        @endif
    </div>

    @foreach($productos->groupBy('section.nombre') as $seccionNombre => $productosPorSeccion)
        <div class="section-header">
            {{ $seccionNombre }}
            <span style="float: right; font-size: 10px; color: #6f42c1;">
                {{ $productosPorSeccion->first()->section->codigo }} | 
                {{ $productosPorSeccion->first()->section->stockType->nombre }}
            </span>
        </div>

        <div class="section-summary">
            <strong>Productos en esta sección:</strong> {{ $productosPorSeccion->count() }}
            | <strong>Stock total:</strong> {{ $productosPorSeccion->sum('stock_actual') }} unidades
            | <strong>Con stock bajo:</strong> {{ $productosPorSeccion->where('stock_actual', '<=', 'stock_minimo')->count() }}
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Código</th>
                    <th style="width: 35%;">Producto</th>
                    <th style="width: 10%;" class="text-right">Stock</th>
                    <th style="width: 10%;" class="text-right">Mínimo</th>
                    <th style="width: 8%;">Unidad</th>
                    <th style="width: 12%;" class="text-center">Vencimiento</th>
                    <th style="width: 13%;" class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productosPorSeccion as $producto)
                <tr>
                    <td><strong>{{ $producto->codigo }}</strong></td>
                    <td>{{ $producto->nombre }}</td>
                    <td class="text-right"><strong>{{ $producto->stock_actual }}</strong></td>
                    <td class="text-right">{{ $producto->stock_minimo }}</td>
                    <td>{{ $producto->unidad_medida }}</td>
                    <td class="text-center">
                        @if($producto->tiene_vencimiento && $producto->fecha_vencimiento)
                            {{ $producto->fecha_vencimiento->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if($producto->stock_actual == 0)
                            <span class="badge badge-danger">SIN STOCK</span>
                        @elseif($producto->stock_actual <= $producto->stock_minimo)
                            <span class="badge badge-warning">STOCK BAJO</span>
                        @else
                            <span class="badge badge-success">NORMAL</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="footer">
        <p>Sistema de Almacén UTP - Reporte generado automáticamente</p>
    </div>
</body>
</html>
