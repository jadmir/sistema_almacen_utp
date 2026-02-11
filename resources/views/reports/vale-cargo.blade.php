<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Vale de Cargo - {{ $movimiento->numero_vale }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 15px;
            font-size: 9pt;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            border: 2px solid #000;
            padding: 8px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 14pt;
            margin-bottom: 3px;
        }
        .header h2 {
            font-size: 11pt;
            color: #333;
        }
        .numero-vale {
            text-align: right;
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .info-grid {
            display: table;
            width: 100%;
            border: 1px solid #000;
            margin-bottom: 8px;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            border: 1px solid #000;
            padding: 4px 6px;
            width: 25%;
        }
        .label {
            font-weight: bold;
        }
        .seccion-titulo {
            background-color: #e0e0e0;
            padding: 4px 6px;
            font-weight: bold;
            font-size: 9pt;
            border: 1px solid #000;
            margin-bottom: 5px;
        }
        .productos-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .productos-table th {
            background-color: #e0e0e0;
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
        }
        .productos-table td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 8pt;
        }
        .receptor-grid {
            display: table;
            width: 100%;
            border: 1px solid #000;
            margin-bottom: 8px;
        }
        .receptor-row {
            display: table-row;
        }
        .receptor-cell {
            display: table-cell;
            border: 1px solid #000;
            padding: 4px 6px;
        }
        .firmas {
            margin-top: 15px;
            display: table;
            width: 100%;
        }
        .firma {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 5px;
        }
        .firma-linea {
            border-top: 1px solid #000;
            margin: 35px 20px 3px 20px;
        }
        .firma-texto {
            font-size: 8pt;
        }
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 7pt;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        <h1>UNIVERSIDAD TECNOLÓGICA DEL PERÚ</h1>
        <h2>VALE DE CARGO</h2>
    </div>

    <!-- Número de Vale -->
    <div class="numero-vale">
        N° {{ $movimiento->numero_vale }}
    </div>

    <!-- Información General en Grid -->
    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell"><span class="label">Fecha:</span> {{ \Carbon\Carbon::parse($movimiento->fecha_movimiento)->format('d/m/Y') }}</div>
            <div class="info-cell"><span class="label">Hora:</span> {{ $movimiento->created_at->format('H:i') }}</div>
            <div class="info-cell"><span class="label">Entregado por:</span> {{ $movimiento->user->nombre }}</div>
            <div class="info-cell"><span class="label">Área:</span> {{ $movimiento->area->codigo }}</div>
        </div>
    </div>

    <!-- Productos Entregados -->
    <div class="seccion-titulo">PRODUCTO ENTREGADO</div>
    <table class="productos-table">
        <thead>
            <tr>
                <th style="width: 15%;">Código</th>
                <th style="width: 35%;">Descripción</th>
                <th style="width: 10%;">Cantidad</th>
                <th style="width: 10%;">Unidad</th>
                <th style="width: 30%;">Motivo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $movimiento->product->codigo }}</td>
                <td>{{ $movimiento->product->nombre }}</td>
                <td style="text-align: center;">{{ $movimiento->cantidad }}</td>
                <td>{{ $movimiento->product->unidad_medida }}</td>
                <td>{{ $movimiento->motivo ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Datos del Receptor en Grid -->
    <div class="seccion-titulo">DATOS DEL RECEPTOR</div>
    <div class="receptor-grid">
        <div class="receptor-row">
            <div class="receptor-cell" style="width: 40%;"><span class="label">Nombre:</span> {{ $movimiento->recibido_por }}</div>
            <div class="receptor-cell" style="width: 20%;"><span class="label">DNI:</span> {{ $movimiento->dni_receptor }}</div>
            <div class="receptor-cell" style="width: 40%;"><span class="label">Cargo:</span> {{ $movimiento->cargo_receptor }}</div>
        </div>
    </div>

    <!-- Firmas -->
    <div class="firmas">
        <div class="firma">
            <div class="firma-linea"></div>
            <div class="firma-texto">
                <strong>ENTREGA</strong><br>
                {{ $movimiento->user->nombre }}<br>
                Asistente/Administrador
            </div>
        </div>
        <div class="firma">
            <div class="firma-linea"></div>
            <div class="firma-texto">
                <strong>RECIBE CONFORME</strong><br>
                {{ $movimiento->recibido_por }}<br>
                DNI: {{ $movimiento->dni_receptor }}
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Documento válido como comprobante de entrega - Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
