<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Productos Próximos a Vencer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ffc107;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 5px 0;
            color: #ffc107;
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
            background-color: #ffc107;
            color: black;
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
            background-color: #fffaf0;
        }
        .urgente {
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
    </style>
</head>
<body>
    <div class="header">
        <h1>📅 PRODUCTOS PRÓXIMOS A VENCER</h1>
        <div class="date">Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</div>
        <div class="date">Ventana de tiempo: Próximos {{ $dias }} días</div>
    </div>

    <div class="alert">
        <strong>⚠️ ATENCIÓN:</strong> {{ $productos->count() }} producto(s) próximos a vencer en los siguientes {{ $dias }} días.
        @if($productos->filter(function($p) { return \Carbon\Carbon::now()->diffInDays($p->fecha_vencimiento, false) <= 7; })->count() > 0)
            <br><strong style="color: #dc3545;">
                {{ $productos->filter(function($p) { return \Carbon\Carbon::now()->diffInDays($p->fecha_vencimiento, false) <= 7; })->count() }} 
                producto(s) URGENTE (7 días o menos).
            </strong>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Código</th>
                <th style="width: 28%;">Producto</th>
                <th style="width: 15%;">Sección</th>
                <th style="width: 8%;" class="text-right">Stock</th>
                <th style="width: 7%;">Unidad</th>
                <th style="width: 12%;" class="text-center">Fecha Venc.</th>
                <th style="width: 10%;" class="text-center">Días Rest.</th>
                <th style="width: 10%;" class="text-center">Urgencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $producto)
            @php
                $diasRestantes = \Carbon\Carbon::now()->diffInDays($producto->fecha_vencimiento, false);
                $urgente = $diasRestantes <= 7;
            @endphp
            <tr class="{{ $urgente ? 'urgente' : '' }}">
                <td><strong>{{ $producto->codigo }}</strong></td>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->section->nombre }}</td>
                <td class="text-right"><strong>{{ $producto->stock_actual }}</strong></td>
                <td>{{ $producto->unidad_medida }}</td>
                <td class="text-center">{{ $producto->fecha_vencimiento->format('d/m/Y') }}</td>
                <td class="text-center"><strong>{{ (int)$diasRestantes }}</strong></td>
                <td class="text-center">
                    @if($urgente)
                        <span class="badge badge-danger">🚨 URGENTE</span>
                    @else
                        <span class="badge badge-warning">⚠️ PRÓXIMO</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema de Almacén UTP - Reporte generado automáticamente</p>
        <p><strong>Acción requerida:</strong> Planificar uso o disposición de productos próximos a vencer</p>
    </div>
</body>
</html>
