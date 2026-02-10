# 📦 Manual de Instalación - Sistema de Almacén UTP
## Windows - IIS (Internet Information Services)

---

## 📋 Índice

1. [Arquitectura del Sistema](#arquitectura-del-sistema)
2. [Requisitos del Sistema](#requisitos-del-sistema)
3. [Instalación de IIS y PHP](#instalación-de-iis-y-php)
4. [Instalación de MySQL](#instalación-de-mysql)
5. [Configuración del Backend Laravel](#configuración-del-backend-laravel)
6. [Configuración del Frontend Vue](#configuración-del-frontend-vue)
7. [Solución de Problemas](#solución-de-problemas)
8. [Uso Diario](#uso-diario)

---

## 🏗️ Arquitectura del Sistema

Este sistema está dividido en **dos proyectos independientes**:

- **Backend (API)**: Laravel - Sirve la API REST (este proyecto)
- **Frontend (UI)**: Vue.js - Interfaz de usuario (proyecto separado)

Ambos se configuran en IIS:
- **Backend**: http://localhost:8000 (o tu dominio/api)
- **Frontend**: http://localhost:80 (o tu dominio)

**IIS (Internet Information Services)** es el servidor web profesional de Microsoft, ideal para:
- ✅ Servidores de producción
- ✅ Windows Server 2019/2022
- ✅ Windows 11 Pro/Enterprise
- ✅ Ambientes corporativos con alta disponibilidad
- ✅ Instalaciones permanentes 24/7
- ✅ No requiere "encenderlo" cada día (funciona como servicio)

---

## ✅ Requisitos del Sistema

### Hardware Mínimo:
- **Sistema Operativo**: Windows Server 2019/2022 o Windows 11 Pro/Enterprise
- **RAM**: 8 GB (recomendado 16 GB)
- **Espacio en Disco**: 5 GB libres
- **Procesador**: Intel Core i5 o equivalente

### Software que se instalará:
- IIS (Internet Information Services)
- PHP 8.2 o superior (Non-Thread Safe)
- Composer (gestor de dependencias PHP)
- **MySQL 8.0.x LTS** (específicamente 8.0.36 o superior)
- URL Rewrite Module para IIS
- Node.js 18+ LTS (para construcción del frontend Vue)

---

## 🔧 1. Instalación de IIS y PHP

### Paso 1: Habilitar IIS en Windows

#### Para Windows 11 Pro/Enterprise:

1. Presiona **Windows + R**, escribe `appwiz.cpl` y presiona Enter
2. Haz clic en **Activar o desactivar las características de Windows**
3. Marca las siguientes casillas:
   - ✅ **Internet Information Services**
     - ✅ Herramientas de administración web
       - ✅ **Consola de administración de IIS**
     - ✅ Servicios World Wide Web
       - ✅ Características de desarrollo de aplicaciones
         - ✅ **CGI**
         - ✅ **Extensibilidad ISAPI**
         - ✅ **Filtros ISAPI**
       - ✅ Características HTTP comunes (todas)
         - ✅ Contenido estático
         - ✅ Documento predeterminado
         - ✅ Examen de directorios
         - ✅ Errores HTTP
4. Haz clic en **Aceptar** y espera la instalación (2-5 minutos)
5. Reinicia tu PC

#### Para Windows Server 2019/2022:

1. Abre **Administrador del servidor**
2. Haz clic en **Administrar** → **Agregar roles y características**
3. Selecciona **Servidor Web (IIS)**
4. En **Servicios de rol**, marca:
   - ✅ Servidor Web
     - ✅ Seguridad → Filtrado de solicitudes
     - ✅ Rendimiento → Compresión de contenido dinámico
     - ✅ Desarrollo de aplicaciones:
       - ✅ **CGI**
       - ✅ **ISAPI Extensions**
       - ✅ **ISAPI Filters**
   - ✅ Herramientas de administración
     - ✅ Consola de administración de IIS
5. Haz clic en **Siguiente** → **Instalar**
6. Espera a que termine la instalación

**Verificar IIS:**
- Abre el navegador
- Ve a: http://localhost
- Debes ver la página de bienvenida de IIS ✅

---

### Paso 2: Instalar PHP en IIS

1. **Descargar PHP:**
   - Ve a: https://windows.php.net/download/
   - Descarga **PHP 8.2 Non Thread Safe (NTS)** x64
   - Archivo: `php-8.2.x-nts-Win32-vs16-x64.zip`
   - ⚠️ **IMPORTANTE:** Debe ser **Non Thread Safe** para FastCGI

2. **Instalar PHP:**
   - Crea la carpeta `C:\PHP`
   - Extrae todo el contenido del ZIP en `C:\PHP`
   - Debes tener archivos como `C:\PHP\php.exe`, `C:\PHP\php-cgi.exe`, etc.

3. **Configurar PHP:**
   - En `C:\PHP`, encuentra el archivo `php.ini-production`
   - Cópialo y renómbralo como `php.ini`
   - Abre `php.ini` con el Bloc de notas (como Administrador)
   
4. **Editar php.ini - Busca y modifica estas líneas:**

   ```ini
   ; Quita el punto y coma (;) al inicio de estas líneas:
   extension_dir = "ext"
   extension=curl
   extension=fileinfo
   extension=gd
   extension=mbstring
   extension=mysqli
   extension=openssl
   extension=pdo_mysql
   extension=zip
   
   ; Configura la zona horaria:
   date.timezone = America/Lima
   
   ; Aumenta límites:
   upload_max_filesize = 64M
   post_max_size = 64M
   memory_limit = 256M
   max_execution_time = 300
   
   ; Habilita FastCGI:
   cgi.fix_pathinfo=1
   fastcgi.impersonate = 1
   cgi.force_redirect = 0
   ```

5. **Agregar PHP al PATH:**
   - Presiona **Windows + Pausa** → **Configuración avanzada del sistema**
   - Haz clic en **Variables de entorno**
   - En **Variables del sistema**, encuentra `Path` y haz clic en **Editar**
   - Haz clic en **Nuevo** y agrega: `C:\PHP`
   - Haz clic en **Aceptar** en todas las ventanas

6. **Verificar PHP:**
   - Abre PowerShell como Administrador
   - Ejecuta:
     ```powershell
     php -v
     ```
   - Debes ver: `PHP 8.2.x (cli)` ✅

---

### Paso 3: Configurar FastCGI en IIS

1. **Abrir Administrador de IIS:**
   - Presiona **Windows + S**
   - Busca **Administrador de Internet Information Services**
   - Ábrelo como Administrador

2. **Configurar asignación de controladores:**
   - En el panel izquierdo, selecciona el **nombre de tu servidor**
   - En el panel central, haz doble clic en **Asignaciones de controlador**
   - En el panel derecho, haz clic en **Agregar asignación de módulo...**
   
   Configura así:
   ```
   Ruta de acceso de solicitud: *.php
   Módulo: FastCgiModule
   Ejecutable: C:\PHP\php-cgi.exe
   Nombre: PHP_via_FastCGI
   ```
   
   - Haz clic en **Aceptar**
   - Si pregunta "¿Desea crear una asignación FastCGI?", haz clic en **Sí**

3. **Configurar FastCGI Settings:**
   - En el servidor, haz doble clic en **Configuración de FastCGI**
   - Haz doble clic en la entrada de PHP (`C:\PHP\php-cgi.exe`)
   - Configura:
     ```
     Instancias máximas: 4
     Límite de tiempo de actividad (segundos): 600
     Solicitudes máximas: 10000
     ```
   - En **Variables de entorno**, agrega:
     ```
     Variable: PHP_FCGI_MAX_REQUESTS
     Valor: 10000
     ```
   - Haz clic en **Aceptar**

---

### Paso 4: Instalar URL Rewrite Module

Laravel requiere URL rewriting para funcionar correctamente.

1. **Descargar URL Rewrite:**
   - Ve a: https://www.iis.net/downloads/microsoft/url-rewrite
   - Descarga **URL Rewrite Module 2.1**
   - O busca en Google: "IIS URL Rewrite download"

2. **Instalar:**
   - Ejecuta el instalador `rewrite_amd64.msi`
   - Sigue el asistente hasta finalizar
   - **Reinicia IIS:**
     ```powershell
     iisreset
     ```

---

### Paso 5: Instalar Composer

1. **Descargar Composer:**
   - Ve a: https://getcomposer.org/download/
   - Descarga `Composer-Setup.exe`

2. **Instalar Composer:**
   - Ejecuta el instalador
   - En "PHP Installation", debe detectar: `C:\PHP\php.exe`
   - Si no lo detecta, navega manualmente a `C:\PHP\php.exe`
   - Deja marcado "Add to PATH"
   - Completa la instalación

3. **Verificar:**
   ```powershell
   composer --version
   ```
   - Debes ver: `Composer version 2.x.x` ✅

---

## 🗄️ 2. Instalación de MySQL

### Versión Recomendada: MySQL 8.0.x LTS

Este proyecto está configurado para MySQL 8.0+ (compatible con PHP 8.2 y Laravel 12).

1. **Descargar MySQL:**
   - Ve a: https://dev.mysql.com/downloads/installer/
   - Descarga **MySQL Installer for Windows** (versión **8.0.36** o superior)
   - Elige la versión **Full** (aproximadamente 500 MB)
   - ⚠️ **Importante**: Asegúrate de descargar MySQL **8.0.x**, NO la versión 5.7 o MySQL 9.0 (innovation)

2. **Instalar MySQL:**
   - Ejecuta el instalador
   - Selecciona **Server only** o **Developer Default**
   - En la lista de productos, verifica que diga **MySQL Server 8.0.x**
   - Haz clic en **Execute** para descargar e instalar
   - Espera a que termine (5-10 minutos)

3. **Configurar MySQL:**
   - Tipo de configuración: **Development Computer** (o **Server Computer** para producción)
   - Puerto: **3306** (dejar por defecto)
   - Authentication Method: **Use Strong Password Encryption** (recomendado para MySQL 8.0+)
   - Root password: Elige una contraseña segura (⚠️ **anótala**)
   - Haz clic en **Next** hasta finalizar
   - MySQL se instalará como servicio de Windows ✅

4. **Verificar MySQL:**
   - Abre PowerShell
   - Ejecuta:
     ```powershell
     mysql -u root -p
     ```
   - Ingresa la contraseña que configuraste
   - Si entras a la consola MySQL¡funciona! ✅
   - Escribe `exit` para salir

---

## 🚀 3. Configuración del Backend Laravel

### Paso 1: Copiar el Proyecto Backend

1. **Ubicación del proyecto:**
   - Copia la carpeta `sistema_almacen_utp` (este proyecto) a `C:\inetpub\wwwroot\`
   - Ruta final: `C:\inetpub\wwwroot\sistema_almacen_utp`

---

### Paso 2: Instalar Dependencias

1. **Abrir PowerShell como Administrador**
2. **Navegar al proyecto:**
   ```powershell
   cd C:\inetpub\wwwroot\sistema_almacen_utp
   ```
3. **Instalar dependencias:**
   ```powershell
   composer install --optimize-autoloader --no-dev
   ```
   - Espera de 2-5 minutos hasta que termine

---

### Paso 3: Crear Base de Datos

1. **Abre PowerShell**
2. **Entra a MySQL:**
   ```powershell
   mysql -u root -p
   ```
3. **Crea la base de datos:**
   ```sql
   CREATE DATABASE almacenUtp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   EXIT;
   ```

---

### Paso 4: Configurar .env

1. **Copiar archivo de ejemplo:**
   - En `C:\inetpub\wwwroot\sistema_almacen_utp`
   - Busca `.env.example` y cópialo como `.env`

2. **Editar .env:**
   ```env
   APP_NAME="Sistema Almacen UTP"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=http://tu-dominio.com
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=almacenUtp
   DB_USERNAME=root
   DB_PASSWORD=TU_PASSWORD_MYSQL
   ```

---

### Paso 5: Generar Llave y Ejecutar Migraciones

En PowerShell (dentro del proyecto):

```powershell
# Generar llave de aplicación
php artisan key:generate

# Ejecutar migraciones y seeders
php artisan migrate --force
php artisan db:seed --force

# Cachear configuración (producción)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### Paso 6: Crear Sitio Web en IIS

1. **Abrir Administrador de IIS**

2. **Crear nuevo sitio:**
   - Clic derecho en **Sitios** → **Agregar sitio web**
   - Configura:
     ```
     Nombre del sitio: Sistema Almacen UTP API
     Grupo de aplicaciones: Sistema Almacen UTP API (se crea automático)
     Ruta de acceso física: C:\inetpub\wwwroot\sistema_almacen_utp\public
     ```
     ⚠️ **MUY IMPORTANTE:** La ruta debe apuntar a la carpeta **public**, NO a la raíz del proyecto
   
   - Enlace:
     ```
     Tipo: http
     Dirección IP: Todas las no asignadas
     Puerto: 8000 (o el que prefieras, como 80 si no está ocupado)
     Nombre de host: (dejar vacío o api.tudominio.com)
     ```
   
   - Haz clic en **Aceptar**

3. **Configurar el grupo de aplicaciones:**
   - En el panel izquierdo, haz clic en **Grupos de aplicaciones**
   - Busca **Sistema Almacen UTP API** y haz doble clic
   - Configura:
     ```
     Versión de .NET CLR: Sin código administrado
     Modo de canalización: Integrado
     ```
   - Haz clic en **Aceptar**

---

### Paso 7: Configurar Permisos de Carpetas

Laravel necesita permisos de escritura en storage y bootstrap/cache.

```powershell
# PowerShell como Administrador
cd C:\inetpub\wwwroot\sistema_almacen_utp

# Dar permisos a IIS_IUSRS
icacls storage /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls bootstrap\cache /grant "IIS_IUSRS:(OI)(CI)F" /T

# Dar permisos al grupo de aplicaciones
icacls storage /grant "IIS APPPOOL\Sistema Almacen UTP API:(OI)(CI)F" /T
icacls bootstrap\cache /grant "IIS APPPOOL\Sistema Almacen UTP API:(OI)(CI)F" /T
```

**Verificar permisos:**
- Clic derecho en carpeta `storage` → **Propiedades** → **Seguridad**
- Debes ver `IIS_IUSRS` y el grupo de aplicaciones con Control total ✅

---

### Paso 8: Configurar web.config

Laravel en IIS requiere un archivo `web.config` en la carpeta `public`.

1. **Crear archivo:**
   - En `C:\inetpub\wwwroot\sistema_almacen_utp\public\`
   - Crea un archivo `web.config`

2. **Contenido del archivo:**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="Imported Rule 1" stopProcessing="true">
                    <match url="^(.*)/$" ignoreCase="false" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                    </conditions>
                    <action type="Redirect" redirectType="Permanent" url="/{R:1}" />
                </rule>
                <rule name="Imported Rule 2" stopProcessing="true">
                    <match url="^" ignoreCase="false" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php" />
                </rule>
            </rules>
        </rewrite>
        <httpErrors errorMode="DetailedLocalOnly" />
        <directoryBrowse enabled="false" />
    </system.webServer>
</configuration>
```

---

### Paso 9: Probar el Backend

1. **Reiniciar IIS:**
   ```powershell
   iisreset
   ```

2. **Probar en el navegador:**
   - Ve a: http://localhost:8000
   - Debes ver la página de Laravel (o mensaje JSON) ✅

3. **Probar API de login:**
   - Ve a: http://localhost:8000/api/login (verás un error - es normal)
   - Usa Postman o Thunder Client:
     ```
     POST http://localhost:8000/api/login
     Content-Type: application/json
     
     {
       "email": "admin@almacen.com",
       "password": "Admin123"
     }
     ```
   - Debes recibir un token JWT ✅

---

## 🎨 4. Configuración del Frontend Vue

### Paso 1: Instalar Node.js

1. **Descargar Node.js:**
   - Ve a: https://nodejs.org/
   - Descarga la versión **LTS** (Long Term Support)

2. **Instalar Node.js:**
   - Ejecuta el instalador
   - Deja todas las opciones por defecto
   - Completa la instalación

3. **Verificar:**
   ```powershell
   node -v
   npm -v
   ```

---

### Paso 2: Configurar el Proyecto Frontend

1. **Copiar proyecto frontend:**
   - Copia tu proyecto Vue a `C:\inetpub\wwwroot\frontend-almacen-utp`
   - (O el nombre que tenga tu proyecto frontend)

2. **Instalar dependencias:**
   ```powershell
   cd C:\inetpub\wwwroot\frontend-almacen-utp
   npm install
   ```

3. **Configurar URL de la API:**
   - En tu proyecto Vue, busca el archivo de configuración (ej: `.env`, `config.js`, constantes, etc.)
   - Actualiza la URL de la API:
     ```
     VITE_API_URL=http://localhost:8000/api
     # O tu dominio de producción:
     VITE_API_URL=http://api.tudominio.com/api
     ```

4. **Construir para producción:**
   ```powershell
   npm run build
   ```
   - Esto generará una carpeta `dist/` con los archivos compilados

---

### Paso 3: Crear Sitio Frontend en IIS

1. **Abrir Administrador de IIS**

2. **Crear nuevo sitio:**
   - Clic derecho en **Sitios** → **Agregar sitio web**
   - Configura:
     ```
     Nombre del sitio: Sistema Almacen UTP Frontend
     Grupo de aplicaciones: Sistema Almacen UTP Frontend
     Ruta de acceso física: C:\inetpub\wwwroot\frontend-almacen-utp\dist
     ```
     ⚠️ **IMPORTANTE:** Apunta a la carpeta **dist** (archivos compilados)
   
   - Enlace:
     ```
     Tipo: http
     Dirección IP: Todas las no asignadas
     Puerto: 80 (o 5173, o el que prefieras)
     Nombre de host: (dejar vacío o tudominio.com)
     ```

3. **Configurar web.config para Vue Router:**
   - En `C:\inetpub\wwwroot\frontend-almacen-utp\dist\`
   - Crea un archivo `web.config`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="Vue Router" stopProcessing="true">
                    <match url=".*" />
                    <conditions logicalGrouping="MatchAll">
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="/" />
                </rule>
            </rules>
        </rewrite>
        <staticContent>
            <mimeMap fileExtension=".json" mimeType="application/json" />
        </staticContent>
    </system.webServer>
</configuration>
```

4. **Reiniciar IIS:**
   ```powershell
   iisreset
   ```

5. **Probar el frontend:**
   - Ve a: http://localhost (o el puerto configurado)
   - Debes ver tu aplicación Vue ✅
   - Intenta hacer login para verificar la conexión con el backend

---

## 🔐 Configurar CORS

Si el frontend y backend están en dominios/puertos diferentes, necesitas configurar CORS en Laravel.

1. **Editar `config/cors.php`:**
   ```php
   'allowed_origins' => [
       'http://localhost',
       'http://localhost:80',
       'http://localhost:5173',
       'http://tudominio.com',
       // Agrega las URL de tu frontend aquí
   ],
   ```

2. **Limpiar caché:**
   ```powershell
   cd C:\inetpub\wwwroot\sistema_almacen_utp
   php artisan config:clear
   php artisan config:cache
   ```

3. **Reiniciar IIS:**
   ```powershell
   iisreset
   ```

---

## 🐛 Solución de Problemas

### Problema 1: HTTP Error 403 - Forbidden

**Causas:**
- Permisos incorrectos
- Sitio apunta a carpeta incorrecta

**Soluciones:**

1. Verificar que el sitio apunte a `/public` (backend) o `/dist` (frontend)
2. Configurar permisos (ver Paso 7 del Backend)
3. Desactivar examen de directorios:
   - IIS Manager → tu sitio → **Examen de directorios** → Deshabilitar

---

### Problema 2: HTTP Error 500 - Internal Server Error

**Soluciones:**

1. **Habilitar errores detallados temporalmente:**
   - En `.env` del backend:
     ```
     APP_DEBUG=true
     APP_ENV=local
     ```
   - Recarga la página para ver el error exacto
   - ⚠️ Después de resolver, volver a `APP_DEBUG=false`

2. **Limpiar caché de Laravel:**
   ```powershell
   cd C:\inetpub\wwwroot\sistema_almacen_utp
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```

3. **Verificar logs:**
   - Laravel: `C:\inetpub\wwwroot\sistema_almacen_utp\storage\logs\laravel.log`
   - IIS: `C:\inetpub\logs\LogFiles\W3SVC1\`

---

### Problema 3: Las rutas no funcionan (404 en /api/productos)

**Causas:**
- URL Rewrite Module no instalado
- web.config faltante o incorrecto

**Soluciones:**

1. Verificar URL Rewrite instalado (IIS Manager → icono Reescritura de direcciones URL)
2. Verificar `web.config` existe en `/public`
3. Probar con: `http://localhost:8000/index.php/api/productos`
   - Si funciona con `index.php` → problema de URL Rewrite

---

### Problema 4: No se puede conectar a MySQL

**Soluciones:**

1. **Verificar MySQL ejecutándose:**
   ```powershell
   # Abrir servicios
   services.msc
   # Buscar MySQL80 → debe estar "En ejecución"
   ```

2. **Verificar extensiones PHP:**
   - Abrir `C:\PHP\php.ini`
   - Verificar estas líneas sin `;`:
     ```ini
     extension=mysqli
     extension=pdo_mysql
     ```
   - Reiniciar IIS: `iisreset`

3. **Probar conexión:**
   ```powershell
   cd C:\inetpub\wwwroot\sistema_almacen_utp
   php artisan tinker
   DB::connection()->getPdo();
   ```

---

### Problema 5: Error CORS desde el frontend

**Solución:**

1. Editar `config/cors.php` en el backend
2. Agregar la URL del frontend en `allowed_origins`
3. Limpiar caché:
   ```powershell
   php artisan config:clear
   php artisan config:cache
   ```
4. Reiniciar IIS: `iisreset`

---

### Problema 6: Frontend no carga estilos (CSS/JS)

**Soluciones:**

1. **Verificar MIME types:**
   - IIS Manager → sitio frontend → **Tipos MIME**
   - Debe tener: `.css` → `text/css`, `.js` → `application/javascript`

2. **Verificar contenido estático habilitado:**
   - IIS Manager → sitio → **Asignación de controladores**
   - `StaticFile` debe estar habilitado

3. **Verificar ruta de assets en Vue:**
   - En `vite.config.js` o configuración de build
   - Base path debe ser `/` o la ruta correcta

---

### Problema 7: FastCGI timeout (Error 500 después de 30 segundos)

**Solución:**

```powershell
# Aumentar timeout de FastCGI
# IIS Manager → Servidor → Configuración de FastCGI
# php-cgi.exe → Editar → Tiempo de espera de actividad: 300
```

---

## 📅 Uso Diario

### Ventajas de IIS (funciona 24/7)

✅ **IIS se ejecuta automáticamente como servicio de Windows**
✅ **NO necesitas "encenderlo" cada día**
✅ **El sistema está disponible siempre** (24/7)
✅ **Se inicia automáticamente con Windows**

### Verificar que IIS esté ejecutándose:

```powershell
# Abrir servicios
services.msc

# Buscar:
# - "Servicio de publicación World Wide Web" → debe estar En ejecución
# - "MySQL80" → debe estar En ejecución
```

### Reiniciar IIS (solo si es necesario):

```powershell
# PowerShell como Administrador
iisreset
```

### Limpiar caché de Laravel (después de hacer cambios):

```powershell
cd C:\inetpub\wwwroot\sistema_almacen_utp

# Limpiar caché
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Reconstruir caché (producción)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Actualizar Frontend (después de cambios):

```powershell
cd C:\inetpub\wwwroot\frontend-almacen-utp

# Reconstruir
npm run build

# Los cambios se aplicarán automáticamente
# No es necesario reiniciar IIS
```

---

## 🔒 Configurar Firewall (Acceso Remoto)

Si quieres acceder al sistema desde otros equipos:

```powershell
# PowerShell como Administrador

# Abrir puerto del backend (8000)
New-NetFirewallRule -DisplayName "IIS Sistema Almacen API" -Direction Inbound -Protocol TCP -LocalPort 8000 -Action Allow

# Abrir puerto del frontend (80)
New-NetFirewallRule -DisplayName "IIS Sistema Almacen Frontend" -Direction Inbound -Protocol TCP -LocalPort 80 -Action Allow

# Obtener IP del servidor
ipconfig
# Anota la Dirección IPv4
```

**Acceder desde otro equipo:**
- Backend: `http://IP_DEL_SERVIDOR:8000/api`
- Frontend: `http://IP_DEL_SERVIDOR`

---

## 🔄 Actualizaciones Futuras

### Actualizar Backend:

```powershell
cd C:\inetpub\wwwroot\sistema_almacen_utp

# Si usas Git:
git pull origin main

# Actualizar dependencias
composer install --optimize-autoloader --no-dev

# Aplicar migraciones nuevas
php artisan migrate --force

# Limpiar y reconstruir caché
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Actualizar Frontend:

```powershell
cd C:\inetpub\wwwroot\frontend-almacen-utp

# Si usas Git:
git pull origin main

# Actualizar dependencias
npm install

# Reconstruir
npm run build
```

---

## ✅ Checklist de Instalación

### IIS y PHP:
- [ ] IIS instalado y habilitado
- [ ] PHP 8.2 NTS instalado en `C:\PHP`
- [ ] PHP agregado al PATH
- [ ] FastCGI configurado en IIS
- [ ] URL Rewrite Module instalado
- [ ] Composer instalado (`composer --version` funciona)

### MySQL:
- [ ] MySQL 8.0.x instalado y ejecutándose como servicio
- [ ] Versión verificada (`mysql --version` muestra 8.0.x)
- [ ] Contraseña root configurada y anotada
- [ ] Base de datos `almacenUtp` creada

### Backend Laravel:
- [ ] Proyecto copiado a `C:\inetpub\wwwroot\sistema_almacen_utp`
- [ ] Dependencias instaladas (`composer install`)
- [ ] Archivo `.env` configurado
- [ ] Llave generada (`php artisan key:generate`)
- [ ] Migraciones ejecutadas (`php artisan migrate`)
- [ ] Sitio IIS creado apuntando a `/public`
- [ ] Permisos configurados (IIS_IUSRS)
- [ ] Archivo `web.config` creado en `/public`
- [ ] API funciona (login exitoso)

### Frontend Vue:
- [ ] Node.js instalado
- [ ] Proyecto frontend copiado
- [ ] Dependencias instaladas (`npm install`)
- [ ] URL de API configurada
- [ ] Build de producción generado (`npm run build`)
- [ ] Sitio IIS creado apuntando a `/dist`
- [ ] Archivo `web.config` creado en `/dist`
- [ ] Frontend carga correctamente
- [ ] Login funcional con el backend

### CORS y Conectividad:
- [ ] CORS configurado en `config/cors.php`
- [ ] Frontend y backend se comunican correctamente
- [ ] Puedes hacer login desde el frontend

---

## 🎉 ¡Instalación Completa!

Tu sistema está configurado y listo para usar 24/7.

**Usuarios por defecto:**
- Email: `admin@almacen.com`
- Contraseña: `Admin123`

**URLs del sistema:**
- Backend API: http://localhost:8000/api
- Frontend: http://localhost

**Próximos pasos:**
1. Cambia la contraseña del administrador
2. Crea usuarios para tu equipo
3. Configura las secciones y tipos de stock
4. Comienza a registrar productos

**¡Gracias por usar el Sistema de Almacén UTP!** 🚀
