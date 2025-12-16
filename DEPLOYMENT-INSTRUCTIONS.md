# 🚀 Instrucciones de Despliegue - MovilTech

## 📍 Contexto

Este proyecto Laravel está desplegado en un hosting compartido (Hostinger) sin acceso SSH. El proyecto vive en `/laravel` y `public_html/index.php` apunta correctamente a `/laravel`.

---

## ✅ Verificaciones Previas

### 1. Configuración de Base de Datos

Asegúrate de que tu archivo `.env` tenga la configuración correcta:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=moviltech
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 2. Tablas Requeridas

Las siguientes tablas se crearán automáticamente con las migraciones:

- ✅ `sessions` - Para almacenar sesiones de usuario
- ✅ `cache` y `cache_locks` - Para almacenar caché
- ✅ `jobs`, `job_batches`, `failed_jobs` - Para colas de trabajos
- ✅ Todas las tablas de la aplicación (users, products, sales, etc.)

---

## 🔐 Paso 1: Configurar Token de Seguridad

**IMPORTANTE:** Antes de usar las rutas de deployment, cambia el token de seguridad.

1. Abre el archivo: `app/Http/Controllers/DeploymentController.php`
2. Busca la línea:
   ```php
   private const DEPLOYMENT_TOKEN = 'CHANGE_THIS_TOKEN_IN_PRODUCTION';
   ```
3. Cámbiala por un token seguro (mínimo 32 caracteres):
   ```php
   private const DEPLOYMENT_TOKEN = 'TU_TOKEN_SUPER_SEGURO_AQUI_123456789';
   ```

**Ejemplo de token seguro:**
```php
private const DEPLOYMENT_TOKEN = 'MovilTech_2025_Deploy_Secure_Token_XYZ789';
```

---

## 🌐 Paso 2: Acceder al Dashboard de Deployment

1. Abre tu navegador y ve a:
   ```
   https://tudominio.com/__deploy__?token=TU_TOKEN_SUPER_SEGURO_AQUI_123456789
   ```

2. Reemplaza `TU_TOKEN_SUPER_SEGURO_AQUI_123456789` con el token que configuraste en el paso anterior.

3. Deberías ver el dashboard de deployment con:
   - Estado de las tablas de la base de datos
   - Botones para ejecutar migraciones
   - Botones para verificar el estado

---

## 🔄 Paso 3: Ejecutar Migraciones

### Opción A: Desde el Dashboard (Recomendado)

1. En el dashboard, haz clic en el botón **"🔄 Ejecutar Migraciones"**
2. Espera a que se complete la operación
3. Revisa los resultados en la sección "Resultados"

### Opción B: Directamente desde la URL

```
https://tudominio.com/__infra__/migrate?token=TU_TOKEN
```

---

## 📊 Paso 4: Verificar Estado

Para verificar el estado de las migraciones y tablas:

1. En el dashboard, haz clic en **"📊 Ver Estado"**
2. O visita directamente:
   ```
   https://tudominio.com/__infra__/status?token=TU_TOKEN
   ```

Esto mostrará:
- Migraciones ejecutadas
- Tablas existentes
- Conteo de registros por tabla

---

## 🌱 Paso 5: Ejecutar Seeders (Opcional)

**⚠️ ADVERTENCIA:** Los seeders crean datos de ejemplo. Solo ejecútalos si:
- Es un entorno de desarrollo/pruebas
- O necesitas datos iniciales y la base de datos está vacía

### Ejecutar todos los seeders:
```
https://tudominio.com/__infra__/seed?token=TU_TOKEN
```

### Ejecutar un seeder específico:
```
https://tudominio.com/__infra__/seed?token=TU_TOKEN&seeder=RoleSeeder
```

**Seeders disponibles:**
- `DatabaseSeeder` - Ejecuta todos los seeders
- `RoleSeeder` - Crea roles y permisos
- `UserSeeder` - Crea usuarios de ejemplo
- `CategorySeeder` - Crea categorías
- `ProductSeeder` - Crea productos
- `CustomerSeeder` - Crea clientes
- `SupplierSeeder` - Crea proveedores

---

## ✅ Paso 6: Verificar que Todo Funciona

1. Visita tu aplicación principal: `https://tudominio.com`
2. Inicia sesión con un usuario existente
3. Verifica que:
   - El dashboard carga correctamente
   - Las tablas muestran datos
   - No hay errores en los logs

---

## 🗑️ Paso 7: Eliminar Rutas Temporales (CRÍTICO)

**⚠️ MUY IMPORTANTE:** Después de completar el despliegue, elimina todas las rutas y archivos temporales.

Consulta el archivo `DEPLOYMENT-REMOVAL.md` para las instrucciones detalladas.

**Resumen rápido:**
1. Eliminar `app/Http/Controllers/DeploymentController.php`
2. Eliminar `resources/views/deployment/index.blade.php`
3. Eliminar las rutas temporales de `routes/web.php`
4. Verificar que las rutas ya no son accesibles

---

## 🔍 Solución de Problemas

### Error: "Invalid deployment token"
- Verifica que el token en la URL coincida con el configurado en `DeploymentController.php`
- Asegúrate de que no haya espacios extra en el token

### Error: "Migration failed"
- Revisa los logs en `storage/logs/laravel.log`
- Verifica que la base de datos tenga los permisos necesarios
- Asegúrate de que no haya migraciones que intenten eliminar columnas que ya no existen

### Las tablas no se crean
- Verifica la conexión a la base de datos en `.env`
- Asegúrate de que el usuario de la base de datos tenga permisos para crear tablas
- Revisa que las migraciones de Laravel estén presentes en `database/migrations/`

### Error 403 o 404 al acceder a las rutas
- Verifica que las rutas estén correctamente agregadas en `routes/web.php`
- Asegúrate de que el archivo `public_html/index.php` apunte correctamente a `/laravel`
- Limpia la caché de rutas (si es posible): `php artisan route:clear`

---

## 📝 Notas Importantes

### Seguridad de Datos
- ✅ Las migraciones **NO eliminan** datos existentes
- ✅ Las migraciones solo **agregan** nuevas tablas o columnas
- ✅ Los seeders **NO usan** `truncate()` ni `delete()`
- ✅ Los seeders solo **crean** nuevos registros

### Migraciones que Eliminan Columnas
Las siguientes migraciones eliminan columnas, pero ya deberían estar ejecutadas:
- `2025_09_02_153038_update_customers_table_structure.php` - Elimina `identification` y `type`
- `2025_09_02_150701_remove_image_and_description_from_products_table.php` - Elimina `image` y `description`
- `2025_08_21_140457_remove_supplier_id_from_products_table.php` - Elimina `supplier_id`

Si estas migraciones ya se ejecutaron anteriormente, Laravel las omitirá automáticamente.

### Tablas de Sistema
Las siguientes tablas se crean automáticamente:
- `migrations` - Registro de migraciones ejecutadas
- `sessions` - Sesiones de usuario (si `SESSION_DRIVER=database`)
- `cache` y `cache_locks` - Caché (si `CACHE_STORE=database`)
- `jobs`, `job_batches`, `failed_jobs` - Colas (si `QUEUE_CONNECTION=database`)

---

## 📞 Soporte

Si encuentras problemas durante el despliegue:
1. Revisa los logs en `storage/logs/laravel.log`
2. Verifica la configuración de la base de datos
3. Asegúrate de que todas las dependencias estén instaladas

---

**Última actualización:** 2025-12-14

