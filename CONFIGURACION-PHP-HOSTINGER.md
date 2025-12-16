# Configuración PHP para Hostinger - Guía Rápida

## ⚡ Configuración Rápida (5 minutos)

### Paso 1: Seleccionar Versión PHP
1. Accede a **hPanel** (Panel de Control de Hostinger)
2. Ve a **Avanzado** → **Selector de Versión de PHP**
3. Selecciona **PHP 8.2** o superior
4. Haz clic en **Guardar**

### Paso 2: Configurar PHP Settings
1. En la misma página, haz clic en **Configuración** (junto a PHP 8.2)
2. Configura estos valores:

```
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
post_max_size = 100M
upload_max_filesize = 100M
display_errors = Off
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
date.timezone = America/Bogota
```

3. Haz clic en **Guardar**

### Paso 3: Habilitar Extensiones
1. En **Selector de Versión de PHP**, haz clic en **Extensiones**
2. Habilita estas extensiones (marca las casillas):
   - ✅ pdo_mysql
   - ✅ mbstring
   - ✅ gd
   - ✅ zip
   - ✅ intl
   - ✅ curl
   - ✅ openssl
   - ✅ json
   - ✅ xml
   - ✅ fileinfo

3. Haz clic en **Guardar**

### Paso 4: Verificar Configuración
1. Sube el archivo `check-php-requirements.php` a `public_html/`
2. Accede a la terminal de Hostinger
3. Ejecuta: `php public_html/check-php-requirements.php`
4. Verifica que todos los requisitos estén marcados como ✓ PASS

## 📋 Valores Recomendados para Producción

### Límites de Memoria y Ejecución
- **memory_limit**: 256M (mínimo 128M)
- **max_execution_time**: 300 (mínimo 60)
- **max_input_time**: 300

### Límites de Subida
- **upload_max_filesize**: 100M (mínimo 20M)
- **post_max_size**: 100M (mínimo 20M)
- **max_file_uploads**: 20

### Configuración de Errores (Producción)
- **display_errors**: Off
- **display_startup_errors**: Off
- **log_errors**: On
- **error_reporting**: E_ALL & ~E_DEPRECATED & ~E_STRICT

### Zona Horaria
- **date.timezone**: America/Bogota (Colombia)

## 🔍 Verificación de Extensiones Requeridas

Ejecuta este comando en la terminal de Hostinger para verificar extensiones:

```bash
php -m | grep -E "pdo|mbstring|gd|zip|intl|curl|openssl|json|xml|fileinfo"
```

Debes ver todas estas extensiones listadas:
- pdo
- pdo_mysql
- mbstring
- gd
- zip
- intl
- curl
- openssl
- json
- xml
- fileinfo

## 🚨 Problemas Comunes

### "Class 'PDO' not found"
**Solución**: Habilita la extensión `pdo` y `pdo_mysql` en el panel de Hostinger

### "Call to undefined function mb_strlen()"
**Solución**: Habilita la extensión `mbstring` en el panel de Hostinger

### "Allowed memory size exhausted"
**Solución**: Aumenta `memory_limit` a 256M o más en la configuración PHP

### "Maximum execution time exceeded"
**Solución**: Aumenta `max_execution_time` a 300 en la configuración PHP

### "Upload file size exceeded"
**Solución**: Aumenta `upload_max_filesize` y `post_max_size` a 100M

## 📝 Notas Importantes

1. **Archivo .user.ini**: Si subes el archivo `.user.ini` a `public_html/`, Hostinger aplicará automáticamente estas configuraciones. Los cambios pueden tardar hasta 5 minutos.

2. **Verificación Temporal**: El archivo `phpinfo.php` es solo para verificación. **ELIMÍNALO** después de verificar la configuración por seguridad.

3. **Reinicio**: Después de cambiar la configuración PHP, espera 2-5 minutos antes de verificar los cambios.

4. **Versión PHP**: Laravel 12 requiere PHP 8.2 o superior. No uses PHP 8.1 o inferior.

## 🔗 Referencias

- [Documentación de Hostinger sobre PHP](https://support.hostinger.com/es/articles/actualizar-version-php)
- [Requisitos de Laravel 12](https://laravel.com/docs/12.x/installation#server-requirements)


