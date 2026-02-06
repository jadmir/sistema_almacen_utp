<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Productos Vencidos</title>
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
            background-color: #ffe6e6;
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
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        .alert {
            margin: 15px 0;
            padding: 10px;
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            border-radius: 5px;
            color: #721c24;
        }
        .alert strong {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>❌ PRODUCTOS VENCIDOS</h1>
        <div class="date">Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="alert">
        <strong>⚠️ CRÍTICO:</strong> {{ $productos->count() }} producto(s) con fecha de vencimiento cumplida.
        <br><strong>Acción inmediata:</strong> Retirar del inventario y gestionar disposición según protocolo.
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Código</th>
                <th style="width: 28%;">Producto</th>
                <th style="width: 15%;">Sección</th>
                <th style="width: 12%;">Tipo Stock</th>
                <th style="width: 8%;" class="text-right">Stock</th>
                <th style="width: 7%;">Unidad</th>
                <th style="width: 12%;" class="text-center">Fecha Venc.</th>
                <th style="width: 8%;" class="text-center">Días Venc.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $producto)
            @php
                $diasVencidos = abs(\Carbon\Carbon::now()->diffInDays($producto->fecha_vencimiento, false));
            @endphp
            <tr>
                <td><strong>{{ $producto->codigo }}</strong></td>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->section->nombre }}</td>
                <td>{{ $producto->section->stockType->nombre }}</td>
                <td class="text-right"><strong>{{ $producto->stock_actual }}</strong></td>
                <td>{{ $producto->unidad_medida }}</td>
                <td class="text-center">{{ $producto->fecha_vencimiento->format('d/m/Y') }}</td>
                <td class="text-center">
                    <span class="badge badge-danger">{{ (int)$diasVencidos }} días</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema de Almacén UTP - Reporte generado automáticamente</p>
        <p><strong>ADVERTENCIA:</strong> Productos vencidos no deben ser utilizados ni distribuidos</p>
    </div>
</body>
</html>
