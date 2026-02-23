<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de Contraseña</title>
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
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #003d82;
        }
        .message-box {
            background-color: #f8f9fa;
            border-left: 4px solid #003d82;
            padding: 20px;
            margin: 20px 0;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background-color: #003d82;
            color: white !important;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
        }
        .button:hover {
            background-color: #002a5c;
        }
        .expiration {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .expiration strong {
            display: block;
            margin-bottom: 5px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #dee2e6;
        }
        .security-note {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .link-fallback {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            word-break: break-all;
        }
        .link-fallback p {
            margin: 5px 0;
            font-size: 12px;
            color: #666;
        }
        .link-fallback a {
            color: #003d82;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Recuperación de Contraseña</h1>
        </div>

        <div class="content">
            <p class="greeting">Hola, <strong>{{ $nombre }}</strong></p>

            <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en el Sistema de Almacén UTP.</p>

            <div class="message-box">
                <p style="margin: 0;">
                    Si realizaste esta solicitud, haz clic en el botón de abajo para crear una nueva contraseña:
                </p>
            </div>

            <div class="button-container">
                <a href="{{ config('app.frontend_url', config('app.url')) }}/reset-password?token={{ $token }}&email={{ urlencode($email) }}" class="button">
                    Restablecer Contraseña
                </a>
            </div>

            <div class="link-fallback">
                <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                <a href="{{ config('app.frontend_url', config('app.url')) }}/reset-password?token={{ $token }}&email={{ urlencode($email) }}">
                    {{ config('app.frontend_url', config('app.url')) }}/reset-password?token={{ $token }}&email={{ urlencode($email) }}
                </a>
            </div>

            <div class="expiration">
                <strong>⏰ Tiempo de validez</strong>
                Este enlace es válido por 60 minutos. Después de ese tiempo, deberás solicitar uno nuevo.
            </div>

            <div class="security-note">
                <strong>🛡️ Nota de Seguridad</strong>
                Si NO solicitaste restablecer tu contraseña, ignora este correo. Tu cuenta permanece segura y no se realizarán cambios.
            </div>

            <p style="margin-top: 30px; color: #666; font-size: 14px;">
                Si tienes problemas o preguntas, contacta al administrador del sistema.
            </p>
        </div>

        <div class="footer">
            <p>
                <strong>Sistema de Almacén UTP</strong><br>
                Universidad Tecnológica del Perú<br>
                Este es un correo automático, por favor no responder.
            </p>
            <p style="margin-top: 10px; font-size: 11px; color: #999;">
                © {{ date('Y') }} Sistema de Almacén UTP. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
