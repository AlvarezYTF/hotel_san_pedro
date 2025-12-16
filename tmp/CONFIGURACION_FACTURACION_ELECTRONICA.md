# Configuración de Facturación Electrónica DIAN

Esta guía explica cómo configurar el sistema de facturación electrónica DIAN en MovilTech.

## 📋 Requisitos Previos

1. **Credenciales de Factus**: Debes tener acceso a la plataforma Factus (sandbox o producción)
2. **Configuración de Empresa**: Datos fiscales completos de tu empresa
3. **Rangos de Numeración**: Rangos de facturación configurados en Factus

## 🔧 Configuración Paso a Paso

### 1. Variables de Entorno

Edita el archivo `.env` y agrega las siguientes variables:

```env
# Factus API - URLs
# Para pruebas (Sandbox)
FACTUS_API_URL=https://api-sandbox.factus.com.co

# Para producción (descomentar cuando estés listo)
# FACTUS_API_URL=https://api.factus.com.co

# Credenciales OAuth2 de Factus (obtener del administrador de Factus)
FACTUS_CLIENT_ID=tu_client_id_aqui
FACTUS_CLIENT_SECRET=tu_client_secret_aqui
FACTUS_USERNAME=tu_username_aqui
FACTUS_PASSWORD=tu_password_aqui
```

**⚠️ Importante**: 
- Las credenciales son suministradas por Factus al dar acceso al sistema
- Contacta al administrador de la API para obtenerlas
- En producción, nunca compartas estas credenciales

### 2. Ejecutar Migraciones

Ejecuta todas las migraciones para crear las tablas necesarias:

```bash
php artisan migrate
```

Esto creará las siguientes tablas:
- `dian_identification_documents` - Tipos de documentos de identidad
- `dian_legal_organizations` - Tipos de organización legal
- `dian_customer_tributes` - Régimenes tributarios
- `dian_municipalities` - Municipios (sincronizados desde Factus)
- `dian_measurement_units` - Unidades de medida (sincronizadas desde Factus)
- `dian_document_types` - Tipos de documentos electrónicos
- `dian_operation_types` - Tipos de operación
- `dian_payment_methods` - Métodos de pago
- `dian_payment_forms` - Formas de pago
- `dian_product_standards` - Estándares de identificación de productos
- `factus_numbering_ranges` - Rangos de numeración (sincronizados desde Factus)
- `company_tax_settings` - Configuración fiscal de la empresa
- `customer_tax_profiles` - Perfiles fiscales de clientes
- `electronic_invoices` - Facturas electrónicas
- `electronic_invoice_items` - Items de facturas electrónicas

### 3. Ejecutar Seeders

⚠️ **IMPORTANTE**: Para producción, consulta primero `tmp/GUIA_DEPLOY_PRODUCCION_HOSTINGER.md` para ejecutar de forma segura sin afectar datos existentes.

Para desarrollo local, ejecuta los seeders para poblar los catálogos DIAN:

```bash
php artisan db:seed
```

O ejecuta seeders específicos:

```bash
php artisan db:seed --class=DianIdentificationDocumentSeeder
php artisan db:seed --class=DianLegalOrganizationSeeder
php artisan db:seed --class=DianCustomerTributeSeeder
php artisan db:seed --class=DianDocumentTypeSeeder
php artisan db:seed --class=DianOperationTypeSeeder
php artisan db:seed --class=DianPaymentMethodSeeder
php artisan db:seed --class=DianPaymentFormSeeder
php artisan db:seed --class=DianProductStandardSeeder
```

### 4. Sincronizar Datos desde Factus

⚠️ **IMPORTANTE**: Los comandos de sincronización son seguros porque usan `updateOrCreate`. Pueden ejecutarse múltiples veces sin problemas.

Los siguientes datos deben sincronizarse desde la API de Factus:

#### 4.1 Sincronizar Municipios

```bash
php artisan factus:sync-municipalities
```

Este comando:
- Obtiene todos los municipios desde Factus
- Los almacena en la tabla `dian_municipalities`
- Usa `factus_id` como identificador único

#### 4.2 Sincronizar Rangos de Numeración

```bash
php artisan factus:sync-numbering-ranges
```

Este comando:
- Obtiene los rangos de numeración activos desde Factus
- Los almacena en la tabla `factus_numbering_ranges`
- Incluye información sobre prefijos, rangos, estado actual, etc.

**⚠️ Importante**: Los rangos se actualizan dinámicamente. Recomendamos ejecutar este comando:
- Diariamente (mediante job programado)
- Antes de generar facturas
- Cuando se active/desactive un rango en Factus

#### 4.3 Sincronizar Unidades de Medida

```bash
php artisan factus:sync-measurement-units
```

Este comando:
- Obtiene todas las unidades de medida desde Factus
- Las almacena en la tabla `dian_measurement_units`
- Usa `factus_id` como identificador único

### 5. Configurar Datos Fiscales de la Empresa

Debes configurar los datos fiscales de tu empresa en la tabla `company_tax_settings`.

**Opción A: Directamente en la base de datos**

```sql
INSERT INTO company_tax_settings (
    company_name,
    nit,
    dv,
    email,
    municipality_id,
    economic_activity,
    created_at,
    updated_at
) VALUES (
    'Nombre de tu Empresa',
    '123456789',  -- NIT sin DV
    '0',  -- Dígito verificador
    'contacto@empresa.com',
    123,  -- factus_id del municipio (obtener de dian_municipalities)
    'Código CIIU de actividad económica',
    NOW(),
    NOW()
);
```

**Opción B: Usar la interfaz administrativa** (recomendado)

La interfaz administrativa está disponible en `/company-tax-settings/edit` para gestionar estos datos desde la aplicación.

**Acceso:**
- Requiere el permiso `manage_roles`
- Disponible en el menú lateral bajo "Administración" → "Configuración Fiscal"
- O accede directamente a: `/company-tax-settings/edit`

**Características de la interfaz:**
- ✅ Indicador de estado de configuración (completa/incompleta)
- ✅ Lista de campos faltantes si la configuración está incompleta
- ✅ Validación en tiempo real
- ✅ Selector de municipios agrupado por departamento
- ✅ Información del sistema (ID Factus, municipio configurado)

### 6. Verificar Configuración

Para verificar que todo está configurado correctamente:

```bash
php artisan tinker
```

Luego ejecuta:

```php
// Verificar configuración de empresa
$company = \App\Models\CompanyTaxSetting::getInstance();
if ($company && $company->isConfigured()) {
    echo "✓ Configuración de empresa OK\n";
} else {
    echo "✗ Configuración de empresa incompleta\n";
}

// Verificar municipios sincronizados
$municipalitiesCount = \App\Models\DianMunicipality::count();
echo "Municipios sincronizados: {$municipalitiesCount}\n";

// Verificar rangos de numeración
$rangesCount = \App\Models\FactusNumberingRange::where('is_active', true)->count();
echo "Rangos activos: {$rangesCount}\n";

// Verificar unidades de medida
$unitsCount = \App\Models\DianMeasurementUnit::count();
echo "Unidades de medida: {$unitsCount}\n";
```

## 🔄 Mantenimiento

### Sincronización Automática de Rangos de Numeración

Para mantener los rangos actualizados automáticamente, puedes programar un job diario.

Edita `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Sincronizar rangos de numeración diariamente a las 2 AM
    $schedule->command('factus:sync-numbering-ranges')
             ->dailyAt('02:00');
}
```

### Verificar Token de Factus

El sistema maneja automáticamente la renovación de tokens OAuth2. Los tokens se almacenan en caché y se renuevan automáticamente cuando están próximos a expirar.

Si necesitas verificar el estado del token:

```php
$apiService = app(\App\Services\FactusApiService::class);
$token = $apiService->getAuthToken();
echo "Token obtenido: " . substr($token, 0, 20) . "...\n";
```

## 📝 Uso del Sistema

### Configurar Cliente para Facturación Electrónica

1. Edita o crea un cliente
2. Activa el checkbox "Facturación Electrónica DIAN"
3. Completa los campos obligatorios:
   - Tipo de Documento
   - Número de Identificación
   - Dígito Verificador (si aplica)
   - Municipio
   - Razón Social (si es persona jurídica)

### Generar Factura Electrónica desde una Venta

1. Crea o visualiza una venta
2. Si el cliente requiere facturación electrónica, verás el botón "Generar Factura Electrónica"
3. Haz clic en el botón
4. El sistema:
   - Validará que todos los datos necesarios estén completos
   - Creará la factura electrónica
   - La enviará a Factus para validación
   - Guardará el CUFE y QR si es aceptada

### Ver Factura Electrónica

1. En la vista de una venta que tiene factura electrónica, verás el botón "Ver Factura Electrónica"
2. Puedes descargar el PDF si está disponible
3. Puedes ver el CUFE, QR y todos los detalles

## ⚠️ Solución de Problemas

### Error: "No hay un rango de numeración válido disponible"

**Solución**: 
1. Sincroniza los rangos: `php artisan factus:sync-numbering-ranges`
2. Verifica que hay rangos activos en `factus_numbering_ranges`
3. Asegúrate de que el tipo de documento coincide

### Error: "Error al autenticar con Factus OAuth2"

**Solución**:
1. Verifica las credenciales en `.env`
2. Verifica que el `FACTUS_API_URL` sea correcto (sandbox o producción)
3. Contacta con Factus si el problema persiste

### Error: "El cliente no tiene datos fiscales completos"

**Solución**:
1. Edita el cliente y activa "Facturación Electrónica DIAN"
2. Completa todos los campos obligatorios marcados con *
3. Asegúrate de seleccionar un municipio válido

### Error: "La configuración fiscal de la empresa no está completa"

**Solución**:
1. Verifica que existe un registro en `company_tax_settings`
2. Verifica que tiene: NIT, DV, email, municipality_id
3. Verifica que el municipality_id existe en `dian_municipalities`

## 📚 Referencias

- Documentación completa: `tmp/ANALISIS_FACTURACION_ELECTRONICA_DIAN.md`
- Factus API: https://api-sandbox.factus.com.co/docs
- Normativa DIAN: https://www.dian.gov.co

## 🆘 Soporte

Si encuentras problemas:
1. Revisa los logs en `storage/logs/laravel.log`
2. Verifica que todas las migraciones y seeders se ejecutaron correctamente
3. Verifica la configuración de `.env`
4. Consulta la documentación en `tmp/ANALISIS_FACTURACION_ELECTRONICA_DIAN.md`

