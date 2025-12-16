# 🔧 Solución Error 419 en Login (Producción HTTPS)

## Problema

Error **419 Page Expired** al intentar hacer login en producción (`https://moviltech.site/login`).

## Causa

En producción con HTTPS, Laravel necesita configuraciones específicas para las cookies de sesión y CSRF:

1. **SESSION_SECURE_COOKIE** debe ser `true` para HTTPS
2. **APP_URL** debe estar configurado con HTTPS
3. **TrustProxies** debe confiar en los proxies de Hostinger
4. La caché de configuración puede estar desactualizada

## ✅ Solución

### Paso 1: Configurar Variables de Entorno

En el archivo `.env` de producción, asegúrate de tener:

```env
APP_ENV=production
APP_URL=https://moviltech.site
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_LIFETIME=120
SESSION_DOMAIN=
```

**Importante:**
- `SESSION_SECURE_COOKIE=true` es **crítico** para HTTPS
- `APP_URL` debe usar `https://` no `http://`
- `SESSION_DOMAIN` debe estar **vacío** (no configurado) para usar el dominio por defecto
- `SESSION_LIFETIME=120` (2 horas) es el tiempo de vida de la sesión en minutos
- No uses `SESSION_SAME_SITE=none` a menos que sea absolutamente necesario

### Paso 2: Configurar TrustProxies

El middleware `TrustProxies` debe confiar en todos los proxies de Hostinger. Ya está configurado correctamente con los headers necesarios.

### Paso 2.5: Detección Automática de HTTPS (Ya Implementado)

Se ha agregado lógica en `AppServiceProvider` para forzar cookies seguras automáticamente cuando:
- `APP_ENV=production`
- `APP_URL` usa `https://`
- `SESSION_SECURE_COOKIE` no está configurado explícitamente

Esto asegura que las cookies se marquen como `Secure` incluso si Laravel no detecta HTTPS correctamente detrás del proxy.

### Paso 3: Limpiar Caché de Configuración

Después de cambiar las variables de entorno, **debes limpiar la caché**:

#### Opción A: Desde SSH (Si tienes acceso)

```bash
cd /ruta/a/tu/proyecto
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

#### Opción B: Eliminar Archivos Manualmente

Elimina estos archivos desde el File Manager de Hostinger:

1. `bootstrap/cache/config.php` - **Eliminar este archivo**
2. `bootstrap/cache/routes-*.php` - Eliminar todos los archivos que empiecen con `routes-`
3. `storage/framework/cache/data/*` - Vaciar esta carpeta

### Paso 4: Verificar Configuración de Sesiones

Verifica que la tabla `sessions` exista en la base de datos:

```sql
SHOW TABLES LIKE 'sessions';
```

Si no existe, créala ejecutando:

```bash
php artisan session:table
php artisan migrate
```

## 🔍 Verificación

Después de aplicar los cambios:

1. **Limpia la caché del navegador** (Ctrl+Shift+Delete)
2. **Abre la página de login** en modo incógnito
3. **Intenta hacer login** - debería funcionar sin error 419

## ⚠️ Si Sigue Fallando

### Verificar Cookies en el Navegador

1. Abre las herramientas de desarrollador (F12)
2. Ve a la pestaña **Application** > **Cookies** > `https://moviltech.site`
3. Verifica que exista la cookie de sesión (ej: `moviltech-site-session`)
4. Verifica que tenga los atributos:
   - `Secure: ✓` (debe estar marcado)
   - `SameSite: Lax` o `Strict`

### Verificar Headers de Respuesta

1. En las herramientas de desarrollador, ve a **Network**
2. Recarga la página de login
3. Selecciona la petición a `/login` (GET)
4. Revisa los headers de respuesta:
   - `Set-Cookie` debe incluir `Secure` y `SameSite=Lax`

### Revisar Logs

Revisa `storage/logs/laravel.log` para ver errores específicos:

```bash
tail -f storage/logs/laravel.log
```

## 📝 Notas Importantes

1. **No uses `SESSION_SAME_SITE=none`** a menos que sea absolutamente necesario (requiere `Secure=true`)
2. **Limpia la caché** después de cada cambio en `.env` o `config/`
3. **En producción siempre usa HTTPS** - nunca `http://` en `APP_URL`
4. **Las cookies deben ser `Secure`** cuando uses HTTPS

## 🔄 Flujo de Solución Rápida

1. ✅ Verificar `.env` tiene `SESSION_SECURE_COOKIE=true` y `APP_URL=https://moviltech.site`
2. ✅ Eliminar `bootstrap/cache/config.php`
3. ✅ Limpiar caché del navegador
4. ✅ Probar login en modo incógnito

## 🔧 Solución Avanzada: Script de Diagnóstico

Si el problema persiste, crea un archivo temporal `public_html/diagnose-session.php` para diagnosticar:

```php
<?php
// ⚠️ TEMPORAL: Eliminar después de usar
// Acceso: https://moviltech.site/diagnose-session.php?token=DIAG_TOKEN_12345

$token = $_GET['token'] ?? '';
if ($token !== 'DIAG_TOKEN_12345') {
    die('Unauthorized');
}

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "<h2>Diagnóstico de Sesión</h2>";
echo "<pre>";

echo "APP_URL: " . config('app.url') . "\n";
echo "APP_ENV: " . config('app.env') . "\n";
echo "SESSION_DRIVER: " . config('session.driver') . "\n";
echo "SESSION_SECURE_COOKIE: " . (config('session.secure') ? 'true' : 'false') . "\n";
echo "SESSION_SAME_SITE: " . config('session.same_site') . "\n";
echo "SESSION_DOMAIN: " . (config('session.domain') ?: 'null (default)') . "\n";
echo "SESSION_LIFETIME: " . config('session.lifetime') . " minutos\n";

echo "\n--- Request Info ---\n";
echo "Scheme: " . $request->getScheme() . "\n";
echo "Is Secure: " . ($request->isSecure() ? 'true' : 'false') . "\n";
echo "URL: " . $request->fullUrl() . "\n";
echo "Has Session: " . ($request->hasSession() ? 'true' : 'false') . "\n";

if ($request->hasSession()) {
    echo "Session ID: " . $request->session()->getId() . "\n";
    echo "CSRF Token: " . csrf_token() . "\n";
}

echo "\n--- Cookies ---\n";
foreach ($_COOKIE as $name => $value) {
    echo "$name: " . substr($value, 0, 50) . "...\n";
}

echo "\n--- Headers ---\n";
foreach (getallheaders() as $name => $value) {
    if (stripos($name, 'forwarded') !== false || stripos($name, 'x-forwarded') !== false) {
        echo "$name: $value\n";
    }
}

echo "</pre>";
echo "<p><strong>⚠️ IMPORTANTE: Elimina este archivo después de usar</strong></p>";

$kernel->terminate($request, $response);
?>
```

Este script te ayudará a verificar:
- Si Laravel detecta HTTPS correctamente
- Si la sesión se está creando
- Si el token CSRF se está generando
- Qué cookies se están estableciendo
- Los headers de proxy que está recibiendo

## 🚨 Solución de Emergencia: Deshabilitar CSRF Temporalmente (NO RECOMENDADO)

**⚠️ SOLO PARA DIAGNÓSTICO - NO USAR EN PRODUCCIÓN**

Si necesitas verificar que el problema es CSRF, puedes temporalmente excluir `/login` del middleware CSRF:

```php
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'login', // ⚠️ SOLO PARA DIAGNÓSTICO
    // ... otras rutas
];
```

**IMPORTANTE:** Esto desactiva la protección CSRF para el login. Solo úsalo para diagnosticar y luego revierte el cambio inmediatamente.
