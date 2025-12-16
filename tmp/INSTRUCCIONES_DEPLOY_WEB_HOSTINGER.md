# 🚀 Instrucciones para Deploy desde el Navegador (Hostinger Compartido)

Como no puedes ejecutar comandos Artisan directamente en Hostinger (hosting compartido), puedes usar la interfaz web de deployment.

## ⚠️ IMPORTANTE: Configurar Token de Seguridad

**ANTES DE USAR**, debes cambiar el token de seguridad:

1. Edita `app/Http/Controllers/DeploymentController.php`
2. Busca la línea:
   ```php
   private const DEPLOYMENT_TOKEN = 'CHANGE_THIS_TOKEN_IN_PRODUCTION';
   ```
3. Cámbiala por un token seguro (al menos 32 caracteres aleatorios):
   ```php
   private const DEPLOYMENT_TOKEN = 'TU_TOKEN_SUPER_SEGURO_AQUI_123456789';
   ```

**Ejemplo de token seguro:**
```php
private const DEPLOYMENT_TOKEN = 'DeployMovilTech2025!SecretKey#XYZ789';
```

## 📍 Cómo Acceder

Una vez configurado el token, accede a:

```
https://tu-dominio.com/__deploy__?token=TU_TOKEN_SUPER_SEGURO_AQUI_123456789
```

**⚠️ IMPORTANTE**: Reemplaza `TU_TOKEN_SUPER_SEGURO_AQUI_123456789` con el token que configuraste en el controlador.

## 🎯 Funcionalidades Disponibles

### 1. Ver Estado de Migraciones

La página muestra:
- Total de migraciones
- Migraciones ejecutadas
- Migraciones pendientes
- Lista de migraciones pendientes

### 2. Ejecutar Migraciones

1. Haz clic en el botón **"🔄 Ejecutar Migraciones Pendientes"**
2. Confirma la acción
3. Solo se ejecutarán las migraciones que **NO** han sido ejecutadas antes
4. **Es seguro** - no afecta datos existentes

### 3. Ejecutar Seeders de Catálogos DIAN

Hay 8 botones para ejecutar seeders específicos:

- 📄 **Documentos de Identificación** - `DianIdentificationDocumentSeeder`
- 🏢 **Organizaciones Legales** - `DianLegalOrganizationSeeder`
- 💰 **Tributos de Cliente** - `DianCustomerTributeSeeder`
- 📋 **Tipos de Documento** - `DianDocumentTypeSeeder`
- ⚙️ **Tipos de Operación** - `DianOperationTypeSeeder`
- 💳 **Métodos de Pago** - `DianPaymentMethodSeeder`
- 💵 **Formas de Pago** - `DianPaymentFormSeeder`
- 📦 **Estándares de Producto** - `DianProductStandardSeeder`

**Todos estos seeders son seguros** porque usan `updateOrInsert` o `updateOrCreate`. Pueden ejecutarse múltiples veces sin duplicar datos.

### 4. Sincronizar Datos desde Factus

Hay 3 botones para sincronizar:

- 🏘️ **Sincronizar Municipios** - `php artisan factus:sync-municipalities`
- 🔢 **Sincronizar Rangos de Numeración** - `php artisan factus:sync-numbering-ranges`
- 📏 **Sincronizar Unidades de Medida** - `php artisan factus:sync-measurement-units`

**Todos estos comandos son seguros** porque usan `updateOrCreate` basado en `factus_id`. Pueden ejecutarse múltiples veces.

### 5. Ver Estado Completo

Haz clic en **"📊 Ver Estado Completo"** para ver un JSON con toda la información del estado actual.

## 📋 Orden Recomendado de Ejecución

Sigue este orden para desplegar cambios de forma segura:

1. **Verificar Estado**
   - La página carga automáticamente el estado al abrir
   - Revisa cuántas migraciones están pendientes

2. **Ejecutar Migraciones Pendientes**
   - Haz clic en "🔄 Ejecutar Migraciones Pendientes"
   - Confirma la acción

3. **Ejecutar Seeders de Catálogos DIAN**
   - Ejecuta todos los 8 seeders haciendo clic en cada botón
   - Puedes ejecutarlos en cualquier orden
   - Puedes ejecutarlos múltiples veces sin problemas

4. **Sincronizar desde Factus**
   - Haz clic en "🏘️ Sincronizar Municipios"
   - Haz clic en "🔢 Sincronizar Rangos de Numeración"
   - Haz clic en "📏 Sincronizar Unidades de Medida"

5. **Verificar Resultado**
   - La página se recarga automáticamente después de cada acción exitosa
   - Revisa los contadores de catálogos para verificar que los datos se cargaron

## 🔒 Seguridad

- **Token requerido**: Sin el token correcto, no puedes acceder a las rutas
- **Solo seeders seguros**: El sistema solo permite ejecutar seeders de catálogos DIAN que usan `updateOrInsert`
- **Confirmación requerida**: Cada acción requiere confirmación en el navegador
- **Logging**: Todas las acciones se registran en `storage/logs/laravel.log`

## ⚠️ Después del Deploy

**IMPORTANTE**: Después de completar el deploy, deberías:

1. **Eliminar las rutas de deployment** (recomendado por seguridad)
2. O **cambiar el token** regularmente
3. O **eliminar el controlador y las rutas** si ya no los necesitas

Consulta `DEPLOYMENT-REMOVAL.md` para ver cómo eliminar todo después del deploy.

## 🐛 Troubleshooting

### Error 403: Invalid deployment token

- Verifica que el token en la URL coincida exactamente con el token en `DeploymentController.php`
- El token es case-sensitive (distingue mayúsculas y minúsculas)

### Error al ejecutar migraciones

- Revisa `storage/logs/laravel.log` para ver el error completo
- Verifica que la base de datos esté accesible
- Verifica permisos de escritura en la base de datos

### Los seeders no funcionan

- Verifica que los modelos existan
- Revisa `storage/logs/laravel.log` para ver el error
- Asegúrate de que las tablas existan (ejecuta migraciones primero)

### Error al sincronizar desde Factus

- Verifica las credenciales de Factus en `.env`
- Verifica que `FACTUS_API_URL` esté correcto
- Revisa `storage/logs/laravel.log` para ver el error completo

## 📚 Referencias

- Guía completa de deploy: `tmp/GUIA_DEPLOY_PRODUCCION_HOSTINGER.md`
- Documentación de configuración: `tmp/CONFIGURACION_FACTURACION_ELECTRONICA.md`
