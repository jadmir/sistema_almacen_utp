<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Productos con Stock Bajo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 5px 0;
            color: #dc3545;
            font-size: 18px;
        }
        .header .date {
            color: #666;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #dc3545;
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
            background-color: #fff3f3;
        }
        .critico {
            background-color: #ffe6e6 !important;
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
        .alert {
            margin: 15px 0;
            padding: 10px;
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 5px;
        }
        .alert strong {
            color: #dc3545;
        }
        .progress-bar {
            width: 100%;
            height: 15px;
            background-color: #f0f0f0;
            border-radius: 3px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            text-align: center;
            color: white;
            font-size: 8px;
            line-height: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚠️ PRODUCTOS CON STOCK BAJO</h1>
        <div class="date">Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="alert">
        <strong>⚠️ ALERTA:</strong> {{ $productos->count() }} producto(s) con stock en nivel crítico o por debajo del mínimo.
        @if($productos->where('stock_actual', 0)->count() > 0)
            <br><strong style="color: #dc3545;">{{ $productos->where('stock_actual', 0)->count() }} producto(s) SIN STOCK.</strong>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Código</th>
                <th style="width: 28%;">Producto</th>
                <th style="width: 15%;">Sección</th>
                <th style="width: 8%;" class="text-right">Stock Actual</th>
                <th style="width: 8%;" class="text-right">Stock Mín.</th>
                <th style="width: 7%;">Unidad</th>
                <th style="width: 12%;" class="text-center">Nivel</th>
                <th style="width: 12%;" class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $producto)
            @php
                $porcentaje = $producto->stock_minimo > 0 
                    ? round(($producto->stock_actual / $producto->stock_minimo) * 100, 1)
                    : 0;
                $color = $producto->stock_actual == 0 ? '#dc3545' : '#ffc107';
            @endphp
            <tr class="{{ $producto->stock_actual == 0 ? 'critico' : '' }}">
                <td><strong>{{ $producto->codigo }}</strong></td>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->section->nombre }}</td>
                <td class="text-right"><strong>{{ $producto->stock_actual }}</strong></td>
                <td class="text-right">{{ $producto->stock_minimo }}</td>
                <td>{{ $producto->unidad_medida }}</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ min($porcentaje, 100) }}%; background-color: {{ $color }};">
                            {{ $porcentaje }}%
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    @if($producto->stock_actual == 0)
                        <span class="badge badge-danger">🚫 SIN STOCK</span>
                    @else
                        <span class="badge badge-warning">⚠️ CRÍTICO</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema de Almacén UTP - Reporte generado automáticamente</p>
        <p><strong>Acción requerida:</strong> Revisar y solicitar reposición de productos listados</p>
    </div>
</body>
</html>
