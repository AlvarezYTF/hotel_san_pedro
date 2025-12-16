# Instrucciones para Ejecutar Seeders en Producción (Hostinger)

## ⚠️ IMPORTANTE
Todos los seeders han sido modificados para ser **seguros en producción**. Ahora usan `firstOrCreate` o `updateOrInsert`, lo que significa que **NO duplicarán datos existentes**.

## 🟢 Opción 1: Ejecutar Solo Seeders Seguros (Recomendado)

Esta opción ejecuta únicamente los seeders de catálogos DIAN que son completamente seguros:

```bash
php artisan db:seed --class=ProductionSeeder
```

**Esto ejecutará:**
- ✅ DianIdentificationDocumentSeeder
- ✅ DianLegalOrganizationSeeder
- ✅ DianCustomerTributeSeeder
- ✅ DianDocumentTypeSeeder
- ✅ DianOperationTypeSeeder
- ✅ DianPaymentMethodSeeder
- ✅ DianPaymentFormSeeder
- ✅ DianProductStandardSeeder

**NO ejecutará:**
- ❌ RoleSeeder
- ❌ UserSeeder
- ❌ CategorySeeder
- ❌ ProductSeeder
- ❌ CustomerSeeder
- ❌ SupplierSeeder

## 🟡 Opción 2: Ejecutar Seeders Individuales (Ahora Seguros)

Si necesitas ejecutar seeders específicos, ahora son seguros y no duplicarán datos:

```bash
# Catálogos DIAN (siempre seguros)
php artisan db:seed --class=DianIdentificationDocumentSeeder
php artisan db:seed --class=DianLegalOrganizationSeeder
php artisan db:seed --class=DianCustomerTributeSeeder
php artisan db:seed --class=DianDocumentTypeSeeder
php artisan db:seed --class=DianOperationTypeSeeder
php artisan db:seed --class=DianPaymentMethodSeeder
php artisan db:seed --class=DianPaymentFormSeeder
php artisan db:seed --class=DianProductStandardSeeder

# Datos de negocio (ahora seguros - no duplican)
php artisan db:seed --class=RoleSeeder          # Solo crea si no existen
php artisan db:seed --class=UserSeeder          # Solo crea usuarios si no existen
php artisan db:seed --class=CategorySeeder      # Solo crea categorías si no existen
php artisan db:seed --class=ProductSeeder       # Solo crea productos si no existen (por SKU)
php artisan db:seed --class=CustomerSeeder      # Solo crea clientes si no existen (por email)
php artisan db:seed --class=SupplierSeeder     # Solo crea proveedores si no existen (por email)
```

## 🔵 Opción 3: Ejecutar Todos los Seeders (Ahora Seguro)

Ahora puedes ejecutar todos los seeders sin riesgo de duplicar datos:

```bash
php artisan db:seed
```

**Comportamiento:**
- Los catálogos DIAN se actualizarán si existen o se crearán si no existen
- Los roles se crearán solo si no existen
- Los usuarios de prueba se crearán solo si no existen (por email)
- Las categorías se crearán solo si no existen (por nombre)
- Los productos se crearán solo si no existen (por SKU)
- Los clientes se crearán solo si no existen (por email o nombre)
- Los proveedores se crearán solo si no existen (por email)

## 📋 Cambios Realizados para Seguridad

### Seeders Modificados:

1. **RoleSeeder**: Usa `firstOrCreate` para roles y permisos
2. **UserSeeder**: Usa `firstOrCreate` por email (no duplica usuarios)
3. **CategorySeeder**: Usa `firstOrCreate` por nombre (no duplica categorías)
4. **ProductSeeder**: Usa `firstOrCreate` por SKU (no duplica productos)
5. **CustomerSeeder**: Usa `firstOrCreate` por email o nombre (no duplica clientes)
6. **SupplierSeeder**: Usa `firstOrCreate` por email (no duplica proveedores)

### Seeders Ya Seguros (sin cambios):

- Todos los seeders DIAN ya usaban `updateOrInsert` desde el inicio

## 🚀 Comando Recomendado para Producción

```bash
# Ejecutar solo seeders de catálogos DIAN (más seguro)
php artisan db:seed --class=ProductionSeeder
```

Este comando es el más seguro porque solo ejecuta los catálogos DIAN que son necesarios para la facturación electrónica y no toca datos de negocio existentes.

## ⚠️ Notas Importantes

1. **Backup**: Aunque los seeders son seguros, siempre es recomendable hacer un backup antes de ejecutar seeders en producción:
   ```bash
   # Si tienes acceso a mysqldump
   mysqldump -u usuario -p nombre_base_datos > backup_antes_seeders.sql
   ```

2. **Usuarios**: El UserSeeder creará usuarios de prueba solo si no existen. Si ya tienes usuarios con esos emails, no se crearán duplicados.

3. **Datos de Prueba**: Los seeders de ProductSeeder, CustomerSeeder, etc. crean datos de prueba. Si ya tienes datos reales en producción, estos seeders solo agregarán los datos de prueba que no existan.

4. **Catálogos DIAN**: Los catálogos DIAN son necesarios para la facturación electrónica. Es seguro ejecutarlos múltiples veces.

## 📞 Soporte

Si tienes dudas o problemas, verifica:
- Los logs de Laravel: `storage/logs/laravel.log`
- Los mensajes de error en la consola
- El estado de la base de datos después de ejecutar los seeders

