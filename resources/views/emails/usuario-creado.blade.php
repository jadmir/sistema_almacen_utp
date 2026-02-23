<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido al Sistema de Almacén UTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #003d82;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .welcome-text {
            font-size: 18px;
            margin-bottom: 20px;
            color: #003d82;
        }
        .credentials-box {
            background-color: #f8f9fa;
            border-left: 4px solid #003d82;
            padding: 20px;
            margin: 20px 0;
        }
        .credentials-box h3 {
            margin-top: 0;
            color: #003d82;
        }
        .credential-item {
            margin: 15px 0;
            padding: 10px;
            background-color: white;
            border-radius: 4px;
        }
        .credential-label {
            font-weight: bold;
            color: #666;
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .credential-value {
            font-size: 18px;
            color: #003d82;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .instructions {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .instructions h4 {
            margin-top: 0;
            color: #856404;
        }
        .instructions ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .instructions li {
            margin: 8px 0;
            color: #856404;
        }
        .button {
            display: inline-block;
            background-color: #003d82;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #dee2e6;
        }
        .warning {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .warning strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏢 Sistema de Almacén UTP</h1>
        </div>

        <div class="content">
            <p class="welcome-text">¡Hola, <strong>{{ $usuario->nombre }}</strong>!</p>

            <p>Se ha creado una cuenta para ti en el Sistema de Almacén de la Universidad Tecnológica del Perú.</p>

            <div class="credentials-box">
                <h3>🔑 Tus credenciales de acceso:</h3>

                <div class="credential-item">
                    <span class="credential-label">Usuario (Correo Electrónico)</span>
                    <span class="credential-value">{{ $usuario->email }}</span>
                </div>

                <div class="credential-item">
                    <span class="credential-label">Contraseña Temporal</span>
                    <span class="credential-value">{{ $passwordTemporal }}</span>
                </div>
            </div>

            <div class="warning">
                <strong>⚠️ IMPORTANTE - Seguridad</strong>
                Esta es una contraseña temporal. Por tu seguridad, el sistema te pedirá que cambies tu contraseña en el primer inicio de sesión.
            </div>

            <div class="instructions">
                <h4>📋 Instrucciones para tu primer acceso:</h4>
                <ul>
                    <li>Ingresa al sistema con tu correo electrónico y la contraseña temporal</li>
                    <li>El sistema te pedirá cambiar tu contraseña</li>
                    <li>Crea una contraseña segura que incluya:
                        <ul>
                            <li>Mínimo 8 caracteres</li>
                            <li>Al menos una letra mayúscula</li>
                            <li>Al menos una letra minúscula</li>
                            <li>Al menos un número</li>
                        </ul>
                    </li>
                    <li>No compartas tu contraseña con nadie</li>
                </ul>
            </div>

            <center>
                <a href="{{ config('app.frontend_url', config('app.url')) }}" class="button">Acceder al Sistema</a>
            </center>

            <p style="margin-top: 30px; color: #666; font-size: 14px;">
                <strong>Rol asignado:</strong> {{ $usuario->role->nombre ?? 'N/A' }}<br>
                <strong>Correo registrado:</strong> {{ $usuario->email }}
            </p>
        </div>

        <div class="footer">
            <p>
                <strong>Sistema de Almacén UTP</strong><br>
                Universidad Tecnológica del Perú<br>
                Este es un correo automático, por favor no responder.
            </p>
            <p style="margin-top: 10px; font-size: 11px; color: #999;">
                Si tienes problemas para acceder al sistema, contacta al administrador.
            </p>
        </div>
    </div>
</body>
</html>
