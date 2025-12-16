# 🗑️ Guía de Eliminación Post-Despliegue

## ⚠️ IMPORTANTE: Eliminar después del despliegue

Este documento indica qué código eliminar después de completar el despliegue en producción.

---

## 📋 Archivos y Código a Eliminar

### 1. Controlador de Deployment

**Archivo:** `app/Http/Controllers/DeploymentController.php`

**Acción:** Eliminar completamente el archivo

```bash
# En producción, eliminar:
rm app/Http/Controllers/DeploymentController.php
```

---

### 2. Vista de Deployment

**Archivo:** `resources/views/deployment/index.blade.php`

**Acción:** Eliminar completamente el archivo y la carpeta si está vacía

```bash
# En producción, eliminar:
rm resources/views/deployment/index.blade.php
rmdir resources/views/deployment  # Si está vacía
```

---

### 3. Rutas Temporales en `routes/web.php`

**Archivo:** `routes/web.php`

**Acción:** Eliminar las siguientes líneas:

#### A. Import del controlador (línea ~12):
```php
use App\Http\Controllers\DeploymentController;
```

#### B. Todo el bloque de rutas temporales (al final del archivo):
```php
/*
|--------------------------------------------------------------------------
| TEMPORARY DEPLOYMENT ROUTES - REMOVE AFTER DEPLOYMENT
|--------------------------------------------------------------------------
|
| ⚠️ WARNING: These routes are for deployment purposes only.
| Remove them immediately after completing the deployment.
|
| Usage:
| - /__deploy__?token=YOUR_TOKEN - Deployment dashboard
| - /__infra__/migrate?token=YOUR_TOKEN - Run migrations
| - /__infra__/seed?token=YOUR_TOKEN - Run seeders
| - /__infra__/status?token=YOUR_TOKEN - Check status
|
| IMPORTANT: Change DEPLOYMENT_TOKEN in DeploymentController.php
| before using these routes in production.
|
*/
Route::prefix('__deploy__')->group(function () {
    Route::get('/', [DeploymentController::class, 'index'])->name('deployment.index');
});

Route::prefix('__infra__')->group(function () {
    Route::post('/migrate', [DeploymentController::class, 'migrate'])->name('deployment.migrate');
    Route::get('/migrate', [DeploymentController::class, 'migrate'])->name('deployment.migrate.get');
    Route::post('/seed', [DeploymentController::class, 'seed'])->name('deployment.seed');
    Route::get('/seed', [DeploymentController::class, 'seed'])->name('deployment.seed.get');
    Route::get('/status', [DeploymentController::class, 'status'])->name('deployment.status');
});
```

---

## ✅ Checklist Post-Despliegue

Después de ejecutar las migraciones y verificar que todo funciona:

- [ ] Eliminar `app/Http/Controllers/DeploymentController.php`
- [ ] Eliminar `resources/views/deployment/index.blade.php`
- [ ] Eliminar la carpeta `resources/views/deployment/` si está vacía
- [ ] Eliminar el import de `DeploymentController` en `routes/web.php`
- [ ] Eliminar todas las rutas temporales (`__deploy__` y `__infra__`) de `routes/web.php`
- [ ] Verificar que las rutas ya no son accesibles
- [ ] Eliminar este archivo (`DEPLOYMENT-REMOVAL.md`)

---

## 🔒 Seguridad

**NUNCA dejes estas rutas activas en producción.**

Estas rutas permiten ejecutar migraciones y seeders sin autenticación adecuada, lo que representa un riesgo de seguridad significativo.

---

## 📝 Notas Adicionales

- Las migraciones ejecutadas NO eliminan datos existentes
- Los seeders solo crean nuevos registros (no usan `truncate()` ni `delete()`)
- Las tablas `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, y `failed_jobs` se crean automáticamente con las migraciones de Laravel

---

**Fecha de creación:** 2025-12-14  
**Última actualización:** 2025-12-14

