# MovilTech - Estructura para Hostinger

## 📁 Estructura Optimizada para Hosting Compartido

Esta estructura está **optimizada específicamente para Hostinger** y otros hosting compartidos.

### 🏗️ Estructura de Directorios

```
MovilTech/
├── public_html/              # Directorio web público (subir a public_html en Hostinger)
│   ├── index.php            # Punto de entrada de la aplicación
│   ├── .htaccess           # Configuración de Apache optimizada
│   ├── .user.ini           # Configuración de PHP para Hostinger
│   ├── phpinfo.php         # Verificación PHP (eliminar después)
│   ├── favicon.ico         # Icono del sitio
│   ├── robots.txt          # Configuración para motores de búsqueda
│   └── storage/            # Enlace simbólico a archivos públicos
│
├── laravel_app/            # Directorio privado (subir fuera de public_html)
│   ├── app/                # Lógica de aplicación
│   ├── bootstrap/          # Archivos de inicialización
│   ├── config/             # Configuraciones
│   ├── database/           # Migraciones y seeders
│   ├── resources/          # Vistas, CSS, JS
│   ├── routes/             # Definición de rutas
│   ├── storage/            # Archivos de almacenamiento
│   ├── vendor/             # Dependencias de Composer
│   ├── artisan             # CLI de Laravel
│   ├── composer.json       # Dependencias PHP
│   ├── composer.lock       # Versiones exactas
│   ├── .env                # Variables de entorno
│   └── .env.example        # Plantilla de variables
│
└── archivos_restantes/     # Archivos de desarrollo (no subir)
    ├── tests/              # Tests
    ├── package.json        # Dependencias Node.js
    ├── tailwind.config.js  # Configuración Tailwind
    └── vite.config.js      # Configuración Vite
```

## 🚀 Instrucciones de Subida a Hostinger

### 1. Subir Archivos Públicos
- **Subir todo el contenido de `public_html/`** al directorio `public_html/` en tu hosting
- Esto incluye: `index.php`, `.htaccess`, `.user.ini`, `favicon.ico`, `robots.txt`, y `storage/`
- **Importante**: Eliminar `phpinfo.php` después de verificar la configuración PHP

### 2. Subir Archivos Privados
- **Subir todo el contenido de `laravel_app/`** al directorio raíz de tu hosting (fuera de `public_html/`)
- Esto incluye: `app/`, `config/`, `database/`, `vendor/`, `.env`, etc.

### 3. Configurar Variables de Entorno
- Editar el archivo `.env` en el directorio `laravel_app/` con tus datos:
```env
APP_NAME="MovilTech"
APP_ENV=production
APP_KEY=base64:TU_CLAVE_AQUI
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=tu_base_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=mail.tu-dominio.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@tu-dominio.com
MAIL_PASSWORD=tu-password-email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@tu-dominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Ejecutar Comandos de Laravel
- Acceder al panel de Hostinger → Terminal
- Navegar al directorio `laravel_app/`
- Ejecutar:
```bash
cd laravel_app
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🔧 Configuración de PHP en Hostinger

### Requisitos de PHP
- **Versión PHP**: 8.2 o superior (requerido por Laravel 12)
- **Extensiones PHP requeridas**:
  - `pdo` y `pdo_mysql` (conexión a base de datos)
  - `mbstring` (manejo de cadenas multibyte)
  - `openssl` (encriptación y HTTPS)
  - `json` (procesamiento JSON)
  - `tokenizer` (procesamiento de tokens)
  - `xml` (procesamiento XML)
  - `ctype` (validación de caracteres)
  - `fileinfo` (detección de tipos de archivo)
  - `curl` (comunicación HTTP)
  - `zip` (manejo de archivos ZIP)
  - `gd` (procesamiento de imágenes)
  - `intl` (internacionalización)

### Configuración de PHP en Hostinger

#### Opción 1: Panel de Control de Hostinger (Recomendado)
1. Accede al **Panel de Control de Hostinger** (hPanel)
2. Ve a **Avanzado** → **Selector de Versión de PHP**
3. Selecciona **PHP 8.2** o superior
4. Haz clic en **Configuración** junto a la versión de PHP
5. Configura los siguientes valores:
   - **memory_limit**: 256M
   - **max_execution_time**: 300
   - **max_input_time**: 300
   - **post_max_size**: 100M
   - **upload_max_filesize**: 100M
   - **display_errors**: Off (producción)
   - **error_reporting**: E_ALL & ~E_DEPRECATED & ~E_STRICT
   - **date.timezone**: America/Bogota (Colombia)

#### Opción 2: Archivo .user.ini (Alternativa)
1. El archivo `.user.ini` ya está incluido en `public_html/`
2. Este archivo se sube automáticamente con los archivos públicos
3. Hostinger aplicará estas configuraciones automáticamente
4. **Nota**: Los cambios pueden tardar hasta 5 minutos en aplicarse

### Verificar Configuración PHP

#### Antes de Subir (Local)
Ejecuta el script de verificación en tu máquina local:
```bash
php check-php-requirements.php
```

Este script verificará:
- Versión de PHP
- Extensiones requeridas
- Límites de memoria y ejecución
- Configuración de subida de archivos

#### Después de Subir (Hostinger)
1. **Método 1 - Terminal de Hostinger**:
   ```bash
   cd public_html
   php check-php-requirements.php
   ```

2. **Método 2 - phpinfo.php** (temporal):
   - Accede a `https://tu-dominio.com/phpinfo.php`
   - Verifica la configuración de PHP
   - **IMPORTANTE**: Elimina este archivo después de verificar por seguridad

### Habilitar Extensiones en Hostinger
Si alguna extensión no está habilitada:
1. Ve a **Avanzado** → **Selector de Versión de PHP**
2. Haz clic en **Extensiones**
3. Habilita las extensiones requeridas:
   - pdo_mysql
   - mbstring
   - gd
   - zip
   - intl
   - curl
   - openssl

## 🔧 Configuración Adicional

### Base de Datos
- Crear una base de datos MySQL en el panel de Hostinger
- Importar el archivo `database.sqlite` si es necesario
- O ejecutar las migraciones con `php artisan migrate`

### Permisos de Archivos
- Asegurar que `storage/` y `bootstrap/cache/` tengan permisos 755
- El archivo `.env` debe tener permisos 644
- El archivo `.user.ini` debe tener permisos 644

### SSL/HTTPS
- Activar SSL en el panel de Hostinger
- Actualizar `APP_URL` en `.env` para usar `https://`

## 📋 Checklist de Verificación

### Configuración PHP
- [ ] PHP 8.2 o superior seleccionado en Hostinger
- [ ] Extensiones PHP requeridas habilitadas
- [ ] Archivo `.user.ini` subido a `public_html/`
- [ ] Configuración PHP verificada con `check-php-requirements.php`
- [ ] `phpinfo.php` eliminado después de verificar (seguridad)

### Archivos y Estructura
- [ ] Archivos de `public_html/` subidos a `public_html/` en hosting
- [ ] Archivos de `laravel_app/` subidos fuera de `public_html/`
- [ ] Permisos correctos en `storage/` (755) y `.env` (644)

### Configuración de Aplicación
- [ ] Archivo `.env` configurado con datos correctos
- [ ] Base de datos creada y configurada
- [ ] Comandos de Laravel ejecutados (migrate, cache, etc.)
- [ ] SSL activado
- [ ] `APP_URL` configurado con HTTPS

### Verificación Final
- [ ] Sitio accesible desde el navegador
- [ ] Sin errores 500 en los logs
- [ ] Assets (CSS/JS) cargando correctamente
- [ ] Base de datos conectando correctamente

## 🆘 Solución de Problemas

### Error 500
- Verificar permisos de archivos (`storage/` y `bootstrap/cache/` con 755)
- Revisar logs en `laravel_app/storage/logs/laravel.log`
- Verificar configuración de `.env`
- Verificar que PHP 8.2+ esté seleccionado
- Verificar que todas las extensiones PHP estén habilitadas

### Error: Extension Missing
- Acceder a **Selector de Versión de PHP** en Hostinger
- Habilita las extensiones faltantes: `pdo_mysql`, `mbstring`, `gd`, `zip`, `intl`
- Reinicia el servicio si es necesario

### Error: Memory Limit Exceeded
- Verificar `memory_limit` en `.user.ini` o panel de Hostinger
- Aumentar a 256M o más si es necesario
- Verificar que `.user.ini` esté en `public_html/`

### Assets no cargan
- Verificar que `storage/` esté en `public_html/`
- Ejecutar `php artisan storage:link`
- Verificar permisos de archivos en `storage/`

### Base de datos no conecta
- Verificar credenciales en `.env`
- Confirmar que la base de datos existe
- Verificar que el usuario tenga permisos
- Verificar que `pdo_mysql` esté habilitado

### PHP Version Mismatch
- Verificar versión PHP en Hostinger: `php -v` en terminal
- Cambiar a PHP 8.2+ en el panel de Hostinger
- Verificar que `composer.json` requiere `"php": "^8.2"`

## 📞 Soporte

Para problemas específicos de Hostinger:
- Revisar la documentación de Hostinger
- Contactar soporte técnico de Hostinger
- Verificar logs de error del servidor

---
**MovilTech** - Sistema optimizado para Hostinger
