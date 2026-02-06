<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Historial de Movimientos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #17a2b8;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 5px 0;
            color: #17a2b8;
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
            background-color: #17a2b8;
            color: white;
            padding: 6px 3px;
            text-align: left;
            font-size: 8px;
        }
        td {
            border-bottom: 1px solid #ddd;
            padding: 5px 3px;
            font-size: 8px;
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
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7px;
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
            background-color: #e7f6f8;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 HISTORIAL DE MOVIMIENTOS</h1>
        <div class="date">Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</div>
        @if(!empty($filtros['fecha_desde']) || !empty($filtros['fecha_hasta']))
            <div class="date">
                Período: 
                {{ !empty($filtros['fecha_desde']) ? \Carbon\Carbon::parse($filtros['fecha_desde'])->format('d/m/Y') : 'Inicio' }}
                - 
                {{ !empty($filtros['fecha_hasta']) ? \Carbon\Carbon::parse($filtros['fecha_hasta'])->format('d/m/Y') : 'Hoy' }}
            </div>
        @endif
    </div>

    <div class="summary">
        <strong>Total de movimientos:</strong> {{ $movimientos->count() }}
        | <strong style="color: #28a745;">Entradas:</strong> {{ $movimientos->where('tipo', 'ENTRADA')->count() }}
        | <strong style="color: #dc3545;">Salidas:</strong> {{ $movimientos->where('tipo', 'SALIDA')->count() }}
        | <strong style="color: #ffc107;">Ajustes:</strong> {{ $movimientos->where('tipo', 'AJUSTE')->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Fecha</th>
                <th style="width: 7%;">Tipo</th>
                <th style="width: 9%;">Código</th>
                <th style="width: 18%;">Producto</th>
                <th style="width: 12%;">Sección</th>
                <th style="width: 5%;" class="text-right">Cant.</th>
                <th style="width: 6%;" class="text-right">St. Ant.</th>
                <th style="width: 6%;" class="text-right">St. Post.</th>
                <th style="width: 12%;">Área</th>
                <th style="width: 15%;">Motivo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientos as $mov)
            <tr>
                <td>{{ \Carbon\Carbon::parse($mov->created_at)->format('d/m/Y H:i') }}</td>
                <td class="text-center">
                    @if($mov->tipo === 'ENTRADA')
                        <span class="badge badge-success">📥 ENT</span>
                    @elseif($mov->tipo === 'SALIDA')
                        <span class="badge badge-danger">📤 SAL</span>
                    @else
                        <span class="badge badge-warning">⚙️ AJU</span>
                    @endif
                </td>
                <td><strong>{{ $mov->product->codigo }}</strong></td>
                <td>{{ $mov->product->nombre }}</td>
                <td>{{ $mov->product->section->nombre }}</td>
                <td class="text-right"><strong>{{ $mov->cantidad }}</strong></td>
                <td class="text-right">{{ $mov->stock_anterior }}</td>
                <td class="text-right">{{ $mov->stock_posterior }}</td>
                <td>{{ $mov->area ? $mov->area->nombre : '-' }}</td>
                <td>{{ Str::limit($mov->motivo, 30) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema de Almacén UTP - Reporte generado automáticamente</p>
    </div>
</body>
</html>
