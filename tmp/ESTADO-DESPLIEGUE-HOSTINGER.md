# Estado Actual de Despliegue - Hostinger

## 📊 Estado Actual

### 1. Migraciones

**Método actual:**
- **Desarrollo local:** `php artisan migrate:fresh --seed` (desde README.md)
- **Hostinger (sin SSH):** A través de `DeploymentController` vía web
  - URL: `https://tudominio.com/__infra__/migrate?token=TOKEN`
  - Dashboard: `https://tudominio.com/__deploy__?token=TOKEN`

**Implementación:**
```56:58:app/Http/Controllers/DeploymentController.php
            Artisan::call('migrate', [
                '--no-interaction' => true,
            ]);
```

### 2. Seeders

**Método actual:**
- **Desarrollo local:** `php artisan migrate:fresh --seed` o `php artisan db:seed`
- **Hostinger (sin SSH):** A través de `DeploymentController` vía web
  - URL: `https://tudominio.com/__infra__/seed?token=TOKEN`
  - Seeder específico: `https://tudominio.com/__infra__/seed?token=TOKEN&seeder=RoleSeeder`

**Implementación:**
```88:91:app/Http/Controllers/DeploymentController.php
            Artisan::call('db:seed', [
                '--class' => $seeder,
                '--no-interaction' => true,
            ]);
```

**Seeders disponibles:**
- `DatabaseSeeder` - Ejecuta todos los seeders
- `RoleSeeder` - Crea roles y permisos
- `UserSeeder` - Crea usuarios de ejemplo
- `CategorySeeder` - Crea categorías
- `ProductSeeder` - Crea productos
- `CustomerSeeder` - Crea clientes
- `SupplierSeeder` - Crea proveedores

### 3. Script de Deploy (deploy.sh)

**Estado actual:**
El script `deploy.sh` NO incluye migraciones ni seeders. Solo incluye:
- Git pull
- Composer install
- NPM install/build
- Limpieza de caché
- Optimización de Laravel

```1:35:deploy.sh
#!/bin/bash

# Navegar al directorio del repositorio
cd /home/u123456789/domains/tudominio.com/private_html/MovilTech

# Obtener los últimos cambios
git pull origin main

# Instalar dependencias de Composer
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Instalar dependencias de NPM
npm install
npm run build

# Limpiar caché de Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Establecer permisos
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Si es necesario, establecer el propietario correcto
# chown -R usuario:grupo /ruta/a/tu/proyecto

echo "Despliegue completado correctamente"
exit 0
```

### 4. factus:sync

**⚠️ NO ENCONTRADO:** No se encontraron referencias a `factus:sync` en el código base.

**Posibles causas:**
- El comando aún no está implementado
- Es un comando de un paquete que no está instalado
- Se refiere a otro comando o funcionalidad

**Paquetes instalados relacionados con facturación:**
- `barryvdh/laravel-dompdf` - Para generación de PDFs

---

## 🚀 Adaptación para Hostinger

### Opción 1: Usar DeploymentController (Recomendado - Ya implementado)

**Ventajas:**
- ✅ Ya está implementado y funcionando
- ✅ No requiere SSH
- ✅ Interfaz web amigable
- ✅ Seguro con token de autenticación

**Pasos:**
1. Configurar token en `DeploymentController.php`
2. Acceder a `https://tudominio.com/__deploy__?token=TOKEN`
3. Ejecutar migraciones y seeders desde el dashboard

**Documentación:** Ver `DEPLOYMENT-INSTRUCTIONS.md`

### Opción 2: Actualizar deploy.sh para Hostinger

Si Hostinger permite ejecutar scripts bash (a través de cron o panel de control), se puede actualizar `deploy.sh`:

```bash
#!/bin/bash

# Navegar al directorio del repositorio
cd /home/u123456789/domains/tudominio.com/private_html/MovilTech

# Obtener los últimos cambios
git pull origin main

# Instalar dependencias de Composer
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Instalar dependencias de NPM
npm install
npm run build

# Ejecutar migraciones (solo las pendientes)
php artisan migrate --no-interaction --force

# Ejecutar seeders (solo si es necesario - comentar en producción)
# php artisan db:seed --no-interaction --class=DatabaseSeeder

# Limpiar caché de Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Establecer permisos
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "Despliegue completado correctamente"
exit 0
```

**⚠️ IMPORTANTE:** 
- No usar `migrate:fresh` en producción (elimina datos)
- Comentar los seeders en producción (solo crear datos de prueba)
- Usar `--force` para evitar confirmaciones interactivas

### Opción 3: Comandos Manuales desde Panel de Hostinger

Si Hostinger tiene un panel de control con ejecutor de comandos PHP:

```php
<?php
// Ejecutar desde el panel de Hostinger o crear un archivo temporal

// Migraciones
exec('cd /home/u123456789/domains/tudominio.com/private_html/MovilTech && php artisan migrate --no-interaction --force');

// Seeders (solo si es necesario)
// exec('cd /home/u123456789/domains/tudominio.com/private_html/MovilTech && php artisan db:seed --no-interaction');

// Limpiar caché
exec('cd /home/u123456789/domains/tudominio.com/private_html/MovilTech && php artisan cache:clear');
exec('cd /home/u123456789/domains/tudominio.com/private_html/MovilTech && php artisan config:clear');
exec('cd /home/u123456789/domains/tudominio.com/private_html/MovilTech && php artisan route:clear');
exec('cd /home/u123456789/domains/tudominio.com/private_html/MovilTech && php artisan view:clear');

// Optimizar
exec('cd /home/u123456789/domains/tudominio.com/private_html/MovilTech && php artisan config:cache');
exec('cd /home/u123456789/domains/tudominio.com/private_html/MovilTech && php artisan route:cache');
exec('cd /home/u123456789/domains/tudominio.com/private_html/MovilTech && php artisan view:cache');

echo "Comandos ejecutados correctamente";
?>
```

---

## 📝 Recomendaciones para Hostinger

### 1. Migraciones
- ✅ Usar `php artisan migrate` (NO `migrate:fresh`)
- ✅ Usar flag `--no-interaction` para evitar confirmaciones
- ✅ Usar flag `--force` en producción
- ✅ Verificar estado antes de ejecutar: `/__infra__/status?token=TOKEN`

### 2. Seeders
- ⚠️ **NO ejecutar en producción** a menos que sea necesario
- ✅ Si se ejecutan, usar seeders específicos, no `DatabaseSeeder`
- ✅ Verificar que los seeders no usen `truncate()` ni `delete()`

### 3. factus:sync
- ❓ **Pendiente de aclaración:** ¿Qué es `factus:sync`?
- ❓ ¿Es un comando de un paquete específico?
- ❓ ¿Necesita ser implementado?

### 4. Seguridad
- ✅ Cambiar el token en `DeploymentController` antes de usar
- ✅ Eliminar rutas de deployment después del despliegue
- ✅ No exponer tokens en logs o commits

---

## 🔍 Archivos Relacionados

- `app/Http/Controllers/DeploymentController.php` - Controlador de deployment
- `routes/web.php` (líneas 137-165) - Rutas temporales de deployment
- `deploy.sh` - Script de deployment (sin migraciones/seeders)
- `DEPLOYMENT-INSTRUCTIONS.md` - Instrucciones detalladas
- `DEPLOYMENT-REMOVAL.md` - Guía para eliminar código temporal

---

## ❓ Preguntas Pendientes

1. **factus:sync:** ¿Qué es este comando y dónde se debe ejecutar?
2. **Hostinger:** ¿Tiene acceso SSH o solo panel de control?
3. **Frecuencia:** ¿Con qué frecuencia se ejecutan migraciones/seeders?
4. **Automatización:** ¿Se necesita automatizar el proceso o es manual?

---

**Última actualización:** 2025-01-27


