# Guía para Desplegar Cambios en Producción (Hostinger)

Esta guía te ayudará a ejecutar migraciones, seeders y comandos de sincronización en producción **sin afectar los datos existentes**.

## ⚠️ IMPORTANTE: Hacer Backup Primero

**ANTES DE HACER CUALQUIER CAMBIO**, crea un backup completo de la base de datos:

```bash
# Si tienes acceso SSH a Hostinger
mysqldump -u tu_usuario -p nombre_base_datos > backup_$(date +%Y%m%d_%H%M%S).sql

# O desde el panel de Hostinger (phpMyAdmin)
# Exportar la base de datos completa
```

## 1. Verificar Estado de Migraciones

Primero, verifica qué migraciones ya están ejecutadas y cuáles faltan:

```bash
php artisan migrate:status
```

Este comando te mostrará:
- ✅ Migraciones que ya están ejecutadas
- ⏳ Migraciones pendientes

## 2. Ejecutar Solo Migraciones Pendientes

Laravel **solo ejecuta migraciones nuevas** que no han sido ejecutadas antes. Para ejecutar solo las pendientes:

```bash
php artisan migrate
```

Esto es seguro porque:
- Laravel registra las migraciones ejecutadas en la tabla `migrations`
- Solo ejecuta las que no están registradas
- No afecta datos existentes, solo agrega nuevas columnas/tablas si es necesario

### ⚠️ Migraciones que Modifican Columnas Existentes

Si alguna migración modifica columnas existentes (ej: cambiar tipo de dato), **revisa primero**:

```bash
# Ver el contenido de la migración antes de ejecutar
cat database/migrations/NOMBRE_MIGRACION.php
```

Si la migración usa `->change()` o `->nullable()`, puede ser segura. Si usa `->dropColumn()`, **¡CUIDADO!** Puede eliminar datos.

## 3. Ejecutar Seeders de Forma Segura

Los seeders de catálogos DIAN están diseñados para ser **idempotentes** (pueden ejecutarse múltiples veces sin problemas):

### 3.1 Seeders Seguros (Usan `updateOrInsert` o `updateOrCreate`)

Estos seeders son seguros porque **actualizan o crean** registros sin duplicar:

```bash
# Seeders de catálogos DIAN (SEGUROS - no duplican datos)
php artisan db:seed --class=DianIdentificationDocumentSeeder
php artisan db:seed --class=DianLegalOrganizationSeeder
php artisan db:seed --class=DianCustomerTributeSeeder
php artisan db:seed --class=DianDocumentTypeSeeder
php artisan db:seed --class=DianOperationTypeSeeder
php artisan db:seed --class=DianPaymentMethodSeeder
php artisan db:seed --class=DianPaymentFormSeeder
php artisan db:seed --class=DianProductStandardSeeder
```

**Cómo funcionan:**
- Usan `updateOrInsert(['id' => X], [...])` o `updateOrCreate(['code' => 'X'], [...])`
- Si el registro existe (por ID o código), lo actualiza
- Si no existe, lo crea
- **No duplica datos**

### 3.2 Seeders que DEBES EJECUTAR SOLO UNA VEZ

Estos seeders pueden crear datos duplicados:

```bash
# ⚠️ SOLO ejecutar si NO tienes estos datos en producción
php artisan db:seed --class=RoleSeeder        # Solo si necesitas roles
php artisan db:seed --class=UserSeeder        # ⚠️ Creará usuarios de prueba
php artisan db:seed --class=CategorySeeder    # ⚠️ Creará categorías de prueba
php artisan db:seed --class=ProductSeeder     # ⚠️ Creará productos de prueba
php artisan db:seed --class=CustomerSeeder    # ⚠️ Creará clientes de prueba
php artisan db:seed --class=SupplierSeeder    # ⚠️ Creará proveedores de prueba
```

**Recomendación:** NO ejecutes estos en producción a menos que sea absolutamente necesario.

### 3.3 Ejecutar Todos los Seeders Seguros a la Vez

Si quieres ejecutar solo los seeders de catálogos DIAN:

```bash
# Ejecutar solo seeders de catálogos DIAN
php artisan db:seed --class=DianIdentificationDocumentSeeder
php artisan db:seed --class=DianLegalOrganizationSeeder
php artisan db:seed --class=DianCustomerTributeSeeder
php artisan db:seed --class=DianDocumentTypeSeeder
php artisan db:seed --class=DianOperationTypeSeeder
php artisan db:seed --class=DianPaymentMethodSeeder
php artisan db:seed --class=DianPaymentFormSeeder
php artisan db:seed --class=DianProductStandardSeeder
```

## 4. Sincronizar Datos desde Factus

Los comandos de sincronización de Factus son **completamente seguros** porque usan `updateOrCreate` basado en `factus_id`.

### 4.1 Sincronizar Municipios

```bash
php artisan factus:sync-municipalities
```

**Seguro porque:**
- Usa `updateOrCreate(['factus_id' => X], [...])`
- Si el municipio ya existe, lo actualiza con la última información de Factus
- Si no existe, lo crea
- **No duplica datos**

### 4.2 Sincronizar Rangos de Numeración

```bash
php artisan factus:sync-numbering-ranges
```

**Seguro porque:**
- Usa `updateOrCreate(['factus_id' => X], [...])`
- Actualiza el estado actual de los rangos (current, is_active, etc.)
- Recomendado ejecutarlo periódicamente para mantener datos actualizados

### 4.3 Sincronizar Unidades de Medida

```bash
php artisan factus:sync-measurement-units
```

**Seguro porque:**
- Usa `updateOrCreate(['factus_id' => X], [...])`
- Sincroniza todas las unidades de medida desde Factus

## 5. Orden Recomendado de Ejecución

Sigue este orden para desplegar cambios de forma segura:

```bash
# 1. Verificar estado
php artisan migrate:status

# 2. Ejecutar migraciones pendientes
php artisan migrate

# 3. Ejecutar seeders de catálogos DIAN (seguros)
php artisan db:seed --class=DianIdentificationDocumentSeeder
php artisan db:seed --class=DianLegalOrganizationSeeder
php artisan db:seed --class=DianCustomerTributeSeeder
php artisan db:seed --class=DianDocumentTypeSeeder
php artisan db:seed --class=DianOperationTypeSeeder
php artisan db:seed --class=DianPaymentMethodSeeder
php artisan db:seed --class=DianPaymentFormSeeder
php artisan db:seed --class=DianProductStandardSeeder

# 4. Sincronizar datos desde Factus
php artisan factus:sync-municipalities
php artisan factus:sync-numbering-ranges
php artisan factus:sync-measurement-units

# 5. Verificar que todo esté correcto
php artisan migrate:status
```

## 6. Verificación Post-Deploy

Después de ejecutar los comandos, verifica que todo esté correcto:

### 6.1 Verificar Migraciones

```bash
php artisan migrate:status
# Todas deberían mostrar "Ran"
```

### 6.2 Verificar Catálogos DIAN

```bash
# Verificar que los catálogos tengan datos
php artisan tinker
```

Dentro de tinker:
```php
// Verificar tipos de documentos
\App\Models\DianIdentificationDocument::count();

// Verificar métodos de pago
\App\Models\DianPaymentMethod::count();

// Verificar formas de pago
\App\Models\DianPaymentForm::count();

// Verificar municipios
\App\Models\DianMunicipality::count();
```

### 6.3 Verificar Datos Existentes

```bash
php artisan tinker
```

```php
// Verificar que los clientes existentes siguen intactos
\App\Models\Customer::count();
\App\Models\Customer::with('taxProfile')->first();

// Verificar que las ventas existentes siguen intactas
\App\Models\Sale::count();
\App\Models\Sale::first();

// Verificar productos
\App\Models\Product::count();
```

## 7. Troubleshooting

### Error: "SQLSTATE[42S21]: Column already exists"

Esto significa que la migración ya se ejecutó antes. Es normal, puedes ignorarlo o usar:

```bash
php artisan migrate --force
```

### Error: "Duplicate entry"

Si un seeder intenta crear un registro duplicado, significa que el seeder NO está usando `updateOrInsert`. Revisa el seeder y modifícalo para usar `updateOrInsert` o `updateOrCreate`.

### Error: "Table doesn't exist"

La tabla no existe. Ejecuta las migraciones primero:

```bash
php artisan migrate
```

### Datos Faltantes en Catálogos

Si después de ejecutar los seeders, algunos catálogos no tienen datos:

```bash
# Verificar qué seeder falta
php artisan db:seed --class=NOMBRE_SEEDER

# O ejecutar todos los seeders de catálogos DIAN
php artisan db:seed --class=DianIdentificationDocumentSeeder
# ... (resto de seeders)
```

## 8. Script de Deploy Completo

Puedes crear un script para automatizar el proceso:

```bash
#!/bin/bash
# deploy-production.sh

echo "🚀 Iniciando deploy en producción..."
echo ""

echo "1️⃣ Verificando estado de migraciones..."
php artisan migrate:status

echo ""
echo "2️⃣ Ejecutando migraciones pendientes..."
php artisan migrate --force

echo ""
echo "3️⃣ Ejecutando seeders de catálogos DIAN..."
php artisan db:seed --class=DianIdentificationDocumentSeeder
php artisan db:seed --class=DianLegalOrganizationSeeder
php artisan db:seed --class=DianCustomerTributeSeeder
php artisan db:seed --class=DianDocumentTypeSeeder
php artisan db:seed --class=DianOperationTypeSeeder
php artisan db:seed --class=DianPaymentMethodSeeder
php artisan db:seed --class=DianPaymentFormSeeder
php artisan db:seed --class=DianProductStandardSeeder

echo ""
echo "4️⃣ Sincronizando datos desde Factus..."
php artisan factus:sync-municipalities
php artisan factus:sync-numbering-ranges
php artisan factus:sync-measurement-units

echo ""
echo "✅ Deploy completado!"
echo ""
echo "5️⃣ Verificando estado final..."
php artisan migrate:status
```

Guarda este script como `deploy-production.sh`, dale permisos de ejecución y ejecútalo:

```bash
chmod +x deploy-production.sh
./deploy-production.sh
```

## 9. Notas Importantes

1. **Siempre hacer backup** antes de cualquier cambio
2. **Probar primero en staging** si es posible
3. **Ejecutar migraciones en horas de bajo tráfico** si es posible
4. Los comandos de sincronización de Factus pueden ejecutarse múltiples veces sin problemas
5. Los seeders de catálogos DIAN pueden ejecutarse múltiples veces sin problemas
6. **NO ejecutar seeders de datos de prueba** (UserSeeder, ProductSeeder, etc.) en producción

## 10. Contacto y Soporte

Si encuentras problemas durante el deploy:
1. Revisa los logs: `storage/logs/laravel.log`
2. Verifica el estado de las migraciones: `php artisan migrate:status`
3. Revisa la base de datos directamente si es necesario
