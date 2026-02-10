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

Este sistema está dividado en dos proyectos independientes:

- **Backend (API)**: Laravel (este proyecto) - Sirve la API REST
- **Frontend (UI)**: Vue.js (proyecto separado) - Interfaz de usuario

Ambos se ejecutan en IIS:
- **Backend**: http://localhost:8000 (o tu dominio/api)
- **Frontend**: http://localhost:5173 (o tu dominio)

**IIS (Internet Information Services)** es el servidor web profesional de Microsoft, ideal para:
- ✅ Servidores de producción
- ✅ Windows Server 2019/2022
- ✅ Windows 11 Pro/Enterprise
- ✅ Ambientes corporativos con alta disponibilidad
- ✅ Instalaciones permanentes 24/7

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
- MySQL 8.0 o superior
- URL Rewrite Module para IIS
- Node.js (para el frontend Vue)

---

## 🔧 1. Instalación de IIS y PHP

#### Para Windows 11 Pro/Enterprise:

1. Presiona **Windows + R**, escribe `appwiz.cpl` y presiona Enter
2. Haz clic en **Activar o desactivar las características de Windows**
3. Marca las siguientes casillas:
   - ✅ **Internet Information Services**
     - ✅ Herramientas de administración web
     - ✅ Servicios World Wide Web
       - ✅ Características de desarrollo de aplicaciones
         - ✅ **CGI**
         - ✅ **Extensibilidad ISAPI**
         - ✅ **Filtros ISAPI**
       - ✅ Características HTTP comunes (todas)
     - ✅ Herramientas de administración web
       - ✅ **Consola de administración de IIS**
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
   - **IMPORTANTE:** Debe ser **Non Thread Safe** para FastCGI

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

---

## 🗄️ 2. Instalación de MySQL
   - Ve a: https://dev.mysql.com/downloads/installer/
   - Descarga **MySQL Installer for Windows**
   - Elige la versión **Full** (aproximadamente 500 MB)

2. **Instalar MySQL:**
   - Ejecuta el instalador
   - Selecciona **Server only** o **Developer Default**
   - Haz clic en **Execute** para descargar e instalar
   - Espera a que termine (5-10 minutos)

3. **Configurar MySQL:**
   - Tipo de configuración: **Development Computer**
   - Puerto: **3306** (dejar por defecto)
   - Authentication Method: **Use Strong Password Encryption**
   - Root password: Elige una contraseña segura (anótala)
   - Haz clic en **Next** hasta finalizar
   - MySQL se instalará como servicio de Windows ✅

4. **Verificar MySQL:**
   - Abre PowerShell
   - Ejecuta:
     ```powershell
     mysql -u root -p
     ```
   - Ingresa la contraseña que configuraste
   - Si entras a la consola MySQL, ¡funciona! ✅
   - Escribe `exit` para salir

---

### Paso 5C: Instalar Composer

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

---

### Paso 6C: Instalar URL Rewrite Module (IMPORTANTE)

Laravel requiere URL rewriting para funcionar correctamente.

1. **Descargar URL Rewrite:**
   - Ve a: https://www.iis.net/downloads/microsoft/url-rewrite
   - Descarga **URL Rewrite Module 2.1**
   - O busca: "IIS URL Rewrite download"

2. **Instalar:**
   - Ejecuta el instalador `rewrite_amd64.msi`
   - Sigue el asistente hasta finalizar
   - **Reinicia IIS:**
     ```powershell
     iisreset
     ```

---

### Paso 7C: Configurar el Proyecto Laravel en IIS

1. **Copiar el proyecto:**
   - Copia la carpeta `sistema_almacen_utp` a `C:\inetpub\wwwroot\`
   - Ruta final: `C:\inetpub\wwwroot\sistema_almacen_utp`

2. **Instalar dependencias:**
   - Abre PowerShell como Administrador
   - Navega al proyecto:
     ```powershell
     cd C:\inetpub\wwwroot\sistema_almacen_utp
     ```
   - Instala dependencias:
     ```powershell
     composer install --optimize-autoloader --no-dev
     ```

3. **Crear base de datos:**
   - Abre PowerShell
   - Entra a MySQL:
     ```powershell
     mysql -u root -p
     ```
   - Crea la base de datos:
     ```sql
     CREATE DATABASE almacenUtp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
     EXIT;
     ```

4. **Configurar .env:**
   - Copia `.env.example` como `.env`
   - Edita `.env`:
     ```env
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

5. **Generar llave y ejecutar migraciones:**
   ```powershell
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

### Paso 8C: Crear Sitio Web en IIS

1. **Abrir Administrador de IIS**

2. **Crear nuevo sitio:**
   - Clic derecho en **Sitios** → **Agregar sitio web**
   - Configura:
     ```
     Nombre del sitio: Sistema Almacen UTP
     Grupo de aplicaciones: Sistema Almacen UTP (se crea automático)
     Ruta de acceso física: C:\inetpub\wwwroot\sistema_almacen_utp\public
     ```
     ⚠️ **IMPORTANTE:** La ruta debe apuntar a la carpeta **public**, no a la raíz
   
   - Enlace:
     ```
     Tipo: http
     Dirección IP: Todas las no asignadas
     Puerto: 8000 (o el que prefieras, ej: 80)
     Nombre de host: (dejar vacío o tu dominio)
     ```
   
   - Haz clic en **Aceptar**

3. **Configurar el grupo de aplicaciones:**
   - En el panel izquierdo, haz clic en **Grupos de aplicaciones**
   - Busca **Sistema Almacen UTP** y haz doble clic
   - Configura:
     ```
     Versión de .NET CLR: Sin código administrado
     Modo de canalización: Integrado
     ```
   - Haz clic en **Aceptar**

---

### Paso 9C: Configurar Permisos de Carpetas

Laravel necesita permisos de escritura en ciertas carpetas.

1. **Dar permisos a storage y bootstrap/cache:**
   - Abre PowerShell como Administrador
   - Ejecuta:
     ```powershell
     # Navegar al proyecto
     cd C:\inetpub\wwwroot\sistema_almacen_utp
     
     # Dar permisos a IIS_IUSRS
     icacls storage /grant "IIS_IUSRS:(OI)(CI)F" /T
     icacls bootstrap\cache /grant "IIS_IUSRS:(OI)(CI)F" /T
     
     # Dar permisos al grupo de aplicaciones
     icacls storage /grant "IIS APPPOOL\Sistema Almacen UTP:(OI)(CI)F" /T
     icacls bootstrap\cache /grant "IIS APPPOOL\Sistema Almacen UTP:(OI)(CI)F" /T
     ```

2. **Verificar permisos:**
   - Clic derecho en carpeta `storage` → **Propiedades** → **Seguridad**
   - Debes ver `IIS_IUSRS` y el grupo de aplicaciones con Control total ✅

---

### Paso 10C: Configurar web.config

Laravel en IIS requiere un archivo `web.config` en la carpeta `public`.

1. **Crear web.config:**
   - En `C:\inetpub\wwwroot\sistema_almacen_utp\public\`
   - Crea un archivo llamado `web.config`
   - Pega este contenido:

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

2. **Guardar el archivo**

---

### Paso 11C: Probar el Sitio

1. **Reiniciar IIS:**
   ```powershell
   iisreset
   ```

2. **Probar en el navegador:**
   - Ve a: http://localhost:8000 (o el puerto que configuraste)
   - Debes ver la página de inicio de Laravel ✅

3. **Probar API:**
   - Ve a: http://localhost:8000/api/login
   - Usa Postman o navegador para hacer login:
     ```json
     POST http://localhost:8000/api/login
     {
       "email": "admin@almacen.com",
       "password": "Admin123"
     }
     ```

---

### Paso 12C: Configurar Firewall (Para acceso remoto)

Si quieres que otros equipos accedan al servidor:

1. **Abrir puerto en Firewall:**
   ```powershell
   # PowerShell como Administrador
   New-NetFirewallRule -DisplayName "IIS Sistema Almacen" -Direction Inbound -Protocol TCP -LocalPort 8000 -Action Allow
   ```

2. **Obtener IP del servidor:**
   ```powershell
   ipconfig
   ```
   - Anota la **Dirección IPv4**

3. **Acceder desde otro equipo:**
   - En el frontend, cambia la URL de la API:
   ```javascript
   const API_URL = 'http://IP_DEL_SERVIDOR:8000/api'
   ```

---

### 🔐 Seguridad Adicional para IIS (Recomendado)

1. **Deshabilitar información de versión:**
   - En IIS Manager → Servidor → **Encabezados de respuesta HTTP**
   - Quita el encabezado `X-Powered-By`

2. **Habilitar HTTPS:**
   - Instala un certificado SSL (Let's Encrypt, DigiCert, etc.)
   - Configura el enlace HTTPS en el sitio (puerto 443)
   - Fuerza HTTPS en Laravel editando `.env`:
     ```env
     APP_URL=https://tu-dominio.com
     ```

3. **Limitar tamaño de solicitudes:**
   - En IIS Manager → Sitio → **Filtrado de solicitudes**
   - Configura límites según tus necesidades

---

### ✅ Ventajas de IIS en Producción

- ✅ Muy estable, no se cierra automáticamente
- ✅ Integrado con Windows Server
- ✅ Mejor rendimiento en producción
- ✅ Herramientas de monitoreo avanzadas
- ✅ Logs detallados en Event Viewer
- ✅ Gestión centralizada con IIS Manager

---

## Después de instalar XAMPP, Laragon o IIS:

### Paso 2: Instalar Composer (Solo si usas XAMPP)

**¿Qué es Composer?** Es el gestor de paquetes de Laravel que descarga todas las librerías necesarias.

1. **Descargar Composer:**
   - Ve a: https://getcomposer.org/download/
   - Descarga `Composer-Setup.exe` para Windows

2. **Instalar Composer:**
   - Ejecuta el instalador
   - En "PHP Installation", debe detectar automáticamente: `C:\xampp\php\php.exe`
   - Si NO lo detecta, haz clic en **Browse** y navega a `C:\xampp\php\php.exe`
   - Deja marcado "Add to PATH" ✅
   - Haz clic en **Next** hasta finalizar
   - **Reinicia tu computadora** para que los cambios surtan efecto

3. **Verificar la instalación:**
   - Abre **PowerShell** (clic derecho en menú inicio → PowerShell)
   - Escribe: `composer --version`
   - Debes ver algo como: `Composer version 2.x.x`
   - Si sale un error, reinicia la computadora e intenta de nuevo

---

### Paso 3: Instalar Git (Opcional pero recomendado)

**¿Qué es Git?** Es un sistema de control de versiones para descargar y actualizar el proyecto.

1. **Descargar Git:**
   - Ve a: https://git-scm.com/download/win
   - Descarga la versión de 64 bits

2. **Instalar Git:**
   - Ejecuta el instalador
   - Deja todas las opciones por defecto
   - Haz clic en **Next** hasta finalizar

3. **Verificar la instalación:**
   - Abre PowerShell
   - Escribe: `git --version`
   - Debes ver algo como: `git version 2.x.x`

---

### Paso 4: Visual Studio Code (Editor de código - Opcional)

Si necesitas editar configuraciones del proyecto:

1. Descarga desde: https://code.visualstudio.com/
2. Instala con las opciones por defecto
3. Abre VSCode y instala las extensiones:
   - PHP Intelephense
   - Laravel Extension Pack

---

## 🚀 Configuración del Proyecto

### Paso 5: Copiar los Archivos del Proyecto

**⚠️ NOTA:** Si estás usando **IIS**, sigue las instrucciones de la **Opción C** completa arriba. Esta sección es solo para XAMPP y Laragon.

**Ubicación según tu entorno:**
- Si usas **XAMPP**: `C:\xampp\htdocs\`
- Si usas **Laragon**: `C:\laragon\www\`
- Si usas **IIS**: `C:\inetpub\wwwroot\` (ver Opción C para detalles)

**Opción A: Si tienes los archivos en una carpeta**
1. Copia toda la carpeta `sistema_almacen_utp` 
2. Pégala en la ubicación según tu entorno:
   - XAMPP: `C:\xampp\htdocs\sistema_almacen_utp`
   - Laragon: `C:\laragon\www\sistema_almacen_utp`
   - IIS: `C:\inetpub\wwwroot\sistema_almacen_utp`

**Opción B: Si usas Git**
1. Abre PowerShell (o Terminal en Laragon: clic derecho → Terminal)
2. Navega a la carpeta correcta:
   ```powershell
   # Para XAMPP:
   cd C:\xampp\htdocs
   
   # Para Laragon:
   cd C:\laragon\www
   
   # Para IIS:
   cd C:\inetpub\wwwroot
   ```
3. Clona el repositorio:
   ```powershell
   git clone [URL_DEL_REPOSITORIO] sistema_almacen_utp
   ```

**📁 Ruta final del proyecto:**
- XAMPP: `C:\xampp\htdocs\sistema_almacen_utp`
- Laragon: `C:\laragon\www\sistema_almacen_utp`
- IIS: `C:\inetpub\wwwroot\sistema_almacen_utp`

---

### Paso 6: Instalar Dependencias de Laravel

1. **Abrir Terminal/PowerShell en la carpeta del proyecto:**

   **Opción A - Desde Windows Explorer (funciona para ambos):**
   - Abre el explorador de archivos
   - Navega a la carpeta del proyecto:
     - XAMPP: `C:\xampp\htdocs\sistema_almacen_utp`
     - Laragon: `C:\laragon\www\sistema_almacen_utp`
   - Mantén presionada la tecla **Shift**
   - Haz clic derecho en un espacio vacío
   - Selecciona **"Abrir ventana de PowerShell aquí"**

   **Opción B - Desde Laragon (solo Laragon):**
   - Abre Laragon
   - Haz clic derecho en el proyecto `sistema_almacen_utp`
   - Selecciona **Terminal**
   - Se abre automáticamente en la carpeta correcta ✅

2. **Instalar dependencias:**
   ```powershell
   composer install
   ```
   - Este proceso puede tardar 2-5 minutos
   - Verás muchas líneas de texto descargando paquetes
   - Al final debe decir algo como "Generating optimized autoload files"

3. **Si sale error de memoria:**
   ```powershell
   composer install --no-scripts
   php artisan optimize
   ```

---

### Paso 7: Crear la Base de Datos

1. **Abrir phpMyAdmin:**
   - Abre tu navegador
   - Ve a: http://localhost/phpmyadmin
   - Usuario: `root`
   - Contraseña: *(dejar vacío)*

2. **Crear base de datos:**
   - Haz clic en **"Nueva"** en el panel izquierdo
   - Nombre de la base de datos: `almacenUtp`
   - Cotejamiento: `utf8mb4_unicode_ci`
   - Haz clic en **Crear**

---

### Paso 8: Configurar el Archivo .env

El archivo `.env` contiene la configuración de conexión a la base de datos.

1. **Copiar archivo de ejemplo:**
   - En la carpeta del proyecto (`C:\xampp\htdocs\sistema_almacen_utp`)
   - Busca el archivo `.env.example`
   - Cópialo y renómbralo como `.env`
   
   **Si no se ven archivos que empiezan con punto:**
   - En el explorador de archivos, ve a **Ver** → ✅ **Elementos ocultos**

2. **Editar el archivo .env:**
   - Abre el archivo `.env` con el Bloc de notas o VSCode
   - Busca las líneas que dicen:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=laravel
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   
   - Cámbiala a:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=almacenUtp
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   
   - **IMPORTANTE:** `DB_PASSWORD=` debe estar VACÍO (sin nada después del `=`)
   - Guarda el archivo (Ctrl + S)

3. **Generar llave de aplicación:**
   - En PowerShell (dentro de la carpeta del proyecto):
   ```powershell
   php artisan key:generate
   ```
   - Debe aparecer: "Application key set successfully"

---

### Paso 9: Crear las Tablas de la Base de Datos

Este paso crea todas las tablas e inserta los datos iniciales.

1. **En PowerShell, ejecuta:**
   ```powershell
   php artisan migrate:fresh --seed
   ```

2. **¿Qué hace este comando?**
   - Crea todas las tablas (usuarios, productos, secciones, etc.)
   - Inserta datos de ejemplo:
     - Usuario administrador
     - Roles y permisos
     - Tipos de stock
     - Secciones
     - Áreas
     - Depósitos (10 depósitos de ejemplo)
     - Productos de prueba

3. **Si todo sale bien, verás:**
   ```
   INFO  Seeding database.
   Database\Seeders\RoleSeeder ........................ RUNNING
   Database\Seeders\RoleSeeder ........................ DONE
   ... (más líneas)
   ```

---

### Paso 10: Configurar CORS (Conexión con Frontend)

Si tienes el frontend en otra carpeta o servidor:

1. Abre el archivo `config/cors.php`
2. Busca la línea `'allowed_origins' =>`
3. Agrega la URL de tu frontend:
   ```php
   'allowed_origins' => [
       'http://localhost:5173',
       'http://127.0.0.1:5173',
       'http://10.243.20.22:5173',  // IP del servidor frontend
   ],
   ```
4. Guarda el archivo

---

## 🎯 Primera Ejecución

### Paso 11: Iniciar el Servidor Laravel

1. **Asegúrate de que XAMPP esté ejecutando MySQL:**
   - Abre el Panel de Control de XAMPP
   - MySQL debe tener fondo VERDE
   - Si no, haz clic en **Start** junto a MySQL

2. **Iniciar servidor Laravel:**
   - En PowerShell (carpeta del proyecto):
   ```powershell
   php artisan serve
   ```

3. **Verás algo como:**
   ```
   INFO  Server running on [http://127.0.0.1:8000].
   Press Ctrl+C to stop the server.
   ```

4. **Probar la API:**
   - Abre tu navegador
   - Ve a: http://127.0.0.1:8000
   - Debes ver la página de Laravel ✅

---

### Paso 12: Prueba de Login

**Datos de acceso inicial:**
- Email: `admin@almacen.com`
- Contraseña: `Admin123`

**Probar con Postman o Thunder Client:**
```
POST http://127.0.0.1:8000/api/login
Content-Type: application/json

{
  "email": "admin@almacen.com",
  "password": "Admin123"
}
```

**Respuesta esperada:**
```json
{
  "status": "success",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "nombre": "Administrador",
    "email": "admin@almacen.com"
  }
}
```

---

### Paso 13: Conectar con el Frontend

1. **Asegúrate de que el backend esté corriendo:**
   ```powershell
   php artisan serve
   ```
   - Debe estar en http://127.0.0.1:8000

2. **Iniciar el frontend (si está en Vite/React):**
   - Abre otra ventana de PowerShell
   - Navega a la carpeta del frontend
   - Ejecuta:
   ```powershell
   npm run dev
   ```

3. **Verificar conexión:**
   - El frontend debe cargar en http://localhost:5173
   - Intenta hacer login
   - Si funciona, ¡todo está conectado! 🎉

---

## 🐛 Solución de Problemas Comunes

### Problema 1: "composer: comando no reconocido"
**Solución:**
- Reinicia tu computadora
- Abre PowerShell como **Administrador**
- Verifica que esté en el PATH:
  ```powershell
  echo $env:Path
  ```
- Debe contener la ruta de Composer (usualmente `C:\ProgramData\ComposerSetup\bin`)

---

### Problema 2: "php artisan: comando no reconocido"
**Solución:**
- Estás ejecutando el comando fuera de la carpeta del proyecto
- Navega a la carpeta correcta:
  ```powershell
  cd C:\xampp\htdocs\sistema_almacen_utp
  ```

---

### Problema 3: "MySQL no inicia en XAMPP"
**Causas comunes:**
- El puerto 3306 está ocupado por otro programa

**Solución:**
1. Abre el Panel de Control de XAMPP
2. Haz clic en **Config** junto a MySQL
3. Selecciona `my.ini`
4. Busca `port=3306` y cámbialo a `port=3307`
5. En el archivo `.env` del proyecto, cambia también:
   ```
   DB_PORT=3307
   ```
6. Reinicia MySQL en XAMPP

---

### Problema 4: "SQLSTATE[HY000] [2002] No se puede conectar"
**Solución:**
- MySQL no está ejecutándose
- Abre el Panel de Control de XAMPP
- Haz clic en **Start** junto a MySQL
- Debe ponerse en color VERDE

---

### Problema 5: "Error 500 al hacer login"
**Solución:**
1. Verifica que la base de datos tenga datos:
   - Ve a http://localhost/phpmyadmin
   - Selecciona la base de datos `almacenUtp`
   - La tabla `usuarios` debe tener al menos 1 registro

2. Si está vacía, ejecuta nuevamente:
   ```powershell
   php artisan migrate:fresh --seed
   ```

---

### Problema 6: "CORS Error desde el frontend"
**Solución:**
1. Abre `config/cors.php`
2. Agrega la URL del frontend en `allowed_origins`
3. Limpia la cache:
   ```powershell
   php artisan config:clear
   php artisan cache:clear
   ```

---

### Problema 7: "Port 8000 already in use"
**Solución:**
- Ya hay otro servidor corriendo en el puerto 8000
- Usa otro puerto:
  ```powershell
  php artisan serve --port=8080
  ```
- Ahora la API estará en http://127.0.0.1:8080

---

### Problema 8: "XAMPP se cierra automáticamente en ciertas horas"

Este es un problema muy común. Puede tener varias causas:

#### 🔴 Causa 1: Antivirus bloqueando Apache/MySQL

**Solución:**
1. Abre tu antivirus (Windows Defender, Avast, etc.)
2. Agrega excepciones para estas carpetas:
   - `C:\xampp\apache\bin\httpd.exe`
   - `C:\xampp\mysql\bin\mysqld.exe`
   - `C:\xampp\htdocs\`

**Para Windows Defender:**
1. Abre **Configuración de Windows** (tecla Windows + I)
2. Ve a **Privacidad y seguridad** → **Seguridad de Windows**
3. Haz clic en **Protección antivirus y contra amenazas**
4. En "Configuración de protección antivirus y contra amenazas", haz clic en **Administrar configuración**
5. Desplázate hasta **Exclusiones** → **Agregar o quitar exclusiones**
6. Haz clic en **Agregar una exclusión** → **Carpeta**
7. Selecciona `C:\xampp`

---

#### 🔴 Causa 2: Configuración de energía de Windows

Windows puede estar apagando servicios para ahorrar energía.

**Solución:**
1. Abre **Panel de control**
2. Ve a **Hardware y sonido** → **Opciones de energía**
3. Haz clic en **Cambiar la configuración del plan** del plan activo
4. Haz clic en **Cambiar la configuración avanzada de energía**
5. Expande **Configuración de PCI Express** → **Administración de energía del estado de vínculo**
6. Cambia ambos (Con batería y Enchufado) a **Desactivado**
7. Haz clic en **Aplicar** y **Aceptar**

**Cambiar plan de energía a Alto rendimiento:**
1. En **Opciones de energía**, selecciona **Alto rendimiento**
2. Si no aparece, haz clic en **Mostrar planes adicionales**

---

#### 🔴 Causa 3: MySQL timeout por inactividad

MySQL puede cerrarse por timeout de conexión.

**Solución:**
1. Abre el Panel de Control de XAMPP
2. Haz clic en **Config** junto a MySQL → `my.ini`
3. Busca estas líneas (usa Ctrl+F):
   ```
   wait_timeout=28800
   interactive_timeout=28800
   ```
4. Si no existen, agrégalas al final del archivo en la sección `[mysqld]`:
   ```ini
   [mysqld]
   wait_timeout=86400
   interactive_timeout=86400
   max_allowed_packet=64M
   ```
5. Guarda el archivo
6. En XAMPP, detén MySQL (Stop)
7. Inícialo nuevamente (Start)

---

#### 🔴 Causa 4: Firewall bloqueando conexiones

**Solución:**
1. Abre **Panel de control** → **Sistema y seguridad** → **Firewall de Windows Defender**
2. Haz clic en **Permitir una aplicación o característica a través de Firewall de Windows Defender**
3. Haz clic en **Cambiar la configuración**
4. Haz clic en **Permitir otra aplicación...**
5. Busca y agrega:
   - `C:\xampp\apache\bin\httpd.exe`
   - `C:\xampp\mysql\bin\mysqld.exe`
6. Marca las casillas **Privada** y **Pública**
7. Haz clic en **Aceptar**

---

#### 🔴 Causa 5: Otro software usando el puerto 3306 o 80

**Solución - Cambiar puerto de MySQL:**
1. Para al servicio MySQL en XAMPP
2. Haz clic en **Config** junto a MySQL → `my.ini`
3. Busca `port=3306`
4. Cámbialo a `port=3307`
5. Guarda y reinicia MySQL
6. En tu proyecto, edita el archivo `.env`:
   ```
   DB_PORT=3307
   ```
7. Reinicia el servidor Laravel:
   ```powershell
   php artisan config:clear
   php artisan serve
   ```

**Solución - Cambiar puerto de Apache:**
1. Para Apache en XAMPP
2. Haz clic en **Config** junto a Apache → `httpd.conf`
3. Busca `Listen 80` (Ctrl+F)
4. Cámbialo a `Listen 8080`
5. Busca `ServerName localhost:80`
6. Cámbialo a `ServerName localhost:8080`
7. Guarda y reinicia Apache
8. Accede a phpMyAdmin en: http://localhost:8080/phpmyadmin

---

#### 🔴 Causa 6: Instalar como Servicio de Windows

Instalar XAMPP como servicio hace que inicie automáticamente con Windows y sea más estable.

**Solución:**
1. Abre el Panel de Control de XAMPP **como Administrador**
   - Clic derecho en el icono de XAMPP Control
   - Selecciona **Ejecutar como administrador**

2. **Para Apache:**
   - Haz clic en la **X** roja a la izquierda de Apache
   - Se convertirá en un ✓ verde
   - Apache ahora es un servicio de Windows

3. **Para MySQL:**
   - Haz clic en la **X** roja a la izquierda de MySQL
   - Se convertirá en un ✓ verde
   - MySQL ahora es un servicio de Windows

4. **Verificar servicios:**
   - Presiona **Windows + R**
   - Escribe `services.msc` y presiona Enter
   - Busca **Apache2.4** y **MySQL**
   - Ambos deben estar en estado **En ejecución**
   - Tipo de inicio: **Automático**

**Para desinstalar los servicios:**
1. Abre XAMPP Control Panel como Administrador
2. Haz clic en el ✓ verde junto a Apache o MySQL
3. Se convertirá en X roja (servicio desinstalado)

---

#### 🔴 Causa 7: Windows Update reiniciando servicios

**Solución:**
1. Posponer actualizaciones durante horas laborales:
   - Ve a **Configuración** → **Windows Update**
   - Haz clic en **Opciones avanzadas**
   - En **Pausar actualizaciones**, selecciona hasta 5 semanas
   
2. Configurar horario activo:
   - En **Windows Update** → **Opciones avanzadas**
   - Haz clic en **Horario activo**
   - Ajusta las horas donde usas el sistema (ej: 8:00 AM - 6:00 PM)
   - Windows NO reiniciará durante estas horas

---

#### 🔴 Verificar qué está cerrando XAMPP

**Ver logs de Apache:**
1. Abre `C:\xampp\apache\logs\error.log`
2. Los últimos errores están al final
3. Busca mensajes justo antes de que se cierre

**Ver logs de MySQL:**
1. Abre `C:\xampp\mysql\data\mysql_error.log`
2. Revisa errores recientes

**Ver Visor de eventos de Windows:**
1. Presiona **Windows + X**
2. Selecciona **Visor de eventos**
3. Ve a **Registros de Windows** → **Sistema**
4. Busca errores relacionados con Apache o MySQL en la hora que se cerró

---

#### ✅ Solución Rápida Recomendada

Si quieres una solución rápida que funciona en el 90% de los casos:

```powershell
# 1. Abrir PowerShell como Administrador (clic derecho → Ejecutar como administrador)

# 2. Agregar excepciones al Firewall
netsh advfirewall firewall add rule name="XAMPP Apache" dir=in action=allow program="C:\xampp\apache\bin\httpd.exe" enable=yes
netsh advfirewall firewall add rule name="XAMPP MySQL" dir=in action=allow program="C:\xampp\mysql\bin\mysqld.exe" enable=yes

# 3. Deshabilitar hibernación (opcional)
powercfg -h off
```

Luego:
1. Abre XAMPP Control Panel como Administrador
2. Instala Apache y MySQL como servicios (haz clic en la X roja)
3. Agrega la carpeta `C:\xampp` a las exclusiones de Windows Defender

**Con estos 3 pasos, el problema debería resolverse.** ✅

---

## �️ Solución de Problemas con IIS

### Problema 1: "HTTP Error 403 - Forbidden"

**Causas comunes:**
- Permisos de carpeta incorrectos
- El sitio apunta a la carpeta raíz en lugar de `/public`

**Soluciones:**

1. **Verificar que el sitio IIS apunte a `/public`:**
   - Abre el Administrador de IIS
   - Selecciona tu sitio
   - Haz clic derecho → **Administrar sitio web** → **Opciones avanzadas**
   - Verifica **Ruta de acceso física**: debe terminar en `\public`
   - Correcto: `C:\inetpub\wwwroot\sistema_almacen_utp\public`
   - **Incorrecto**: `C:\inetpub\wwwroot\sistema_almacen_utp`

2. **Configurar permisos correctamente:**
   ```powershell
   # PowerShell como Administrador
   cd C:\inetpub\wwwroot\sistema_almacen_utp
   
   # Permisos para storage y bootstrap/cache
   icacls storage /grant "IIS_IUSRS:(OI)(CI)F" /T
   icacls bootstrap\cache /grant "IIS_IUSRS:(OI)(CI)F" /T
   icacls storage /grant "IIS APPPOOL\DefaultAppPool:(OI)(CI)F" /T
   icacls bootstrap\cache /grant "IIS APPPOOL\DefaultAppPool:(OI)(CI)F" /T
   ```

3. **Verificar lista de directorios:**
   - En IIS Manager → tu sitio → **Examen de directorios**
   - Debe estar **Deshabilitado** ❌

---

### Problema 2: "HTTP Error 500 - Internal Server Error"

**Causas:**
- Falta el archivo `web.config`
- Permisos insuficientes
- Error de PHP en la aplicación

**Soluciones:**

1. **Crear/verificar `web.config`:**
   - Abre `C:\inetpub\wwwroot\sistema_almacen_utp\public\web.config`
   - Debe contener las reglas de reescritura URL (ver Paso 10C de la instalación)
   - Si no existe, créalo con el contenido del manual

2. **Habilitar errores detallados temporalmente:**
   ```powershell
   # En el archivo .env, cambia a:
   APP_DEBUG=true
   APP_ENV=local
   ```
   - Recarga la página para ver el error exacto
   - **⚠️ IMPORTANTE**: Después de solucionar, vuelve a:
   ```
   APP_DEBUG=false
   APP_ENV=production
   ```

3. **Limpiar caché de Laravel:**
   ```powershell
   cd C:\inetpub\wwwroot\sistema_almacen_utp
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```

4. **Verificar logs:**
   - **Logs de Laravel**: `C:\inetpub\wwwroot\sistema_almacen_utp\storage\logs\laravel.log`
   - **Logs de IIS**: `C:\inetpub\logs\LogFiles\W3SVC1\`
   - **Visor de eventos**: Windows + X → Visor de eventos → Registros de aplicaciones y servicios → Microsoft → Windows → IIS-Configuration

---

### Problema 3: "Las rutas no funcionan (404 en /api/productos)"

**Causa:** URL Rewrite Module no está instalado o configurado

**Soluciones:**

1. **Verificar URL Rewrite Module instalado:**
   - Abre IIS Manager
   - Selecciona el servidor (nodo superior)
   - Busca el icono **Reescritura de direcciones URL**
   - Si NO existe → Instalar URL Rewrite Module:
     - Descarga: https://www.iis.net/downloads/microsoft/url-rewrite
     - Instala y reinicia IIS: `iisreset`

2. **Verificar `web.config` en la carpeta `/public`:**
   ```powershell
   # Verificar que existe
   Test-Path C:\inetpub\wwwroot\sistema_almacen_utp\public\web.config
   ```
   - Debe existir y contener las reglas de reescritura

3. **Probar URL manualmente:**
   - Prueba: `http://localhost/index.php/api/productos`
   - Si funciona CON `index.php` pero NO sin él → problema de URL Rewrite
   - Verifica el `web.config`

---

### Problema 4: "FastCGI timeout (Error 500 después de 30 segundos)"

**Causa:** Operaciones largas (migraciones, reportes) exceden el timeout de FastCGI

**Solución:**

1. **Aumentar timeout de FastCGI:**
   - Abre IIS Manager
   - Selecciona el servidor (nodo raíz)
   - Doble clic en **Configuración de FastCGI**
   - Selecciona `php-cgi.exe`
   - Haz clic en **Editar**
   - Aumenta **Tiempo de espera de actividad**: de `30` a `300` (5 minutos)
   - Aplica cambios

2. **Aumentar timeout en `web.config`:**
   ```xml
   <!-- Agregar dentro de <system.webServer> -->
   <aspNetCore requestTimeout="00:05:00" />
   ```

3. **Para migraciones largas:**
   ```powershell
   # Ejecutar desde línea de comandos en lugar de desde el navegador
   php artisan migrate:fresh --seed
   ```

---

### Problema 5: "No se puede conectar a MySQL"

**Causas:**
- MySQL no está corriendo
- Credenciales incorrectas en `.env`
- PHP no tiene extensión MySQL habilitada

**Soluciones:**

1. **Verificar MySQL corriendo:**
   - Presiona **Windows + R**, escribe `services.msc`
   - Busca **MySQL** o **MySQL80**
   - Estado: **En ejecución** ✅
   - Si no está corriendo:
     ```powershell
     # PowerShell como Administrador
     net start MySQL80
     ```

2. **Verificar extensiones PHP:**
   - Abre `C:\PHP\php.ini`
   - Busca estas líneas (deben estar SIN punto y coma al inicio):
     ```ini
     extension=mysqli
     extension=pdo_mysql
     ```
   - Si tienen `;` al inicio, quítalos
   - Reinicia IIS: `iisreset`

3. **Probar conexión manualmente:**
   ```powershell
   cd C:\inetpub\wwwroot\sistema_almacen_utp
   php artisan tinker
   
   # Dentro de tinker:
   DB::connection()->getPdo();
   ```
   - Si funciona, verás información de la conexión PDO ✅
   - Si falla, verás el error exacto

---

### Problema 6: "Warning: Could not write to storage/logs"

**Causa:** IIS no tiene permisos de escritura en `storage/` o `bootstrap/cache/`

**Solución:**

```powershell
# PowerShell como Administrador
cd C:\inetpub\wwwroot\sistema_almacen_utp

# Dar permisos completos
icacls storage /grant "IIS_IUSRS:(OI)(CI)F" /T
icacls bootstrap\cache /grant "IIS_IUSRS:(OI)(CI)F" /T

# Verificar permisos aplicados
icacls storage
icacls bootstrap\cache
```

**Verificación:**
- Debe aparecer `IIS_IUSRS:(OI)(CI)F` en la lista de permisos
- `(OI)` = Object Inherit (heredar a archivos)
- `(CI)` = Container Inherit (heredar a carpetas)
- `F` = Full Control (control total)

---

### Problema 7: "El sitio carga pero sin estilos (CSS/JS no cargan)"

**Causas:**
- Archivos estáticos no configurados correctamente
- MIME types faltantes

**Soluciones:**

1. **Verificar MIME types en IIS:**
   - IIS Manager → tu sitio → **Tipos MIME**
   - Debe tener estos tipos:
     - `.css` → `text/css`
     - `.js` → `application/javascript`
     - `.json` → `application/json`
     - `.woff2` → `font/woff2`

2. **Habilitar contenido estático:**
   - IIS Manager → tu sitio → **Asignación de controladores**
   - Busca `StaticFile`
   - Debe estar **Habilitado** ✅

3. **Verificar ruta de assets:**
   - Los archivos CSS/JS deben estar en `public/`
   - Verifica en `public/` que existan las carpetas `css/`, `js/`, etc.

---

### Problema 8: "IIS no inicia después de instalar PHP"

**Causa:** Configuración incorrecta de FastCGI

**Solución:**

1. **Verificar ruta de PHP en FastCGI:**
   - IIS Manager → Servidor → **Configuración de FastCGI**
   - Ruta completa: `C:\PHP\php-cgi.exe`
   - Verifica que el archivo exista: `Test-Path C:\PHP\php-cgi.exe`

2. **Reinstalar FastCGI:**
   ```powershell
   # PowerShell como Administrador
   # Desinstalar configuración actual
   Remove-WebConfigurationProperty -pspath 'MACHINE/WEBROOT/APPHOST' -filter "system.webServer/fastCgi" -name "." -AtElement @{fullPath='C:\PHP\php-cgi.exe'}
   
   # Reinstalar
   Add-WebConfiguration -pspath 'MACHINE/WEBROOT/APPHOST' -filter "system.webServer/fastCgi" -value @{fullPath='C:\PHP\php-cgi.exe'}
   
   # Reiniciar IIS
   iisreset
   ```

---

### 🔍 Dónde encontrar logs de IIS

1. **Logs de acceso de IIS:**
   ```
   C:\inetpub\logs\LogFiles\W3SVC1\
   ```
   - Archivos: `u_exYYMMDD.log` (por día)
   - Contiene todas las peticiones HTTP y códigos de respuesta

2. **Logs de aplicación Laravel:**
   ```
   C:\inetpub\wwwroot\sistema_almacen_utp\storage\logs\laravel.log
   ```
   - Errores de PHP y Laravel

3. **Visor de eventos de Windows:**
   - Presiona **Windows + X** → **Visor de eventos**
   - Ve a **Registros de aplicaciones y servicios** → **Microsoft** → **Windows** → **IIS-Configuration**
   - Aquí aparecen errores de configuración de IIS

4. **Errores de FastCGI:**
   - IIS Logs (arriba) + Visor de eventos
   - Busca "FastCGI" en los eventos

---

### ✅ Checklist de Troubleshooting IIS

Cuando tengas problemas, revisa en este orden:

- [ ] ¿El sitio apunta a la carpeta `/public`? (no a la raíz)
- [ ] ¿Existe el archivo `web.config` en `/public`?
- [ ] ¿URL Rewrite Module está instalado?
- [ ] ¿Los permisos de `storage/` y `bootstrap/cache/` están configurados?
- [ ] ¿MySQL está corriendo como servicio?
- [ ] ¿El archivo `.env` tiene las credenciales correctas?
- [ ] ¿PHP tiene las extensiones necesarias habilitadas?
- [ ] ¿FastCGI está configurado correctamente?
- [ ] ¿Se limpió la caché de Laravel?
- [ ] ¿Los logs muestran algún error específico?

---

## �🔄 Actualizaciones Futuras

### Cómo actualizar el sistema cuando haya cambios:

**Si usas Git:**
```powershell
# Descargar cambios
git pull origin main

# Instalar nuevas dependencias
composer install

# Aplicar nuevas migraciones
php artisan migrate

# Limpiar cache
php artisan config:clear
php artisan cache:clear
```

**Si recibes archivos actualizados:**
1. Copia los archivos nuevos a `C:\xampp\htdocs\sistema_almacen_utp`
2. Ejecuta:
   ```powershell
   composer install
   php artisan migrate
   php artisan config:clear
   ```

---

## 📱 Uso Diario del Sistema

### Iniciar el sistema cada día:

#### Si usas XAMPP:

1. **Encender el servidor:**
   - Abre XAMPP Control Panel
   - Inicia **MySQL** (debe estar en verde)

2. **Iniciar la API Laravel:**
   - Abre PowerShell en la carpeta del proyecto
   - Ejecuta:
     ```powershell
     php artisan serve
     ```

#### Si usas Laragon:

1. **Encender el servidor:**
   - Abre Laragon
   - Haz clic en **Start All**
   - Apache y MySQL se ponen en verde automáticamente ✅

2. **Iniciar la API Laravel:**
   - En Laragon, haz clic derecho en el proyecto → **Terminal**
   - Ejecuta:
     ```powershell
     php artisan serve
     ```
   
   **O más fácil con Laragon:**
   - En Laragon, haz clic derecho en el proyecto
   - Selecciona **Web** → Abre automáticamente en el navegador
   - Agrega `/api` al final de la URL

#### Si usas IIS (Servidor de Producción):

1. **IIS ya está ejecutándose automáticamente:**
   - IIS se ejecuta como servicio de Windows
   - NO necesitas "encender" nada cada día ✅
   - El sistema está disponible 24/7

2. **Verificar que IIS esté ejecutándose:**
   - Presiona **Windows + R**, escribe `services.msc`
   - Busca **Servicio de publicación World Wide Web**
   - Estado debe ser: **En ejecución** ✅
   - Tipo de inicio: **Automático** ✅

3. **Acceder al sistema:**
   - Abre el navegador
   - Ve a la IP o dominio del servidor:
     - Local: `http://localhost:8000`
     - Red local: `http://IP_DEL_SERVIDOR:8000`
     - Dominio: `http://tu-dominio.com`

4. **Si necesitas reiniciar IIS:**
   - Abre PowerShell como Administrador
   - Ejecuta:
     ```powershell
     iisreset
     ```

5. **Limpiar caché de Laravel (cuando hagas cambios):**
   - Abre PowerShell como Administrador
   - Navega al proyecto:
     ```powershell
     cd C:\inetpub\wwwroot\sistema_almacen_utp
     ```
   - Limpia caché:
     ```powershell
     php artisan config:clear
     php artisan cache:clear
     php artisan view:clear
     php artisan route:clear
     
     # Luego reconstruye caché (producción):
     php artisan config:cache
     php artisan route:cache
     php artisan view:cache
     ```

**💡 Ventaja de IIS:** Una vez configurado, funciona 24/7 sin necesidad de "encenderlo" cada día.

---

### Para ambos (XAMPP, Laragon e IIS):

3. **Iniciar el Frontend** (si aplica):
   - Abre otra ventana de PowerShell/Terminal
   - Navega a la carpeta del frontend
   - Ejecuta:
     ```powershell
     npm run dev
     ```

4. **Acceder al sistema:**
   - Abre el navegador
   - Ve a la URL del frontend (ej: http://localhost:5173)

---

### Apagar el sistema:

#### Si usas XAMPP:
1. En PowerShell donde corre `php artisan serve`, presiona **Ctrl + C**
2. En PowerShell donde corre el frontend, presiona **Ctrl + C**
3. En XAMPP Control Panel, haz clic en **Stop** junto a MySQL

#### Si usas Laragon:
1. En PowerShell donde corre `php artisan serve`, presiona **Ctrl + C** (si lo iniciaste manualmente)
2. En PowerShell donde corre el frontend, presiona **Ctrl + C**
3. En Laragon, haz clic en **Stop All** (o simplemente cierra Laragon)

**💡 Tip Laragon:** Si instalaste Apache/MySQL como servicios de Windows en Laragon, no necesitas apagarlos - se mantienen corriendo sin problemas.

#### Si usas IIS:
- **NO necesitas apagar IIS** - está diseñado para funcionar 24/7 como servicio de Windows
- El sistema estará disponible constantemente
- Solo detén el frontend si lo estás usando:
  ```powershell
  # En PowerShell del frontend, presiona Ctrl + C
  ```

**⚠️ Solo apaga IIS si:**
- Necesitas hacer mantenimiento del servidor
- Vas a actualizar Windows
- Para detener IIS (si realmente lo necesitas):
  ```powershell
  # PowerShell como Administrador
  iisreset /stop
  
  # Para iniciarlo de nuevo:
  iisreset /start
  ```

---

## 📞 Soporte y Contacto

### Si tienes problemas:

1. **Revisa los logs de Laravel:**
   - Ve a `storage/logs/laravel.log`
   - Los últimos errores están al final del archivo

2. **Verifica el estado de los servicios:**
   ```powershell
   # Verificar PHP
   php -v
   
   # Verificar Composer
   composer --version
   
   # Verificar conexión a MySQL
   php artisan tinker
   DB::connection()->getPdo();
   ```

3. **Contacta al equipo de desarrollo:**
   - Proporciona el mensaje de error completo
   - Indica qué paso estabas realizando
   - Adjunta capturas de pantalla si es posible

---

## ✅ Checklist de Instalación

Marca cada paso completado según tu opción elegida:

### Para todos (XAMPP, Laragon, IIS):
- [ ] PHP 8.2 o superior instalado
- [ ] Composer instalado y verificado (`composer --version`)
- [ ] MySQL instalado y corriendo
- [ ] Base de datos `almacenUtp` creada
- [ ] Proyecto copiado a la carpeta correcta
- [ ] Dependencias instaladas con `composer install`
- [ ] Archivo `.env` configurado correctamente (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
- [ ] Llave de aplicación generada (`php artisan key:generate`)
- [ ] Migraciones ejecutadas (`php artisan migrate:fresh --seed`)
- [ ] Login probado con admin@almacen.com / Admin123

### Si usaste XAMPP:
- [ ] XAMPP instalado en `C:\xampp`
- [ ] MySQL iniciado y en verde en XAMPP Control Panel
- [ ] Proyecto en `C:\xampp\htdocs\sistema_almacen_utp`
- [ ] Servidor Laravel iniciado (`php artisan serve`)
- [ ] Puedes acceder a http://localhost:8000

### Si usaste Laragon:
- [ ] Laragon instalado
- [ ] Composer viene incluido con Laragon ✅
- [ ] Proyecto en `C:\laragon\www\sistema_almacen_utp`
- [ ] Laragon iniciado (Start All)
- [ ] Servidor Laravel funcionando (automático o manual con `php artisan serve`)
- [ ] Puedes acceder al sitio desde Laragon

### Si usaste IIS:
- [ ] IIS instalado y habilitado en Windows
- [ ] PHP 8.2 NTS instalado en `C:\PHP`
- [ ] FastCGI configurado en IIS
- [ ] URL Rewrite Module instalado
- [ ] Proyecto en `C:\inetpub\wwwroot\sistema_almacen_utp`
- [ ] Sitio IIS creado apuntando a `/public` (¡no a la raíz!)
- [ ] Archivo `web.config` creado en `/public` con reglas de reescritura
- [ ] Permisos configurados (IIS_IUSRS) para `storage/` y `bootstrap/cache/`
- [ ] Caché de producción compilada (`config:cache`, `route:cache`, `view:cache`)
- [ ] Sitio accesible desde navegador (http://localhost o http://IP_SERVIDOR)
- [ ] Rutas API funcionando (probado con http://localhost/api/productos)

### Frontend (si aplica):
- [ ] Frontend conectado y funcionando
- [ ] Puede hacer login correctamente
- [ ] Se comunica con la API sin errores

---

## 🔄 Cambiar de XAMPP a Laragon

Si ya tienes XAMPP instalado y quieres cambiarte a Laragon por problemas de estabilidad:

### Paso 1: Hacer backup de tu base de datos

1. Abre phpMyAdmin en XAMPP: http://localhost/phpmyadmin
2. Selecciona la base de datos `almacenUtp`
3. Haz clic en **Exportar**
4. Deja las opciones por defecto
5. Haz clic en **Continuar**
6. Guarda el archivo `almacenUtp.sql` en un lugar seguro

### Paso 2: Detener XAMPP

1. Abre el Panel de Control de XAMPP
2. Haz clic en **Stop** en Apache
3. Haz clic en **Stop** en MySQL
4. Cierra XAMPP

### Paso 3: Instalar Laragon

1. Descarga e instala Laragon siguiendo **Opción B** de este manual
2. Inicia Laragon y haz clic en **Start All**

### Paso 4: Copiar el proyecto

**Opción A - Copiar carpeta:**
1. Copia toda la carpeta `C:\xampp\htdocs\sistema_almacen_utp`
2. Pégala en `C:\laragon\www\`
3. Ruta final: `C:\laragon\www\sistema_almacen_utp`

**Opción B - Mover carpeta:**
1. Corta la carpeta `C:\xampp\htdocs\sistema_almacen_utp`
2. Pégala en `C:\laragon\www\`

### Paso 5: Restaurar la base de datos

1. Abre phpMyAdmin en Laragon: http://localhost/phpmyadmin
2. Crea la base de datos `almacenUtp` (igual que antes)
3. Selecciona la base de datos `almacenUtp`
4. Haz clic en **Importar**
5. Selecciona el archivo `almacenUtp.sql` que guardaste
6. Haz clic en **Continuar**
7. Espera a que termine (debe decir "Importación finalizada con éxito")

### Paso 6: Verificar configuración

1. Abre el archivo `.env` del proyecto (en `C:\laragon\www\sistema_almacen_utp`)
2. Verifica que tenga:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=almacenUtp
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Si está correcto, no necesitas cambiar nada ✅

### Paso 7: Probar el sistema

1. Abre Terminal en Laragon (clic derecho en proyecto → Terminal)
2. Ejecuta:
   ```powershell
   php artisan serve
   ```
3. Abre el navegador en http://127.0.0.1:8000
4. Haz login con tu usuario

**¡Listo! Ahora estás usando Laragon** 🎉

### Paso 8: Desinstalar XAMPP (Opcional)

Si todo funciona bien con Laragon y ya no necesitas XAMPP:

1. Ve a **Panel de Control** → **Programas y características**
2. Busca **XAMPP**
3. Haz clic derecho → **Desinstalar**
4. Sigue el asistente de desinstalación
5. **OPCIONAL:** Elimina la carpeta `C:\xampp` manualmente si queda

**💡 Ventajas que notarás con Laragon:**
- ✅ Ya no se cerrará automáticamente
- ✅ El sistema será más rápido
- ✅ Consume menos RAM
- ✅ Más fácil de administrar proyectos

---

## ✅ Checklist de Instalación

Marca cada paso completado:

---

## 🎉 ¡Instalación Completa!

Si completaste todos los pasos, el sistema está listo para usar.

**Próximos pasos:**
1. Cambia la contraseña del administrador desde el sistema
2. Crea usuarios para tu equipo
3. Configura las secciones y tipos de stock según tu almacén
4. Empieza a registrar tus productos

**¿Problemas con XAMPP cerrándose?** Lee la sección "Cambiar de XAMPP a Laragon" en este manual.

**¡Gracias por usar el Sistema de Almacén UTP!** 🚀
