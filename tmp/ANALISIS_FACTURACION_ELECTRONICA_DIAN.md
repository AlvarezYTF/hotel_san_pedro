# ANÁLISIS Y DISEÑO: INTEGRACIÓN FACTURACIÓN ELECTRÓNICA DIAN

**Proyecto:** MovilTech - Sistema de Gestión  
**Fecha:** 2025-01-XX  
**Arquitecto de Software:** Análisis Técnico Completo

---

## 🎯 PRINCIPIO ARQUITECTÓNICO FUNDAMENTAL

### **GRUPO A — Tabla `customers` (Estructura Simple)**

La tabla `customers` debe mantenerse **simple y limpia**, conteniendo únicamente:

#### ✅ **Campos Permitidos en `customers`:**
- `id` (PK)
- `name` / `names` (Nombre completo del cliente)
- `email` (Email de contacto - opcional)
- `phone` (Teléfono - opcional)
- `address` (Dirección comercial - opcional)
- `city` (Ciudad - opcional)
- `state` (Estado/Departamento - opcional)
- `zip_code` (Código postal - opcional)
- `notes` (Notas adicionales - opcional)
- `is_active` (Estado activo/inactivo)
- `requires_electronic_invoice` (boolean, default: false) ⭐ **ÚNICO flag relacionado con facturación**
- `created_at` / `updated_at` (timestamps)

#### ❌ **Campos PROHIBIDOS en `customers`:**
- ❌ **NO** debe tener campos DIAN obligatorios
- ❌ **NO** debe tener `identification`, `dv`, `document_type`
- ❌ **NO** debe tener campos fiscales como `tribute`, `municipality_code`, etc.
- ❌ **NO** debe tener campos específicos de facturación electrónica

#### 📋 **Regla de Oro:**
> **"La tabla `customers` es para clientes normales. NO todos los clientes facturan electrónicamente. Los datos DIAN van en una tabla relacionada separada."**

---

### **GRUPO B — Facturación Electrónica (CRÍTICO)**

#### **3️⃣ Tabla `electronic_invoices` (EL CORAZÓN DEL SISTEMA)**

Esta tabla es **CRÍTICA** y almacena toda la información de facturación electrónica DIAN:

#### ✅ **Campos Mínimos Recomendados:**
- `id` (PK)
- `sale_id` (FK a sales) - o `order_id` según tu modelo
- `customer_id` (FK a customers)
- `factus_numbering_range_id` (FK a factus_numbering_ranges - usa factus_id)
- `document_type_id` (FK a dian_document_types) ⭐ **Desde catálogo**
- `operation_type_id` (FK a dian_operation_types) ⭐ **Desde catálogo**
- `payment_method_code` (FK a dian_payment_methods.code) ⭐ **Desde catálogo**
- `payment_form_code` (FK a dian_payment_forms.code) ⭐ **Desde catálogo**
- `reference_code` (Código de referencia único)
- `document` (Número de documento/factura)
- `status` (Estado: pending, sent, accepted, rejected, cancelled)
- `cufe` (CUFE - Código Único de Facturación Electrónica)
- `qr` (Código QR para validación)
- `total` (Total de la factura)
- `tax_amount` (Valor de impuestos)
- `gross_value` (Valor bruto)
- `discount_amount` (Descuentos)
- `surcharge_amount` (Recargos)
- `validated_at` (Fecha de validación DIAN)
- `payload_sent` (JSON) - Lo que se envió a Factus/DIAN
- `response_dian` (JSON) - Respuesta completa de DIAN
- `pdf_url` (URL del PDF generado)
- `xml_url` (URL del XML generado)
- `created_at` / `updated_at` (timestamps)

#### 📌 **Propósito:**
- Guarda **lo que enviaste** a Factus/DIAN
- Guarda **lo que Factus/DIAN respondió**
- Guarda el **estado actual** de la factura
- Permite auditoría completa del proceso
- Almacena URLs de documentos generados (PDF, XML)

#### **4️⃣ Tabla `electronic_invoice_items` (NO DEPENDAS SOLO DEL JSON)**

**IMPORTANTE:** No dependas solo del JSON en `payload_sent`. Guarda los items en una tabla normalizada.

#### ✅ **Campos:**
- `id` (PK)
- `electronic_invoice_id` (FK a electronic_invoices)
- `tribute_id` (FK a dian_customer_tributes) ⭐ **Desde catálogo**
- `standard_code_id` (FK a dian_product_standards) ⭐ **Desde catálogo**
- `code_reference` (Código de referencia del producto - SKU)
- `name` (Nombre del producto/item)
- `quantity` (Cantidad)
- `price` (Precio unitario)
- `unit_measure_id` (FK a dian_measurement_units.factus_id) ⭐ **OBLIGATORIO**
- `tax_rate` (Tasa de impuesto %)
- `tax_amount` (Valor del impuesto)
- `discount_rate` (Tasa de descuento %)
- `total` (Total del item)
- `created_at` / `updated_at` (timestamps)

#### 📌 **Ventajas de Tabla Separada:**
- Consultas eficientes sin parsear JSON
- Reportes y análisis más fáciles
- Validaciones de integridad
- Historial de cambios
- No dependes de estructura JSON que puede cambiar

---

## 🧩 PRINCIPIO CLAVE: TABLAS DE REFERENCIA DIAN

### ⚠️ **REGLA DE ORO (MUY IMPORTANTE):**

> **👉 NUNCA hardcodees códigos DIAN en el código**  
> **👉 TODOS estos datos viven en tablas de referencia**  
> **👉 Se cargan con seeders**  
> **👉 Luego solo se referencian por ID / código**

### 📋 **Objetivo de las Tablas de Referencia:**

Definir qué tablas de referencia debes crear, qué datos van en cada una, cuáles se llenan con seeders, y cómo se relacionan con la facturación electrónica (Factus / DIAN).

Esto te deja listo para:
- ✅ Factura electrónica
- ✅ Nota crédito
- ✅ Nota débito
- ✅ Eventos DIAN
- ✅ Reclamos
- ✅ Documento soporte (futuro)

---

## 📚 CATÁLOGOS DIAN - TABLAS DE REFERENCIA

### **1️⃣ Tipos de Documentos Electrónicos**

#### **Tabla: `dian_document_types`**
```sql
CREATE TABLE dian_document_types (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) NOT NULL UNIQUE,  -- 01, 03, etc.
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_code (code)
);
```

#### **Seeder:**
```php
[
    ['code' => '01', 'name' => 'Factura electrónica de venta'],
    ['code' => '03', 'name' => 'Instrumento electrónico de transmisión'],
]
```

#### **📌 Usado en:**
- `electronic_invoices.document_type_id` (FK)
- Notas crédito / débito
- Validaciones de tipo de documento

---

### **2️⃣ Tipos de Operación**

#### **Tabla: `dian_operation_types`**
```sql
CREATE TABLE dian_operation_types (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) NOT NULL UNIQUE,  -- 10, 11, 12
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_code (code)
);
```

#### **Seeder:**
```php
[
    ['code' => '10', 'name' => 'Estándar'],
    ['code' => '11', 'name' => 'Mandatos'],
    ['code' => '12', 'name' => 'Transporte'],
]
```

#### **📌 Usado en:**
- `electronic_invoices.operation_type_id` (FK)
- Validaciones de items según `scheme_id`
- Mandatos (operaciones especiales)

---

### **3️⃣ Códigos de Corrección (Notas Crédito)**

#### **Tabla: `dian_correction_codes`**
```sql
CREATE TABLE dian_correction_codes (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) NOT NULL UNIQUE,  -- 1, 2, 3, etc.
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_code (code)
);
```

#### **Seeder:**
```php
[
    ['code' => '1', 'description' => 'Devolución parcial'],
    ['code' => '2', 'description' => 'Anulación'],
    ['code' => '3', 'description' => 'Rebaja / descuento'],
    ['code' => '4', 'description' => 'Ajuste de precio'],
    ['code' => '5', 'description' => 'Pronto pago'],
    ['code' => '6', 'description' => 'Volumen de ventas'],
]
```

#### **📌 Usado en:**
- `credit_notes.correction_code_id` (FK)
- Validación de motivo de nota crédito

---

### **4️⃣ Tipos de Operación – Notas Crédito**

#### **Tabla: `dian_credit_note_types`**
```sql
CREATE TABLE dian_credit_note_types (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) NOT NULL UNIQUE,  -- 20, 22
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_code (code)
);
```

#### **Seeder:**
```php
[
    ['code' => '20', 'description' => 'Nota Crédito con referencia'],
    ['code' => '22', 'description' => 'Nota Crédito sin referencia'],
]
```

#### **📌 Usado en:**
- `credit_notes.type_id` (FK)
- Validación de tipo de nota crédito

---

### **5️⃣ Estándares de Identificación del Producto**

#### **Tabla: `dian_product_standards`**
```sql
CREATE TABLE dian_product_standards (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### **Seeder:**
```php
[
    ['id' => 1, 'name' => 'Estándar contribuyente'],
    ['id' => 2, 'name' => 'UNSPSC'],
    ['id' => 3, 'name' => 'Partida Arancelaria'],
    ['id' => 4, 'name' => 'GTIN'],
]
```

#### **📌 Usado en:**
- `electronic_invoice_items.standard_code_id` (FK)
- Identificación de productos según estándar

---

### **6️⃣ Conceptos de Reclamo**

#### **Tabla: `dian_claim_concepts`**
```sql
CREATE TABLE dian_claim_concepts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) NOT NULL UNIQUE,  -- 01, 02, 03, 04
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_code (code)
);
```

#### **Seeder:**
```php
[
    ['code' => '01', 'name' => 'Documento con inconsistencias'],
    ['code' => '02', 'name' => 'Mercancía no entregada'],
    ['code' => '03', 'name' => 'Entrega parcial'],
    ['code' => '04', 'name' => 'Servicio no prestado'],
]
```

#### **📌 Usado en:**
- Reclamos DIAN
- Eventos 031 (Reclamo)
- Validación de motivo de reclamo

---

### **7️⃣ Eventos DIAN**

#### **Tabla: `dian_events`**
```sql
CREATE TABLE dian_events (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) NOT NULL UNIQUE,  -- 030, 031, 032, etc.
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_code (code)
);
```

#### **Seeder:**
```php
[
    ['code' => '030', 'name' => 'Acuse de recibo'],
    ['code' => '031', 'name' => 'Reclamo'],
    ['code' => '032', 'name' => 'Recibo del bien'],
    ['code' => '033', 'name' => 'Aceptación expresa'],
    ['code' => '034', 'name' => 'Aceptación tácita'],
]
```

#### **📌 Usado en:**
- Seguimiento post-factura
- RADIAN (si se implementa)
- Historial de eventos de factura

---

### **8️⃣ Tipos de Documento de Identidad (DIAN)**

#### **Tabla: `dian_identification_documents`**
```sql
CREATE TABLE dian_identification_documents (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) NULL,  -- Opcional, algunos tienen código
    name VARCHAR(255) NOT NULL UNIQUE,
    requires_dv BOOLEAN DEFAULT FALSE,  -- Si requiere dígito verificador
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_code (code)
);
```

#### **Seeder (tal cual DIAN):**
```php
[
    ['id' => 1, 'code' => null, 'name' => 'Registro civil', 'requires_dv' => false],
    ['id' => 2, 'code' => null, 'name' => 'Tarjeta de identidad', 'requires_dv' => false],
    ['id' => 3, 'code' => 'CC', 'name' => 'Cédula de ciudadanía', 'requires_dv' => false],
    ['id' => 4, 'code' => null, 'name' => 'Tarjeta de extranjería', 'requires_dv' => false],
    ['id' => 5, 'code' => 'CE', 'name' => 'Cédula de extranjería', 'requires_dv' => false],
    ['id' => 6, 'code' => 'NIT', 'name' => 'NIT', 'requires_dv' => true],
    ['id' => 7, 'code' => 'PP', 'name' => 'Pasaporte', 'requires_dv' => false],
    ['id' => 8, 'code' => null, 'name' => 'Documento extranjero', 'requires_dv' => false],
    ['id' => 9, 'code' => null, 'name' => 'PEP', 'requires_dv' => false],
    ['id' => 10, 'code' => null, 'name' => 'NIT otro país', 'requires_dv' => false],
    ['id' => 11, 'code' => null, 'name' => 'NUIP', 'requires_dv' => false],
]
```

#### **📌 Usado en:**
- `customer_tax_profiles.identification_document_id` (FK)
- Mandantes
- Documentos soporte
- Validación de tipo de documento

#### **⚠️ NOTA:**
Esta tabla reemplaza/consolida la tabla `identification_documents` mencionada anteriormente. Es la fuente única de verdad para tipos de documento DIAN.

---

### **9️⃣ Tributos del Cliente**

#### **Tabla: `dian_customer_tributes`**
```sql
CREATE TABLE dian_customer_tributes (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) NOT NULL UNIQUE,  -- 01, ZZ, etc.
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_code (code)
);
```

#### **Seeder:**
```php
[
    ['id' => 18, 'code' => '01', 'name' => 'IVA'],
    ['id' => 21, 'code' => 'ZZ', 'name' => 'No aplica'],
]
```

#### **📌 Usado en:**
- `customer_tax_profiles.tribute_id` (FK)
- Validación de régimen tributario del cliente

#### **⚠️ NOTA:**
Esta tabla puede consolidarse con la tabla `tributes` mencionada anteriormente, o mantener separada si hay diferencias entre tributos de cliente vs. tributos de items.

---

### **🔟 Tipos de Organización Legal**

#### **Tabla: `dian_legal_organizations`**
```sql
CREATE TABLE dian_legal_organizations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) NULL,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### **Seeder:**
```php
[
    ['id' => 1, 'code' => null, 'name' => 'Persona Jurídica'],
    ['id' => 2, 'code' => null, 'name' => 'Persona Natural'],
]
```

#### **📌 Usado en:**
- `customer_tax_profiles.legal_organization_id` (FK)
- Validación de tipo de organización

#### **⚠️ NOTA:**
Esta tabla puede consolidarse con `legal_organizations` mencionada anteriormente.

---

### **1️⃣1️⃣ Métodos de Pago**

#### **Tabla: `dian_payment_methods`**
```sql
CREATE TABLE dian_payment_methods (
    code VARCHAR(10) PRIMARY KEY,  -- PK es el código
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### **Seeder:**
```php
[
    ['code' => '10', 'name' => 'Efectivo'],
    ['code' => '42', 'name' => 'Consignación'],
    ['code' => '20', 'name' => 'Cheque'],
    ['code' => '47', 'name' => 'Transferencia'],
    ['code' => '71', 'name' => 'Bonos'],
    ['code' => '72', 'name' => 'Vales'],
    ['code' => '1', 'name' => 'No definido'],
    ['code' => '49', 'name' => 'Tarjeta Débito'],
    ['code' => '48', 'name' => 'Tarjeta Crédito'],
    ['code' => 'ZZZ', 'name' => 'Otro'],
]
```

#### **📌 Usado en:**
- `sales.payment_method_code` (FK o string)
- `electronic_invoices.payment_method_code`
- Validación de método de pago

---

### **1️⃣2️⃣ Formas de Pago**

#### **Tabla: `dian_payment_forms`**
```sql
CREATE TABLE dian_payment_forms (
    code VARCHAR(10) PRIMARY KEY,  -- PK es el código
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### **Seeder:**
```php
[
    ['code' => '1', 'name' => 'Contado'],
    ['code' => '2', 'name' => 'Crédito'],
]
```

#### **📌 Usado en:**
- `sales.payment_form_code` (FK o string)
- `electronic_invoices.payment_form_code`
- Validación de forma de pago

---

### **1️⃣3️⃣ Tipos de Documento para Rangos de Numeración**

#### **Tabla: `dian_numbering_document_types`**
```sql
CREATE TABLE dian_numbering_document_types (
    code VARCHAR(10) PRIMARY KEY,  -- 21, 22, 23, etc. hasta 30
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### **Seeder (códigos 21 → 30):**
```php
[
    ['code' => '21', 'description' => 'Factura de venta'],
    ['code' => '22', 'description' => 'Nota de Crédito'],
    ['code' => '23', 'description' => 'Nota de Débito'],
    ['code' => '24', 'description' => 'Nota de Ajuste'],
    ['code' => '25', 'description' => 'Documento Soporte de Pago'],
    ['code' => '26', 'description' => 'Documento Soporte de Contingencia'],
    ['code' => '27', 'description' => 'Documento Soporte de Exportación'],
    ['code' => '28', 'description' => 'Documento Soporte de Importación'],
    ['code' => '29', 'description' => 'Documento Soporte de Servicios'],
    ['code' => '30', 'description' => 'Documento Soporte de Venta'],
]
```

#### **📌 Usado en:**
- `factus_numbering_ranges.document_code` (String nullable - opcional, para cruzar con catálogo)
- Validación de tipo de documento para rango
- Asociación de rango con tipo de factura

---

### **1️⃣4️⃣ Responsabilidades Fiscales**

#### **Tabla: `dian_fiscal_responsibilities`**
```sql
CREATE TABLE dian_fiscal_responsibilities (
    code VARCHAR(20) PRIMARY KEY,  -- O-13, R-99-PN, etc.
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### **Seeder:**
```php
[
    ['code' => 'O-13', 'description' => 'Gran contribuyente'],
    ['code' => 'O-15', 'description' => 'Autorretenedor'],
    ['code' => 'O-23', 'description' => 'Agente de retención IVA'],
    ['code' => 'O-47', 'description' => 'Régimen simple'],
    ['code' => 'R-99-PN', 'description' => 'No responsable'],
]
```

#### **📌 Usado en:**
- Empresa (configuración del emisor)
- Cliente (opcional avanzado)
- `customer_tax_profiles.fiscal_responsibilities` (JSON array de códigos)
- Validación de responsabilidades fiscales

---

## 🔗 RELACIONES ENTRE CATÁLOGOS

### **Mapeo de Tablas Anteriores vs. Nuevas:**

| Tabla Anterior (Propuesta) | Tabla DIAN (Definitiva) | Acción |
|---------------------------|------------------------|--------|
| `identification_documents` | `dian_identification_documents` | ✅ Usar DIAN (más completo) |
| `legal_organizations` | `dian_legal_organizations` | ✅ Usar DIAN |
| `tributes` | `dian_customer_tributes` | ⚠️ Evaluar consolidación |
| `municipalities` | (Mantener) | ✅ Mantener (datos DANE) |

### **Actualización de Relaciones:**

```php
// customer_tax_profiles ahora usa:
- identification_document_id → dian_identification_documents.id
- legal_organization_id → dian_legal_organizations.id
- tribute_id → dian_customer_tributes.id (o tributes.id si se consolida)

// electronic_invoices ahora usa:
- document_type_id → dian_document_types.id
- operation_type_id → dian_operation_types.id
- payment_method_code → dian_payment_methods.code
- payment_form_code → dian_payment_forms.code

// numbering_ranges ahora usa:
- document_type_code → dian_numbering_document_types.code
```

---

## 📦 ESTRUCTURA DE SEEDERS

### **Orden de Ejecución Recomendado:**

1. `DianIdentificationDocumentsSeeder` (Base para clientes)
2. `DianLegalOrganizationsSeeder` (Base para clientes)
3. `DianCustomerTributesSeeder` (Base para clientes)
4. `MunicipalitiesSeeder` (Datos DANE - puede ser archivo CSV grande)
5. `DianDocumentTypesSeeder` (Para facturas)
6. `DianOperationTypesSeeder` (Para facturas)
7. `DianPaymentMethodsSeeder` (Para facturas)
8. `DianPaymentFormsSeeder` (Para facturas)
9. `DianNumberingDocumentTypesSeeder` (Para rangos)
10. `DianCorrectionCodesSeeder` (Para notas crédito)
11. `DianCreditNoteTypesSeeder` (Para notas crédito)
12. `DianProductStandardsSeeder` (Para items)
13. `DianClaimConceptsSeeder` (Para reclamos)
14. `DianEventsSeeder` (Para eventos)
15. `DianFiscalResponsibilitiesSeeder` (Para empresa/cliente)

---

## 🎯 RESUMEN: USO DE CATÁLOGOS DIAN

### **Mapeo de Uso por Módulo:**

#### **Módulo de Clientes:**
- `dian_identification_documents` → `customer_tax_profiles.identification_document_id`
- `dian_legal_organizations` → `customer_tax_profiles.legal_organization_id`
- `dian_customer_tributes` → `customer_tax_profiles.tribute_id`
- `dian_municipalities` → `customer_tax_profiles.municipality_id` (FK a factus_id)
- `dian_municipalities` → `company_tax_settings.municipality_id` (FK a factus_id)

#### **Módulo de Facturación Electrónica:**
- `dian_document_types` → `electronic_invoices.document_type_id`
- `dian_operation_types` → `electronic_invoices.operation_type_id`
- `dian_payment_methods` → `electronic_invoices.payment_method_code`
- `dian_payment_forms` → `electronic_invoices.payment_form_code`
- `dian_numbering_document_types` → `factus_numbering_ranges.document_code` (opcional, para cruzar)
- `dian_product_standards` → `electronic_invoice_items.standard_code_id`
- `dian_customer_tributes` → `electronic_invoice_items.tribute_id`
- `dian_measurement_units` → `electronic_invoice_items.unit_measure_id` (FK a factus_id) ⭐ **OBLIGATORIO**

#### **Módulo de Notas Crédito (Futuro):**
- `dian_correction_codes` → `credit_notes.correction_code_id`
- `dian_credit_note_types` → `credit_notes.type_id`

#### **Módulo de Reclamos y Eventos (Futuro):**
- `dian_claim_concepts` → `claims.concept_id`
- `dian_events` → `invoice_events.event_id`

#### **Configuración de Empresa:**
- `dian_fiscal_responsibilities` → `company.fiscal_responsibilities` (JSON array)

### **⚠️ IMPORTANTE - Principio de No Hardcodear:**

```php
// ❌ MAL - Hardcodeado
$documentType = '01'; // Factura

// ✅ BIEN - Desde catálogo
$documentType = DianDocumentType::where('code', '01')->first();
$invoice->document_type_id = $documentType->id;

// ❌ MAL - Hardcodeado
if ($operationType === '10') { // Estándar

// ✅ BIEN - Desde catálogo
$operationType = DianOperationType::where('code', '10')->first();
if ($invoice->operation_type_id === $operationType->id) {
```

### **📋 Ventajas de Usar Catálogos:**
1. **Mantenibilidad**: Cambios en códigos DIAN solo requieren actualizar seeder
2. **Validación**: Puedes validar que el código existe antes de usar
3. **Traducción**: Puedes mostrar nombres descriptivos en UI
4. **Auditoría**: Sabes exactamente qué código se usó en cada factura
5. **Escalabilidad**: Fácil agregar nuevos códigos sin cambiar código
6. **Consistencia**: Todos los módulos usan la misma fuente de verdad

---

## 🏙️ MUNICIPIOS DIAN (SINCRONIZADOS DESDE FACTUS)

### **OBJETIVO DE ESTE BLOQUE:**

Definir una implementación correcta y profesional de municipios, que permita:

- ✔ Evitar errores DIAN por municipios inválidos
- ✔ No depender de la API en cada factura
- ✔ Tener búsquedas rápidas en formularios
- ✔ Soportar clientes nacionales y extranjeros
- ✔ Escalar a empresa, sucursales y documentos soporte

### **1️⃣ PRINCIPIO CLAVE (NO NEGOCIABLE):**

> **👉 Los municipios NO se consultan en tiempo real al facturar**  
> **👉 Se sincronizan una vez y se guardan localmente**  
> **👉 Factus es la fuente de verdad**

**Esto es exactamente lo que Factus recomienda en su documentación.**

### **2️⃣ TABLA OBLIGATORIA: `dian_municipalities`**

Esta tabla almacena los municipios sincronizados desde Factus. **NO se seedean hardcodeados.**

#### ✅ **Estructura de la Tabla:**

```php
// database/migrations/XXXX_XX_XX_create_dian_municipalities_table.php
Schema::create('dian_municipalities', function (Blueprint $table) {
    $table->id();
    
    $table->unsignedBigInteger('factus_id')->unique(); // ID que entrega Factus ⭐
    $table->string('code', 10);                        // Código DIAN (informativo)
    $table->string('name');                            // Nombre del municipio
    $table->string('department');                      // Departamento
    
    $table->timestamps();
    
    // Índices
    $table->index('factus_id');
    $table->index('code');
    $table->index('name');
    $table->index('department');
    $table->index(['name', 'department']); // Para búsquedas combinadas
});
```

#### 📌 **IMPORTANTE:**

- **`factus_id`** es el valor que **SIEMPRE** enviarás a Factus (`municipality_id`)
- **`code`** es informativo (código DIAN/DANE)
- **`name` + `department`** es para UI (mostrar al usuario)
- **NO se seedean hardcodeados** - Se sincronizan desde Factus

### **3️⃣ ¿Seeder o Sincronización?**

#### ❌ **NO usar Seeder estático**

**Por qué:**
- Factus puede actualizar municipios
- Factus define los IDs válidos
- DIAN cambia códigos históricamente
- Necesitas los `factus_id` correctos

#### ✅ **Usar SINCRONIZACIÓN DESDE API**

Los municipios se sincronizan desde Factus mediante servicio y comando Artisan.

### **4️⃣ Servicio: `FactusMunicipalityService`**

```php
// app/Services/FactusMunicipalityService.php
namespace App\Services;

use App\Models\DianMunicipality;
use App\Services\FactusApiService;
use Illuminate\Support\Facades\Log;

class FactusMunicipalityService
{
    public function __construct(
        private FactusApiService $apiService
    ) {}

    /**
     * Sincroniza todos los municipios desde Factus
     * 
     * @return int Número de municipios sincronizados
     * @throws \Exception Si falla la sincronización
     */
    public function sync(): int
    {
        // 1. Obtener token de autenticación
        $token = $this->apiService->getAuthToken();
        
        // 2. Llamar API de Factus
        $response = $this->apiService->get('/v1/municipalities');
        
        if (!isset($response['data'])) {
            throw new \Exception('Respuesta inválida de Factus API');
        }
        
        $synced = 0;
        
        // 3. Iterar y sincronizar
        foreach ($response['data'] as $municipality) {
            DianMunicipality::updateOrCreate(
                ['factus_id' => $municipality['id']],
                [
                    'code' => $municipality['code'] ?? null,
                    'name' => $municipality['name'],
                    'department' => $municipality['department'] ?? '',
                ]
            );
            $synced++;
        }
        
        Log::info("Sincronizados {$synced} municipios desde Factus");
        
        return $synced;
    }

    /**
     * Busca municipios por término (para autocomplete)
     * 
     * @param string $term Término de búsqueda
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(string $term): \Illuminate\Database\Eloquent\Collection
    {
        return DianMunicipality::where('name', 'LIKE', "%{$term}%")
            ->orWhere('department', 'LIKE', "%{$term}%")
            ->orWhere('code', 'LIKE', "%{$term}%")
            ->orderBy('name')
            ->limit(20)
            ->get();
    }
}
```

### **5️⃣ Comando Artisan (RECOMENDADO)**

```php
// app/Console/Commands/SyncFactusMunicipalities.php
namespace App\Console\Commands;

use App\Services\FactusMunicipalityService;
use Illuminate\Console\Command;

class SyncFactusMunicipalities extends Command
{
    protected $signature = 'factus:sync-municipalities';
    protected $description = 'Sincroniza municipios desde Factus';

    public function handle(FactusMunicipalityService $service): int
    {
        $this->info('Sincronizando municipios desde Factus...');
        
        try {
            $synced = $service->sync();
            $this->info("✅ Sincronizados {$synced} municipios.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Error al sincronizar: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
```

**Este comando:**
- Se ejecuta una vez al instalar
- O cuando Factus actualice datos
- O al desplegar producción

### **6️⃣ DÓNDE USAR MUNICIPIOS EN TU MODELO DE DATOS**

#### **✔ Empresa (Emisor)**
```php
// company_tax_settings.municipality_id
// Relación:
public function municipality()
{
    return $this->belongsTo(DianMunicipality::class, 'municipality_id', 'factus_id');
}
```

#### **✔ Cliente DIAN**
```php
// customer_tax_profiles.municipality_id
// ⚠️ Regla DIAN:
// - Obligatorio si es Colombia
// - Opcional si es extranjero

// Relación:
public function municipality()
{
    return $this->belongsTo(DianMunicipality::class, 'municipality_id', 'factus_id');
}
```

#### **✔ Establecimientos / Sucursales (Futuro)**
```php
// establishments.municipality_id
// Relación similar
```

#### **✔ Facturas**

⚠️ **NO se guarda municipio directo en factura**

Se infiere desde:
- Cliente (`customer_tax_profiles.municipality_id`)
- Establecimiento (si aplica)

### **7️⃣ VALIDACIONES IMPORTANTES (Backend)**

#### **Cliente DIAN (StoreCustomerRequest):**

```php
'municipality_id' => [
    'required_if:is_colombian,true', // Solo obligatorio si es colombiano
    'nullable', // Opcional si es extranjero
    'exists:dian_municipalities,factus_id' // ⭐ Validar que existe el factus_id
]
```

#### **Empresa (StoreCompanyTaxSettingRequest):**

```php
'municipality_id' => [
    'required',
    'exists:dian_municipalities,factus_id' // ⭐ Validar que existe el factus_id
]
```

### **8️⃣ UX / UI RECOMENDADO (Blade)**

#### ❌ **NO usar select con 1.100 municipios sin filtro**

**Problemas:**
- Carga lenta
- UX terrible
- Difícil encontrar municipio

#### ✅ **Usar búsqueda tipo autocomplete**

**Ejemplo de implementación:**

```blade
<!-- Input con autocomplete -->
<div x-data="municipalitySearch()">
    <input 
        type="text"
        x-model="searchTerm"
        @input.debounce.300ms="search()"
        placeholder="Buscar municipio..."
        class="w-full"
    />
    
    <!-- Resultados -->
    <div x-show="results.length > 0" class="mt-2 border rounded">
        <template x-for="municipality in results" :key="municipality.factus_id">
            <div 
                @click="select(municipality)"
                class="p-2 hover:bg-gray-100 cursor-pointer"
            >
                <span x-text="municipality.name"></span> – 
                <span x-text="municipality.department"></span>
            </div>
        </template>
    </div>
    
    <!-- Input hidden con factus_id -->
    <input 
        type="hidden" 
        name="municipality_id" 
        x-model="selectedId"
    />
</div>

<script>
function municipalitySearch() {
    return {
        searchTerm: '',
        results: [],
        selectedId: null,
        
        async search() {
            if (this.searchTerm.length < 2) {
                this.results = [];
                return;
            }
            
            const response = await fetch(`/api/municipalities/search?q=${this.searchTerm}`);
            const data = await response.json();
            this.results = data;
        },
        
        select(municipality) {
            this.selectedId = municipality.factus_id; // ⭐ Guarda factus_id
            this.searchTerm = `${municipality.name} – ${municipality.department}`;
            this.results = [];
        }
    }
}
</script>
```

**Valor guardado:** `factus_id` (NO el `id` local)

### **9️⃣ QUÉ NO HACER (Errores Graves):**

1. **🚫 Guardar nombre del municipio como string**
   - Siempre usar FK a `dian_municipalities`
   - Guardar `factus_id`, no el nombre

2. **🚫 Enviar `code` en lugar de `factus_id`**
   - Factus espera `factus_id` en `municipality_id`
   - El `code` es solo informativo

3. **🚫 Consultar Factus en cada factura**
   - Sincronizar una vez y usar localmente
   - Consultar API solo para sincronización

4. **🚫 Permitir municipios no colombianos sin validación**
   - Validar que existe en `dian_municipalities`
   - Para extranjeros, puede ser opcional según reglas DIAN

5. **🚫 Hardcodear IDs en formularios**
   - Siempre sincronizar desde Factus
   - Usar autocomplete o búsqueda

6. **🚫 Usar `id` local en lugar de `factus_id`**
   - Siempre usar `factus_id` en relaciones y payloads
   - El `id` local es solo para índices internos

### **🔟 Modelo: `DianMunicipality`**

```php
// app/Models/DianMunicipality.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DianMunicipality extends Model
{
    protected $table = 'dian_municipalities';

    protected $fillable = [
        'factus_id',
        'code',
        'name',
        'department',
    ];

    protected $casts = [
        'factus_id' => 'integer',
    ];

    // Relaciones
    public function companyTaxSettings()
    {
        return $this->hasMany(CompanyTaxSetting::class, 'municipality_id', 'factus_id');
    }

    public function customerTaxProfiles()
    {
        return $this->hasMany(CustomerTaxProfile::class, 'municipality_id', 'factus_id');
    }

    // Scopes
    public function scopeSearch($query, string $term)
    {
        return $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('department', 'LIKE', "%{$term}%")
                    ->orWhere('code', 'LIKE', "%{$term}%");
    }

    // Helpers
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} – {$this->department}";
    }
}
```

### **1️⃣1️⃣ Ruta API para Búsqueda (Opcional, Recomendado)**

```php
// routes/api.php
Route::get('/municipalities/search', function (Request $request) {
    $term = $request->query('q', '');
    
    if (strlen($term) < 2) {
        return response()->json([]);
    }
    
    $municipalities = DianMunicipality::search($term)
        ->limit(20)
        ->get()
        ->map(function ($municipality) {
            return [
                'factus_id' => $municipality->factus_id,
                'name' => $municipality->name,
                'department' => $municipality->department,
                'code' => $municipality->code,
                'display' => "{$municipality->name} – {$municipality->department}",
            ];
        });
    
    return response()->json($municipalities);
});
```

---

## 📏 UNIDADES DE MEDIDA DIAN (SINCRONIZADAS DESDE FACTUS)

### **OBJETIVO DE ESTE BLOQUE:**

Implementar Unidades de Medida para que:

- ✔ `items.unit_measure_id` sea válido ante Factus
- ✔ No dependas de la API en cada factura
- ✔ Tengas UI usable (unidad, kg, lb, etc.)
- ✔ Evites hardcodeos
- ✔ Escales a productos físicos y servicios

### **1️⃣ PRINCIPIO CLAVE (NO NEGOCIABLE):**

> **👉 Las unidades de medida NO se crean manualmente**  
> **👉 NO se seedan hardcodeadas**  
> **👉 Se sincronizan desde Factus y se almacenan localmente**

**Factus es la fuente de verdad para los IDs.**

### **2️⃣ TABLA OBLIGATORIA: `dian_measurement_units`**

Esta tabla almacena las unidades de medida sincronizadas desde Factus. **NO se seedean hardcodeadas.**

#### ✅ **Estructura de la Tabla:**

```php
// database/migrations/XXXX_XX_XX_create_dian_measurement_units_table.php
Schema::create('dian_measurement_units', function (Blueprint $table) {
    $table->id();
    
    $table->unsignedBigInteger('factus_id')->unique(); // ID real de Factus ⭐
    $table->string('code', 10);                        // Código estándar (94, KGM, LBR, etc.)
    $table->string('name');                           // Nombre (unidad, kilogramo, libra, etc.)
    
    $table->timestamps();
    
    // Índices
    $table->index('factus_id');
    $table->index('code');
    $table->index('name');
});
```

#### 📌 **IMPORTANTE:**

- **`factus_id`** es el valor que **SIEMPRE** enviarás a Factus en `items.unit_measure_id`
- **`code`** es estándar (informativo) - ej: "94" (unidad), "KGM" (kilogramo), "LBR" (libra)
- **`name`** es para UI (mostrar al usuario)
- **NO se seedean hardcodeadas** - Se sincronizan desde Factus

### **3️⃣ ¿Seeder o Sincronización?**

#### ❌ **NO usar Seeder estático**

**Por qué:**
- Factus puede agregar unidades nuevas
- Los IDs pueden variar entre entornos
- No todas las unidades aplican siempre
- Necesitas los `factus_id` correctos

#### ✅ **Usar SINCRONIZACIÓN DESDE API**

Las unidades de medida se sincronizan desde Factus mediante servicio y comando Artisan.

### **4️⃣ Servicio: `FactusMeasurementUnitService`**

```php
// app/Services/FactusMeasurementUnitService.php
namespace App\Services;

use App\Models\DianMeasurementUnit;
use App\Services\FactusApiService;
use Illuminate\Support\Facades\Log;

class FactusMeasurementUnitService
{
    public function __construct(
        private FactusApiService $apiService
    ) {}

    /**
     * Sincroniza todas las unidades de medida desde Factus
     * 
     * @return int Número de unidades sincronizadas
     * @throws \Exception Si falla la sincronización
     */
    public function sync(): int
    {
        // 1. Obtener token de autenticación
        $token = $this->apiService->getAuthToken();
        
        // 2. Llamar API de Factus
        $response = $this->apiService->get('/v1/measurement-units');
        
        if (!isset($response['data'])) {
            throw new \Exception('Respuesta inválida de Factus API');
        }
        
        $synced = 0;
        
        // 3. Iterar y sincronizar
        foreach ($response['data'] as $unit) {
            DianMeasurementUnit::updateOrCreate(
                ['factus_id' => $unit['id']],
                [
                    'code' => $unit['code'] ?? null,
                    'name' => $unit['name'],
                ]
            );
            $synced++;
        }
        
        Log::info("Sincronizadas {$synced} unidades de medida desde Factus");
        
        return $synced;
    }

    /**
     * Busca unidades de medida por término (para autocomplete)
     * 
     * @param string $term Término de búsqueda
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(string $term): \Illuminate\Database\Eloquent\Collection
    {
        return DianMeasurementUnit::where('name', 'LIKE', "%{$term}%")
            ->orWhere('code', 'LIKE', "%{$term}%")
            ->orderBy('name')
            ->limit(20)
            ->get();
    }
}
```

### **5️⃣ Comando Artisan (RECOMENDADO)**

```php
// app/Console/Commands/SyncFactusMeasurementUnits.php
namespace App\Console\Commands;

use App\Services\FactusMeasurementUnitService;
use Illuminate\Console\Command;

class SyncFactusMeasurementUnits extends Command
{
    protected $signature = 'factus:sync-measurement-units';
    protected $description = 'Sincroniza unidades de medida desde Factus';

    public function handle(FactusMeasurementUnitService $service): int
    {
        $this->info('Sincronizando unidades de medida desde Factus...');
        
        try {
            $synced = $service->sync();
            $this->info("✅ Sincronizadas {$synced} unidades de medida.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Error al sincronizar: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
```

**📌 Ejecútalo:**
- Al instalar
- Al desplegar
- Cuando Factus actualice catálogos

### **6️⃣ DÓNDE SE USA LA UNIDAD DE MEDIDA**

#### **🧩 En Ítems de Factura (OBLIGATORIO)**

```json
{
  "items": [
    {
      "unit_measure_id": 70  // ⭐ factus_id, NO el code
    }
  ]
}
```

⚠️ **70 = `factus_id`, NO el `code`.**

#### **🧩 En Productos (RECOMENDADO)**

```php
// Tabla products
// Agregar campo:
$table->unsignedBigInteger('unit_measure_id')->nullable(); // FK a dian_measurement_units.factus_id

// Relación en modelo Product:
public function unitMeasure()
{
    return $this->belongsTo(DianMeasurementUnit::class, 'unit_measure_id', 'factus_id');
}
```

**📌 Ventajas:**
- Cada producto ya "sabe" su unidad
- Al crear factura, se usa automáticamente
- Evita errores de selección manual
- Consistencia en todo el sistema

### **7️⃣ VALIDACIONES IMPORTANTES (Backend)**

#### **Items de Factura (StoreElectronicInvoiceRequest):**

```php
'items.*.unit_measure_id' => [
    'required',
    'exists:dian_measurement_units,factus_id' // ⭐ Validar que existe el factus_id
]
```

#### **Productos (StoreProductRequest):**

```php
'unit_measure_id' => [
    'nullable', // Opcional, pero recomendado
    'exists:dian_measurement_units,factus_id' // ⭐ Validar que existe el factus_id
]
```

### **8️⃣ UX / UI RECOMENDADO (Blade)**

#### ❌ **NO pedir ID directamente**

**Problemas:**
- Usuario no sabe qué ID usar
- Propenso a errores
- UX terrible

#### ✅ **Mostrar nombre y guardar factus_id**

**Ejemplo de implementación:**

```blade
<!-- Select de unidades de medida -->
<div>
    <label class="block text-xs font-semibold text-gray-700 mb-2">
        Unidad de Medida <span class="text-red-500">*</span>
    </label>
    <select 
        name="unit_measure_id"
        x-model="unitMeasureId"
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        required
    >
        <option value="">Seleccione una unidad</option>
        @foreach($measurementUnits as $unit)
            <option value="{{ $unit->factus_id }}"> <!-- ⭐ Guarda factus_id -->
                {{ $unit->name }} ({{ $unit->code }})
            </option>
        @endforeach
    </select>
</div>
```

**O con autocomplete (si hay muchas unidades):**

```blade
<div x-data="measurementUnitSearch()">
    <input 
        type="text"
        x-model="searchTerm"
        @input.debounce.300ms="search()"
        placeholder="Buscar unidad (ej: kilogramo, unidad)..."
        class="w-full"
        required
    />
    
    <div x-show="results.length > 0" class="mt-2 border rounded bg-white shadow-lg">
        <template x-for="unit in results" :key="unit.factus_id">
            <div 
                @click="select(unit)"
                class="p-2 hover:bg-gray-100 cursor-pointer"
            >
                <span x-text="unit.name"></span> 
                <span class="text-gray-500" x-text="'(' + unit.code + ')'"></span>
            </div>
        </template>
    </div>
    
    <input 
        type="hidden" 
        name="unit_measure_id" 
        x-model="selectedId"
        required
    />
</div>
```

**Valor guardado:** `factus_id` (NO el `id` local)

### **9️⃣ ERRORES GRAVES A EVITAR:**

1. **🚫 Enviar `code` en lugar de `factus_id`**
   - Factus espera `factus_id` en `unit_measure_id`
   - El `code` es solo informativo

2. **🚫 Hardcodear unidad = 70**
   - Siempre buscar la unidad desde la BD
   - Los IDs pueden variar entre entornos

3. **🚫 No sincronizar**
   - Sincronizar una vez y usar localmente
   - Consultar API solo para sincronización

4. **🚫 Permitir unidad nula**
   - Validar que siempre hay unidad de medida
   - Es obligatorio para items de factura

5. **🚫 Usar texto libre**
   - Siempre usar FK a `dian_measurement_units`
   - No permitir que usuario escriba "kg", "lb", etc.

6. **🚫 Usar `id` local en lugar de `factus_id`**
   - Siempre usar `factus_id` en relaciones y payloads
   - El `id` local es solo para índices internos

### **🔟 Modelo: `DianMeasurementUnit`**

```php
// app/Models/DianMeasurementUnit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DianMeasurementUnit extends Model
{
    protected $table = 'dian_measurement_units';

    protected $fillable = [
        'factus_id',
        'code',
        'name',
    ];

    protected $casts = [
        'factus_id' => 'integer',
    ];

    // Relaciones
    public function products()
    {
        return $this->hasMany(Product::class, 'unit_measure_id', 'factus_id');
    }

    public function electronicInvoiceItems()
    {
        return $this->hasMany(ElectronicInvoiceItem::class, 'unit_measure_id', 'factus_id');
    }

    // Scopes
    public function scopeSearch($query, string $term)
    {
        return $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('code', 'LIKE', "%{$term}%");
    }

    // Helpers
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }
}
```

### **1️⃣1️⃣ Actualización de Tabla `products` (Recomendado)**

```php
// database/migrations/XXXX_XX_XX_add_unit_measure_id_to_products_table.php
Schema::table('products', function (Blueprint $table) {
    $table->unsignedBigInteger('unit_measure_id')->nullable()->after('price');
    
    // Índice
    $table->index('unit_measure_id');
    
    // Nota: No se agrega FK directa porque usa factus_id
    // Validar en aplicación que existe en dian_measurement_units.factus_id
});
```

### **1️⃣2️⃣ Actualización de Tabla `electronic_invoice_items`**

```php
// En migración de electronic_invoice_items
$table->unsignedBigInteger('unit_measure_id'); // FK a dian_measurement_units.factus_id

// Relación en modelo ElectronicInvoiceItem:
public function unitMeasure()
{
    return $this->belongsTo(DianMeasurementUnit::class, 'unit_measure_id', 'factus_id');
}
```

### **1️⃣3️⃣ Ruta API para Búsqueda (Opcional, Recomendado)**

```php
// routes/api.php
Route::get('/measurement-units/search', function (Request $request) {
    $term = $request->query('q', '');
    
    if (strlen($term) < 2) {
        return response()->json([]);
    }
    
    $units = DianMeasurementUnit::search($term)
        ->limit(20)
        ->get()
        ->map(function ($unit) {
            return [
                'factus_id' => $unit->factus_id,
                'name' => $unit->name,
                'code' => $unit->code,
                'display' => "{$unit->name} ({$unit->code})",
            ];
        });
    
    return response()->json($units);
});
```

---

## 🏢 GRUPO D — CONFIGURACIÓN DE EMPRESA

### **1️⃣1️⃣ Tabla `company_tax_settings` (Datos de TU Empresa frente a DIAN)**

Esta tabla almacena la información fiscal de **TU EMPRESA** (el emisor de facturas), no de los clientes.

#### ✅ **Campos:**
- `id` (PK)
- `company_name` (Razón social de la empresa)
- `nit` (NIT de la empresa)
- `dv` (Dígito verificador del NIT)
- `email` (Email de la empresa para facturación)
- `municipality_id` (FK a dian_municipalities.factus_id - Ubicación fiscal)
- `economic_activity` (Código CIIU - Actividad económica)
- `logo_url` (URL del logo de la empresa - para PDFs)
- `factus_company_id` (ID de la empresa en Factus - después de registro)
- `created_at` / `updated_at` (timestamps)

#### 📌 **Características:**
- **Relación 1:1** con el sistema (solo una empresa configurada - singleton)
- Datos obligatorios para poder emitir facturas electrónicas
- Se configura una sola vez al inicio
- `factus_company_id` se obtiene después de registrar la empresa en Factus

### **1️⃣2️⃣ Tabla `factus_numbering_ranges` (Rangos de Numeración - SINCRONIZADOS DESDE FACTUS)**

#### ⚠️ **PRINCIPIO CLAVE (NO NEGOCIABLE):**

> **👉 Los rangos de numeración NO se crean manualmente en tu BD**  
> **👉 NO se seedan hardcodeados**  
> **👉 SE SINCRONIZAN DESDE FACTUS**

#### **¿Por qué este principio es crítico?**

> **👉 Los rangos de numeración NO se crean manualmente en tu BD**  
> **👉 NO se seedan hardcodeados**  
> **👉 SE SINCRONIZAN DESDE FACTUS**

#### **¿Por qué?**
- Son dinámicos (pueden activarse/desactivarse)
- Pueden vencerse
- El `current` cambia cada factura
- Factus es la fuente de verdad

Esta tabla es **independiente de los catálogos DIAN** y almacena los rangos sincronizados desde la API de Factus.

#### ✅ **Campos:**
- `id` (PK - auto-increment local)
- `factus_id` (ID REAL de Factus - UNIQUE, se usa al facturar) ⭐
- `document` (String - "Factura de Venta", "Nota Crédito", etc.)
- `document_code` (String nullable - "21", "22", "23", etc. - si se cruza con catálogo)
- `prefix` (String nullable - Prefijo del rango - ej: "FV", "NC", "ND")
- `range_from` (bigInteger - Número inicial del rango)
- `range_to` (bigInteger - Número final del rango)
- `current` (bigInteger - Número actual - gestionado por Factus)
- `resolution_number` (String nullable - Número de resolución DIAN)
- `technical_key` (String nullable - Clave técnica para facturación electrónica)
- `start_date` (Date nullable - Fecha de inicio de validez)
- `end_date` (Date nullable - Fecha de fin de validez)
- `is_expired` (Boolean - Si el rango está vencido)
- `is_active` (Boolean - Si el rango está activo)
- `created_at` / `updated_at` (timestamps)

#### 📌 **Características:**
- **Sincronización automática**: Se llena mediante comando Artisan o Job
- **`factus_id` es el ID real**: Este es el ID que se envía a Factus al facturar
- **No se modifica manualmente**: Solo se actualiza mediante sincronización
- **`current` se gestiona en Factus**: NO se incrementa localmente
- **Múltiples rangos posibles**: Puede haber varios rangos activos del mismo tipo

#### 📌 **Uso:**
- Se sincroniza desde Factus mediante `php artisan factus:sync-numbering-ranges`
- Un rango puede usarse solo si:
  - `is_active = true`
  - `is_expired = false`
  - `start_date <= hoy <= end_date` (si están definidas)
- Al facturar, se envía `numbering_range_id = factus_id` a Factus
- Factus incrementa el `current` automáticamente
- Se vuelve a sincronizar para obtener el `current` actualizado

### **Flujo de Configuración Inicial:**

```
1. Usuario configura datos de empresa (company_tax_settings)
   ↓
2. Validar NIT y DV
   ↓
3. Registrar empresa en Factus (obtener factus_company_id)
   ↓
4. Sincronizar rangos de numeración desde Factus (factus:sync-numbering-ranges)
   ↓
5. Validar que hay al menos un rango activo para facturas
   ↓
6. Sistema listo para emitir facturas electrónicas
```

### **🔄 Sincronización de Rangos de Numeración**

#### **Estrategia: SINCRONIZACIÓN AUTOMÁTICA**

Los rangos se sincronizan desde Factus mediante:

1. **Comando Artisan**: `php artisan factus:sync-numbering-ranges`
2. **Servicio**: `FactusNumberingRangeService`
3. **Job (opcional, recomendado)**:
   - Diario (scheduled)
   - Al iniciar sesión de admin
   - Antes de facturar (validación)

#### **Flujo de Sincronización:**

```
1. Obtener token de autenticación Factus
   ↓
2. Llamar API: GET /v1/numbering-ranges?filter[is_active]=1
   ↓
3. Iterar response['data']['data']
   ↓
4. updateOrCreate por factus_id
   ↓
5. Actualizar campos: document, prefix, range_from, range_to, current, etc.
```

#### **Ejemplo de Código de Sincronización:**

```php
// app/Services/FactusNumberingRangeService.php
foreach ($response['data']['data'] as $range) {
    FactusNumberingRange::updateOrCreate(
        ['factus_id' => $range['id']],
        [
            'document' => $range['document'],
            'prefix' => $range['prefix'],
            'range_from' => $range['from'],
            'range_to' => $range['to'],
            'current' => $range['current'],
            'resolution_number' => $range['resolution_number'],
            'technical_key' => $range['technical_key'],
            'start_date' => $range['start_date'],
            'end_date' => $range['end_date'],
            'is_expired' => $range['is_expired'],
            'is_active' => $range['is_active'],
        ]
    );
}
```

### **Validaciones Críticas (GRUPO D):**

#### **CompanyTaxSetting:**
- NIT debe tener formato válido (9-10 dígitos)
- DV debe ser válido según algoritmo DIAN
- Email debe ser válido (se usa para notificaciones)
- `municipality_id` debe existir en `dian_municipalities` (validar por `factus_id`)
- Solo debe haber UN registro (singleton)

#### **FactusNumberingRange:**
- `range_from` debe ser menor que `range_to`
- `start_date` debe ser menor que `end_date` (si están definidas)
- `factus_id` debe ser único (viene de Factus)
- `current` se gestiona en Factus, no localmente
- Solo se actualiza mediante sincronización, no manualmente

### **Reglas de Negocio (GRUPO D):**

#### **RB-9: Configuración de Empresa**
- La empresa debe estar completamente configurada antes de emitir facturas
- `factus_company_id` se obtiene después de registrar en Factus
- Una vez configurada, los cambios deben ser auditados

#### **RB-10: Rangos de Numeración (Sincronizados desde Factus)**
- Los rangos se sincronizan desde Factus, NO se crean manualmente
- Debe haber al menos un rango activo y no vencido para facturar
- El `current` se gestiona en Factus, NO se incrementa localmente
- Antes de facturar, validar que el rango existe, está activo y no está vencido
- Sincronizar rangos periódicamente (diario o antes de facturar)
- No se pueden eliminar rangos con facturas asociadas (solo se desactivan en Factus)

### **🚫 QUÉ NO HACER (MUY IMPORTANTE):**

#### **Errores Comunes a Evitar:**

1. **🚫 NO guardes `current` como contador local**
   - El `current` se gestiona en Factus
   - Solo se sincroniza para lectura, no para escritura

2. **🚫 NO incrementes tú el número**
   - Factus incrementa automáticamente al facturar
   - Solo envías `numbering_range_id = factus_id`

3. **🚫 NO hardcodees `numbering_range_id`**
   - Siempre busca el rango activo y válido
   - Puede haber múltiples rangos del mismo tipo

4. **🚫 NO uses rangos vencidos**
   - Validar `is_expired = false`
   - Validar fechas de validez (`start_date`, `end_date`)

5. **🚫 NO asumas que solo hay un rango**
   - Puede haber múltiples rangos activos
   - Selecciona el más apropiado según lógica de negocio

6. **🚫 NO crees rangos manualmente en la BD**
   - Solo se sincronizan desde Factus
   - No se seedean hardcodeados

7. **🚫 NO modifiques `current` localmente**
   - Se actualiza solo mediante sincronización
   - Factus es la fuente de verdad

### **✅ CÓMO USAR EL RANGO AL FACTURAR:**

#### **Forma Correcta:**

```php
// 1. El usuario elige el tipo de documento (o se determina automáticamente)
$documentType = 'Factura de Venta'; // o 'Nota Crédito', etc.

// 2. Tu sistema elige el rango activo correcto
$range = FactusNumberingRange::valid()
    ->forDocument($documentType)
    ->firstOrFail();

// 3. Validar que el rango es válido
if (!$range->isValid()) {
    throw new \Exception('El rango de numeración no es válido');
}

if ($range->isExhausted()) {
    throw new \Exception('El rango de numeración está agotado');
}

if (empty($range->technical_key)) {
    throw new \Exception('El rango no tiene technical_key configurado');
}

// 4. Envías SOLO el numbering_range_id (factus_id) a Factus
$payload['numbering_range_id'] = $range->factus_id;

// 5. Factus incrementa el current automáticamente
// 6. Sincronizas después para obtener el current actualizado
```

### **✅ VALIDACIONES IMPORTANTES (ANTES DE FACTURAR):**

Antes de enviar una factura a Factus, validar:

1. **✔ El rango existe** en `factus_numbering_ranges`
2. **✔ Está activo** (`is_active = true`)
3. **✔ No está vencido** (`is_expired = false`)
4. **✔ Coincide con el tipo de documento** (`document` coincide)
5. **✔ Tiene `technical_key`** si es factura electrónica
6. **✔ Fechas de validez** (si están definidas, deben ser válidas)
7. **✔ No está agotado** (`current < range_to`)

**Si falla alguna validación → NO factures**

### **📋 RELACIÓN CON FACTURAS / NC / ND:**

En `electronic_invoices`:

```php
// Migración
$table->unsignedBigInteger('factus_numbering_range_id'); // Guarda el factus_id

// Modelo
public function numberingRange()
{
    return $this->belongsTo(FactusNumberingRange::class, 'factus_numbering_range_id', 'factus_id');
}
```

**Esto permite:**
- Saber con qué resolución se facturó
- Auditar qué rango se usó
- Mostrar prefijo y rango en reportes
- Soportar validaciones DIAN
- Rastrear el `current` usado

---

## FASE 1 – ANÁLISIS DEL ESTADO ACTUAL

### 1.1 Estructura Actual del Módulo de Clientes

#### **Modelo Customer** (`app/Models/Customer.php`)
```php
// Campos fillable actuales (estructura simple - GRUPO A)
protected $fillable = [
    'name',           // Nombre completo
    'email',          // Email (opcional)
    'phone',          // Teléfono (opcional)
    'address',        // Dirección comercial (opcional)
    'city',           // Ciudad (opcional)
    'state',          // Estado/Departamento (opcional)
    'zip_code',       // Código postal (opcional)
    'notes',          // Notas adicionales (opcional)
    'is_active',      // Estado activo/inactivo
    // ⚠️ FALTA: 'requires_electronic_invoice' (boolean)
];

// Relaciones existentes
- hasMany(Sale::class)
- hasMany(Repair::class)

// ⚠️ FALTA: hasOne(CustomerElectronicBilling::class)
```

#### **Migración Base** (`database/migrations/2025_08_20_041709_create_customers_table.php`)
```sql
Campos actuales:
- id (PK)
- name (string, required)
- email (string, nullable, unique)
- phone (string, nullable)
- address (text, nullable)
- identification (string, nullable) ⚠️ EXISTE pero no se usa - ELIMINAR
- type (enum: 'individual', 'business') ⚠️ EXISTE pero no se usa - ELIMINAR
- is_active (boolean, default: true)
- timestamps

Campos agregados posteriormente (migración 2025_09_02_153249):
- city (string, nullable)
- state (string, nullable)
- zip_code (string, nullable)
- notes (text, nullable)

⚠️ LIMPIEZA NECESARIA:
- Eliminar campo 'identification' (va a customer_electronic_billing)
- Eliminar campo 'type' (se determina por document_type en tabla relacionada)
- Agregar campo 'requires_electronic_invoice' (boolean, default: false)
```

#### **Controlador** (`app/Http/Controllers/CustomerController.php`)
- **Validación inline** en métodos `store()` y `update()` (NO usa FormRequest)
- Validaciones básicas sin lógica condicional
- No hay separación entre cliente normal y cliente DIAN

#### **Vistas Blade**
- `create.blade.php`: Formulario completo con secciones organizadas
- `edit.blade.php`: Similar estructura, muestra información del sistema
- **NO existe** sección para facturación electrónica

#### **FormRequests Existentes** (NO se usan actualmente)
- `StoreCustomerRequest.php`: Validaciones antiguas (no coinciden con modelo actual)
- `UpdateCustomerRequest.php`: Validaciones antiguas

### 1.2 Campos Reutilizables para Facturación Electrónica

#### ✅ **Campos que YA EXISTEN en `customers` (se mantienen):**
- `name` → Nombre del cliente (comercial)
- `email` → Email de contacto (opcional)
- `phone` → Teléfono de contacto (opcional)
- `address` → Dirección comercial (opcional)
- `city` → Ciudad comercial (opcional)
- `state` → Estado/Departamento comercial (opcional)
- `zip_code` → Código postal comercial (opcional)
- `notes` → Notas adicionales (opcional)
- `is_active` → Estado activo/inactivo

#### ⚠️ **Campos a ELIMINAR de `customers`:**
- `identification` → Se mueve a `customer_electronic_billing.identification`
- `type` → Se reemplaza por `customer_electronic_billing.document_type`

#### ✅ **Campo a AGREGAR a `customers`:**
- `requires_electronic_invoice` (boolean, default: false) → Flag para activar facturación electrónica

#### ❌ **Campos FALTANTES para DIAN (van en `customer_tax_profiles`):**
- `identification_document_id` (FK a catálogo de tipos de documento)
- `identification` (Número de identificación)
- `dv` (Dígito Verificador) - Obligatorio para NIT
- `legal_organization_id` (FK a catálogo de organizaciones legales)
- `company` (Razón social / Nombre empresa)
- `trade_name` (Nombre comercial)
- `tribute_id` (FK a catálogo de regímenes tributarios)
- `municipality_id` (FK a dian_municipalities.factus_id - Sincronizado desde Factus)

#### 📋 **Tablas de Catálogos Necesarias:**
- `identification_documents` (CC, NIT, CE, PP, TI, NUI)
- `legal_organizations` (Tipos de organización legal)
- `tributes` (Regímenes tributarios: R-99-PN, 48, etc.)
- `dian_municipalities` (Municipios sincronizados desde Factus - NO seedear hardcodeados)

### 1.3 Problemas de Diseño Identificados

#### 🔴 **Problema 1: Mezcla de Responsabilidades**
- La tabla `customers` mezcla datos de contacto con datos fiscales
- No hay separación clara entre "cliente comercial" y "cliente fiscal"
- Si todos los campos DIAN fueran obligatorios, rompería clientes existentes

#### 🔴 **Problema 2: Campos No Utilizados**
- `identification` existe pero no se valida ni se muestra en formularios
- `type` existe pero no se usa en la lógica de negocio
- Esto genera confusión y datos inconsistentes

#### 🔴 **Problema 3: Validaciones Inconsistentes**
- El controlador valida inline en lugar de usar FormRequest
- No hay validaciones condicionales
- Los FormRequests existentes no coinciden con el modelo actual

#### 🔴 **Problema 4: Impacto en Ventas**
- El modelo `Sale` solo tiene `customer_id`
- No hay forma de saber si una venta requiere facturación electrónica
- No hay relación con datos fiscales del cliente al momento de facturar

#### 🔴 **Problema 5: Escalabilidad Futura**
- Si se agregan campos DIAN directamente a `customers`, todos los clientes los tendrían
- No hay forma de marcar "este cliente requiere facturación electrónica"
- Dificulta migración futura a otros proveedores (Factus, otros)

### 1.4 Impacto en Módulos Relacionados

#### **Ventas (Sales)**
- Actualmente: `Sale` → `Customer` (relación simple)
- Futuro: Necesitará saber si el cliente requiere facturación electrónica
- Impacto: Al crear venta, validar si cliente tiene datos DIAN completos

#### **Facturación (Futuro)**
- Necesitará acceso a datos fiscales del cliente
- Validación de datos DIAN antes de generar factura
- Envío a Factus requiere todos los campos obligatorios

#### **Reportes (Futuro)**
- Filtrar clientes con/sin facturación electrónica
- Reportes fiscales por régimen tributario
- Validación de cumplimiento DIAN

---

## FASE 2 – DISEÑO DE SOLUCIÓN

### 2.1 Decisión Arquitectónica: Tabla Relacionada

#### ✅ **RECOMENDACIÓN: Tabla Separada `customer_electronic_billing`**

**Razones:**
1. **Separación de Responsabilidades (SRP)**
   - `customers`: Datos comerciales/contacto
   - `customer_electronic_billing`: Datos fiscales/DIAN

2. **Escalabilidad**
   - No contamina la tabla principal con campos que no todos necesitan
   - Fácil agregar nuevos proveedores (Factus, otros) sin modificar estructura base

3. **Compatibilidad Retroactiva**
   - Clientes existentes NO se rompen
   - Migración gradual posible
   - Validaciones condicionales claras

4. **Normalización de Datos**
   - Relación 1:1 (un cliente puede tener o no facturación electrónica)
   - Datos fiscales agrupados lógicamente

#### ❌ **Alternativa Rechazada: Campos en Tabla Única**
- Problema: Todos los clientes tendrían campos DIAN (mayoría NULL)
- Problema: Validaciones complejas y confusas
- Problema: Dificulta mantenimiento futuro

### 2.2 Estructura de Base de Datos Propuesta

#### **Tabla: `customers` (Estructura Simplificada - GRUPO A)**
```sql
-- Estructura final de customers (SIN campos DIAN)
CREATE TABLE customers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,                    -- Nombre completo
    email VARCHAR(255) NULLABLE UNIQUE,            -- Email (opcional)
    phone VARCHAR(20) NULLABLE,                    -- Teléfono (opcional)
    address TEXT NULLABLE,                         -- Dirección comercial (opcional)
    city VARCHAR(100) NULLABLE,                    -- Ciudad (opcional)
    state VARCHAR(100) NULLABLE,                   -- Estado/Departamento (opcional)
    zip_code VARCHAR(10) NULLABLE,                -- Código postal (opcional)
    notes TEXT NULLABLE,                          -- Notas adicionales
    is_active BOOLEAN DEFAULT TRUE,               -- Estado activo/inactivo
    requires_electronic_invoice BOOLEAN DEFAULT FALSE,  -- ⭐ FLAG: Requiere facturación electrónica
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_customers_electronic_invoice (requires_electronic_invoice),
    INDEX idx_customers_active (is_active)
);

-- ⚠️ IMPORTANTE: Esta tabla NO debe tener campos DIAN obligatorios
-- Todos los datos fiscales/DIAN van en customer_tax_profiles
```

#### **Nueva Tabla: `customer_tax_profiles` (OBLIGATORIA para facturación electrónica)**
```sql
CREATE TABLE customer_tax_profiles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL UNIQUE,
    
    -- Identificación Fiscal (DIAN) - Relaciones normalizadas
    identification_document_id BIGINT UNSIGNED NOT NULL,  -- FK a catálogo (CC, NIT, CE, etc.)
    identification VARCHAR(20) NOT NULL,                 -- Número de identificación
    dv VARCHAR(1) NULL,                                  -- Dígito verificador (solo NIT)
    
    -- Información Legal - Relaciones normalizadas
    legal_organization_id BIGINT UNSIGNED NULL,          -- FK a catálogo (tipo organización)
    company VARCHAR(255) NULL,                           -- Razón social / Nombre empresa
    trade_name VARCHAR(255) NULL,                        -- Nombre comercial
    
    -- Régimen Tributario - Relación normalizada
    tribute_id BIGINT UNSIGNED NULL,                     -- FK a catálogo (R-99-PN, 48, etc.)
    
    -- Ubicación Fiscal (DIAN) - Relación normalizada
    municipality_id BIGINT UNSIGNED NOT NULL,           -- FK a dian_municipalities.factus_id (sincronizado desde Factus)
    
    -- Metadatos
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (identification_document_id) REFERENCES identification_documents(id),
    FOREIGN KEY (legal_organization_id) REFERENCES legal_organizations(id),
    FOREIGN KEY (tribute_id) REFERENCES tributes(id),
    -- municipality_id guarda el factus_id de dian_municipalities (no FK directa)
    -- Validar en aplicación que existe en dian_municipalities.factus_id
    
    INDEX idx_customer_id (customer_id),
    INDEX idx_identification (identification, identification_document_id),
    INDEX idx_municipality (municipality_id)
);

-- 📌 IMPORTANTE: Esta tabla se llena SOLO si requires_electronic_invoice = true
-- Relación: 1 cliente → 0 o 1 perfil fiscal (1:1)
```

#### **Tablas de Catálogos (Normalización)**
```sql
-- Catálogo: Tipos de Documento de Identificación
CREATE TABLE identification_documents (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) NOT NULL UNIQUE,           -- 'CC', 'NIT', 'CE', 'PP', 'TI', 'NUI'
    name VARCHAR(100) NOT NULL,                 -- 'Cédula de Ciudadanía', 'NIT', etc.
    requires_dv BOOLEAN DEFAULT FALSE,          -- Si requiere dígito verificador
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Catálogo: Tipos de Organización Legal
CREATE TABLE legal_organizations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Catálogo: Regímenes Tributarios
CREATE TABLE tributes (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,           -- 'R-99-PN', '48', etc.
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Catálogo: Municipios (Códigos DANE)
CREATE TABLE municipalities (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) NOT NULL UNIQUE,           -- Código DANE
    name VARCHAR(255) NOT NULL,
    department_code VARCHAR(10) NOT NULL,       -- Código departamento
    department_name VARCHAR(255) NOT NULL,      -- Nombre departamento
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_department (department_code)
);
```

### 2.3 Campos DIAN Requeridos (Según Resolución 000042 de 2020)

#### **Obligatorios para Personas Naturales:**
- `identification` (CC, CE, TI, PP)
- `document_type`
- `fiscal_address`
- `fiscal_city`
- `municipality_code`
- `department_code`
- `fiscal_email`

#### **Obligatorios para Personas Jurídicas (NIT):**
- `identification` (NIT)
- `dv` (Dígito Verificador)
- `document_type` (NIT)
- `legal_organization` (Razón Social)
- `fiscal_address`
- `fiscal_city`
- `municipality_code`
- `department_code`
- `fiscal_email`
- `tribute` (Régimen tributario)

#### **Opcionales pero Recomendados:**
- `trade_name` (Nombre comercial)
- `legal_representative` (Representante legal)
- `economic_activity` (CIIU)
- `tax_responsibilities` (Responsabilidades fiscales)
- `fiscal_phone`

### 2.4 Reglas de Negocio

#### **RB-1: Activación de Facturación Electrónica**
- Un cliente puede activar facturación electrónica en cualquier momento
- Al activar `requires_electronic_invoice = true`, se crea registro en `customer_tax_profiles`
- Al desactivar `requires_electronic_invoice = false`, se elimina el registro de `customer_tax_profiles` (cascade)
- **IMPORTANTE:** La tabla `customers` NO debe tener campos DIAN obligatorios
- **RELACIÓN:** 1 cliente → 0 o 1 perfil fiscal (1:1)

#### **RB-2: Validación de Datos Completos**
- Si `requires_electronic_invoice = true`, debe existir registro en `customer_tax_profiles`
- Los campos obligatorios según `identification_document_id` deben estar completos:
  - `identification_document_id` (obligatorio)
  - `identification` (obligatorio)
  - `municipality_id` (obligatorio)
  - `dv` (obligatorio solo si el documento requiere DV, ej: NIT)
  - `company` (obligatorio para personas jurídicas)
- No se puede crear venta con facturación si datos están incompletos
- **IMPORTANTE:** Clientes normales (`requires_electronic_invoice = false`) NO requieren validación DIAN

#### **RB-3: Modificación de Datos Fiscales**
- Si el cliente tiene facturas emitidas, algunos campos NO pueden modificarse (auditoría)
- `identification` y `dv` son inmutables una vez creados
- `identification_document_id` no puede cambiar (tipo de documento es inmutable)
- `municipality_id` puede cambiar solo si no hay facturas emitidas

#### **RB-4: Integración con Ventas**
- Al seleccionar cliente en venta, verificar si requiere facturación
- Mostrar indicador visual si cliente tiene facturación activa
- Validar datos completos antes de permitir crear venta con facturación

### 2.5 Flujo Completo: UI → Base de Datos

```
1. Usuario crea/edita cliente
   ↓
2. Marca checkbox "Requiere facturación electrónica"
   ↓
3. Frontend muestra campos DIAN (Alpine.js)
   ↓
4. Usuario completa campos obligatorios
   ↓
5. Frontend valida campos visibles (JavaScript)
   ↓
6. Submit → FormRequest valida condicionalmente
   ↓
7. Controller crea/actualiza Customer
   ↓
8. Si requires_electronic_invoice = true:
   - Crea/actualiza CustomerTaxProfile
   - Valida campos obligatorios según identification_document_id
   - Valida relaciones con catálogos (municipality_id, tribute_id, legal_organization_id)
   - Valida que todos los IDs de catálogos existan
   ↓
9. Si requires_electronic_invoice = false:
   - Elimina registro de CustomerTaxProfile (si existe) - cascade delete
   - Cliente funciona normalmente sin datos DIAN
   ↓
10. 📌 IMPORTANTE: customer_tax_profiles se llena SOLO si requires_electronic_invoice = true
   ↓
10. Respuesta exitosa → Redirect con mensaje
```

---

## FASE 3 – UX / UI (BLADE)

### 3.1 Componente: Switch de Facturación Electrónica

**Ubicación:** Después de la sección "Información Adicional", antes de "Botones de Acción"

```blade
<!-- Facturación Electrónica DIAN -->
<div class="bg-white rounded-xl border border-gray-100 p-4 sm:p-6"
     x-data="{ requiresElectronicInvoice: {{ old('requires_electronic_invoice', $customer->requires_electronic_invoice ?? false) ? 'true' : 'false' }} }">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-3">
            <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                <i class="fas fa-file-invoice text-sm"></i>
            </div>
            <div>
                <h2 class="text-base sm:text-lg font-semibold text-gray-900">
                    Facturación Electrónica DIAN
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Activa esta opción si el cliente requiere facturación electrónica
                </p>
            </div>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox"
                   name="requires_electronic_invoice"
                   value="1"
                   x-model="requiresElectronicInvoice"
                   class="sr-only peer">
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
        </label>
    </div>

    <!-- Campos DIAN (mostrar/ocultar dinámicamente) -->
    <div x-show="requiresElectronicInvoice"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="mt-6 space-y-5 border-t border-gray-200 pt-6">
        
        <!-- Mensaje informativo -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-3"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-1">Campos Obligatorios para Facturación Electrónica</p>
                    <p class="text-xs">Complete todos los campos marcados con <span class="text-red-500 font-bold">*</span> para poder generar facturas electrónicas válidas según la normativa DIAN.</p>
                </div>
            </div>
        </div>

        <!-- Tipo de Documento -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-2">
                    Tipo de Documento <span class="text-red-500">*</span>
                </label>
                <select name="identification_document_id"
                        x-model="identificationDocumentId"
                        @change="updateRequiredFields()"
                        required
                        class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                    <option value="">Seleccione...</option>
                    @foreach($identificationDocuments as $doc)
                        <option value="{{ $doc->id }}" 
                                data-code="{{ $doc->code }}"
                                data-requires-dv="{{ $doc->requires_dv ? 'true' : 'false' }}">
                            {{ $doc->name }} ({{ $doc->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Identificación -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-2">
                    Número de Identificación <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="identification"
                       x-model="identification"
                       @input="calculateDV()"
                       required
                       class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
            </div>
        </div>

        <!-- Dígito Verificador (solo si el documento lo requiere) -->
        <div x-show="requiresDV" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-2">
                    Dígito Verificador (DV) <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="dv"
                       x-model="dv"
                       maxlength="1"
                       required
                       class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                <p class="mt-1 text-xs text-gray-500">
                    Se calcula automáticamente para NIT
                </p>
            </div>
        </div>

        <!-- Razón Social / Nombre Comercial (solo para personas jurídicas) -->
        <div x-show="isJuridicalPerson" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-2">
                    Razón Social / Empresa <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="company"
                       required
                       class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-2">
                    Nombre Comercial
                </label>
                <input type="text"
                       name="trade_name"
                       class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
            </div>
        </div>

        <!-- Tipo de Organización Legal (opcional) -->
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-2">
                Tipo de Organización Legal
            </label>
            <select name="legal_organization_id"
                    class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                <option value="">Seleccione...</option>
                @foreach($legalOrganizations as $org)
                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Municipio (con búsqueda de catálogo DANE) -->
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-2">
                Municipio <span class="text-red-500">*</span>
            </label>
            <select name="municipality_id"
                    x-model="municipalityId"
                    @change="updateDepartmentInfo()"
                    required
                    class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                <option value="">Seleccione municipio...</option>
                @foreach($municipalities as $municipality)
                    <option value="{{ $municipality->id }}"
                            data-department="{{ $municipality->department_name }}">
                        {{ $municipality->name }} - {{ $municipality->department_name }} ({{ $municipality->code }})
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">
                Seleccione el municipio según código DANE
            </p>
        </div>

        <!-- Régimen Tributario (opcional) -->
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-2">
                Régimen Tributario
            </label>
            <select name="tribute_id"
                    class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
                <option value="">Seleccione...</option>
                @foreach($tributes as $tribute)
                    <option value="{{ $tribute->id }}">{{ $tribute->name }} ({{ $tribute->code }})</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
```

### 3.2 Indicadores Visuales

- **Badge en Lista de Clientes:** Mostrar icono de facturación si `requires_electronic_billing = true`
- **Indicador en Formulario de Venta:** Mostrar si cliente tiene facturación activa
- **Validación Visual:** Campos requeridos marcados con `*` rojo
- **Mensajes de Error:** Específicos por campo y contexto

### 3.3 Comportamiento en Edición

- Si cliente ya tiene `requires_electronic_invoice = true`, cargar datos de `customer_tax_profiles`
- Cargar relaciones: `identification_document`, `municipality` (DianMunicipality), `tribute`, `legal_organization`
- Permitir desactivar facturación (elimina `customer_tax_profiles` por cascade)
- Validar que no haya facturas pendientes antes de desactivar
- Mostrar advertencia si se modifican campos críticos (identification, document_type)
- Validar que los IDs de catálogos seleccionados existan y estén activos

---

## FASE 4 – VALIDACIONES

### 4.1 Backend: FormRequest Condicional

```php
// app/Http/Requests/StoreCustomerRequest.php
public function rules(): array
{
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|unique:customers|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:100',
        'state' => 'nullable|string|max:100',
        'zip_code' => 'nullable|string|max:10',
        'notes' => 'nullable|string',
        'is_active' => 'boolean',
        'requires_electronic_invoice' => 'boolean',
    ];

    // Validaciones condicionales para facturación electrónica
    // ⚠️ IMPORTANTE: Solo valida si requires_electronic_invoice = true
    if ($this->boolean('requires_electronic_invoice')) {
        $rules = array_merge($rules, [
            'identification_document_id' => 'required|exists:dian_identification_documents,id',
            'identification' => 'required|string|max:20',
            'municipality_id' => [
                'required_if:is_colombian,true', // Obligatorio si es colombiano
                'nullable', // Opcional si es extranjero
                'exists:dian_municipalities,factus_id' // ⭐ Validar que existe el factus_id
            ],
        ]);

        // Obtener tipo de documento para validaciones específicas
        $identificationDocument = \App\Models\IdentificationDocument::find(
            $this->input('identification_document_id')
        );

        // Validaciones específicas según tipo de documento
        if ($identificationDocument && $identificationDocument->requires_dv) {
            $rules['dv'] = 'required|string|size:1';
        }

        // Validaciones para personas jurídicas (NIT)
        if ($identificationDocument && $identificationDocument->code === 'NIT') {
            $rules['company'] = 'required|string|max:255';
            $rules['legal_organization_id'] = 'nullable|exists:dian_legal_organizations,id';
            $rules['tribute_id'] = 'nullable|exists:dian_customer_tributes,id';
        }

        // Campos opcionales
        $rules['trade_name'] = 'nullable|string|max:255';
        $rules['tribute_id'] = 'nullable|exists:tributes,id';
    }

    return $rules;
}
```

### 4.2 Frontend: Validación JavaScript

```javascript
// Validación en tiempo real con Alpine.js
function validateElectronicInvoice() {
    if (!this.requiresElectronicInvoice) {
        return true; // No requiere validación - Cliente normal
    }

    const errors = [];

    if (!this.identificationDocumentId) {
        errors.push('Tipo de documento es obligatorio');
    }

    if (!this.identification) {
        errors.push('Número de identificación es obligatorio');
    }

    if (!this.municipalityId) {
        errors.push('Municipio es obligatorio');
    }

    // Validar DV si el documento lo requiere
    const selectedDoc = this.identificationDocuments.find(
        d => d.id == this.identificationDocumentId
    );
    if (selectedDoc && selectedDoc.requires_dv && !this.dv) {
        errors.push('Dígito verificador es obligatorio para este tipo de documento');
    }

    // Validar empresa si es persona jurídica
    if (selectedDoc && selectedDoc.code === 'NIT' && !this.company) {
        errors.push('Razón social es obligatoria para NIT');
    }

    return errors.length === 0;
}
```

### 4.3 Validaciones de Negocio (Service Layer)

```php
// app/Services/CustomerService.php
public function validateTaxProfileData(array $data): void
{
    if (!isset($data['requires_electronic_invoice']) || !$data['requires_electronic_invoice']) {
        return; // No requiere validación - Cliente normal
    }

    $identificationDocumentId = $data['identification_document_id'] ?? null;
    
    if (!$identificationDocumentId) {
        throw new \Exception('Tipo de documento es obligatorio para facturación electrónica');
    }

    $identificationDocument = DianIdentificationDocument::findOrFail($identificationDocumentId);
    
    // Validar campos según tipo de documento
    if ($identificationDocument->code === 'NIT') {
        $this->validateNIT($data);
    } else {
        $this->validateNaturalPerson($data);
    }

    // Validar que municipality_id (factus_id) existe en dian_municipalities
    if (isset($data['municipality_id'])) {
        $municipality = DianMunicipality::where('factus_id', $data['municipality_id'])->first();
        if (!$municipality) {
            throw new \Exception('El municipio seleccionado no es válido');
        }
    }

    // Validar que identification_document_id existe
    $identificationDocument = DianIdentificationDocument::find($data['identification_document_id'] ?? null);
    if (!$identificationDocument) {
        throw new \Exception('El tipo de documento seleccionado no es válido');
    }
}

private function validateNIT(array $data): void
{
    $required = ['identification', 'dv', 'company', 'municipality_id'];
    
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new \Exception("El campo {$field} es obligatorio para NIT");
        }
    }

    // Validar formato de NIT
    if (!preg_match('/^\d{9,10}$/', $data['identification'])) {
        throw new \Exception('El NIT debe tener entre 9 y 10 dígitos');
    }

    // Validar dígito verificador
    if (!$this->validateDV($data['identification'], $data['dv'])) {
        throw new \Exception('El dígito verificador no es válido');
    }

    // Validar que municipality_id (factus_id) existe en dian_municipalities
    if (isset($data['municipality_id'])) {
        if (!\App\Models\DianMunicipality::where('factus_id', $data['municipality_id'])->exists()) {
            throw new \Exception('El municipio seleccionado no es válido');
        }
    }
}
```

---

## FASE 5 – IMPLEMENTACIÓN (PROPUESTA)

### 5.1 Migraciones

#### **Migración 1: Limpiar y Agregar Flag a Customers**
```php
// database/migrations/XXXX_XX_XX_clean_and_add_electronic_invoice_flag_to_customers.php
Schema::table('customers', function (Blueprint $table) {
    // Eliminar campos no utilizados (si existen)
    if (Schema::hasColumn('customers', 'identification')) {
        $table->dropColumn('identification');
    }
    if (Schema::hasColumn('customers', 'type')) {
        $table->dropColumn('type');
    }
    
    // Agregar flag de facturación electrónica
    $table->boolean('requires_electronic_invoice')->default(false)->after('is_active');
    $table->index('requires_electronic_invoice');
});
```

#### **Migración 2: Crear Tablas de Catálogos DIAN**
```php
// database/migrations/XXXX_XX_XX_create_dian_identification_documents_table.php
// database/migrations/XXXX_XX_XX_create_dian_legal_organizations_table.php
// database/migrations/XXXX_XX_XX_create_dian_customer_tributes_table.php
// database/migrations/XXXX_XX_XX_create_municipalities_table.php
// database/migrations/XXXX_XX_XX_create_dian_document_types_table.php
// database/migrations/XXXX_XX_XX_create_dian_operation_types_table.php
// database/migrations/XXXX_XX_XX_create_dian_payment_methods_table.php
// database/migrations/XXXX_XX_XX_create_dian_payment_forms_table.php
// database/migrations/XXXX_XX_XX_create_dian_numbering_document_types_table.php
// database/migrations/XXXX_XX_XX_create_dian_correction_codes_table.php
// database/migrations/XXXX_XX_XX_create_dian_credit_note_types_table.php
// database/migrations/XXXX_XX_XX_create_dian_product_standards_table.php
// database/migrations/XXXX_XX_XX_create_dian_claim_concepts_table.php
// database/migrations/XXXX_XX_XX_create_dian_events_table.php
// database/migrations/XXXX_XX_XX_create_dian_fiscal_responsibilities_table.php
// (Ver estructuras completas en sección CATÁLOGOS DIAN)
```

#### **Migración 3: Crear Tabla de Perfiles Fiscales**
```php
// database/migrations/XXXX_XX_XX_create_customer_tax_profiles_table.php
// (Ver estructura completa en FASE 2.2)
```

#### **Migración 4: Crear Tabla de Facturas Electrónicas (GRUPO B - CRÍTICO)**
```php
// database/migrations/XXXX_XX_XX_create_electronic_invoices_table.php
Schema::create('electronic_invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sale_id')->constrained()->onDelete('cascade');
    $table->foreignId('customer_id')->constrained()->onDelete('cascade');
    $table->unsignedBigInteger('factus_numbering_range_id')->nullable(); // Guarda el factus_id del rango usado
    
    // Relaciones con catálogos DIAN
    $table->foreignId('document_type_id')->constrained('dian_document_types')->onDelete('restrict');
    $table->foreignId('operation_type_id')->constrained('dian_operation_types')->onDelete('restrict');
    $table->string('payment_method_code', 10)->nullable();
    $table->string('payment_form_code', 10)->nullable();
    
    // Identificación del documento
    $table->string('reference_code')->unique();  // Código único de referencia
    $table->string('document');                  // Número de factura
    
    // Estado de la factura
    $table->enum('status', [
        'pending',      // Pendiente de envío
        'sent',         // Enviada a DIAN
        'accepted',     // Aceptada por DIAN
        'rejected',     // Rechazada por DIAN
        'cancelled'     // Cancelada
    ])->default('pending');
    
    // Códigos DIAN
    $table->string('cufe')->nullable()->unique();  // CUFE (único)
    $table->text('qr')->nullable();                 // Código QR
    
    // Valores financieros
    $table->decimal('total', 15, 2);
    $table->decimal('tax_amount', 15, 2)->default(0);
    $table->decimal('gross_value', 15, 2);
    $table->decimal('discount_amount', 15, 2)->default(0);
    $table->decimal('surcharge_amount', 15, 2)->default(0);
    
    // Fechas
    $table->timestamp('validated_at')->nullable();  // Fecha validación DIAN
    
    // Payloads y respuestas (JSON)
    $table->json('payload_sent')->nullable();      // Lo que enviaste
    $table->json('response_dian')->nullable();     // Respuesta DIAN completa
    
    // URLs de documentos
    $table->string('pdf_url')->nullable();
    $table->string('xml_url')->nullable();
    
    $table->timestamps();
    
    // Foreign keys a catálogos
    $table->foreign('payment_method_code')->references('code')->on('dian_payment_methods')->onDelete('set null');
    $table->foreign('payment_form_code')->references('code')->on('dian_payment_forms')->onDelete('set null');
    
    // Índices
    $table->index('sale_id');
    $table->index('customer_id');
    $table->index('status');
    $table->index('cufe');
    $table->index('reference_code');
    $table->index('created_at');
    $table->index('document_type_id');
    $table->index('operation_type_id');
});
```

#### **Migración 5: Crear Tabla de Items de Factura Electrónica**
```php
// database/migrations/XXXX_XX_XX_create_electronic_invoice_items_table.php
Schema::create('electronic_invoice_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('electronic_invoice_id')->constrained()->onDelete('cascade');
    $table->foreignId('tribute_id')->nullable()->constrained('dian_customer_tributes')->onDelete('restrict');
    $table->foreignId('standard_code_id')->nullable()->constrained('dian_product_standards')->onDelete('restrict');
    
    // Unidad de medida (OBLIGATORIO) ⭐
    $table->unsignedBigInteger('unit_measure_id'); // FK a dian_measurement_units.factus_id
    
    // Información del item
    $table->string('code_reference')->nullable();  // SKU o código del producto
    $table->string('name');                        // Nombre del producto/item
    $table->decimal('quantity', 10, 3);
    $table->decimal('price', 15, 2);
    
    // Impuestos y descuentos
    $table->decimal('tax_rate', 5, 2)->default(0);      // Tasa de impuesto (%)
    $table->decimal('tax_amount', 15, 2)->default(0);    // Valor del impuesto
    $table->decimal('discount_rate', 5, 2)->default(0); // Tasa de descuento (%)
    $table->decimal('total', 15, 2);                    // Total del item
    
    $table->timestamps();
    
    // Índices
    $table->index('electronic_invoice_id');
    $table->index('tribute_id');
    $table->index('unit_measure_id');
    
    // Nota: No se agrega FK directa para unit_measure_id porque usa factus_id
    // Validar en aplicación que existe en dian_measurement_units.factus_id
});
```

#### **Migración 6: Crear Tabla de Configuración de Empresa (GRUPO D)**
```php
// database/migrations/XXXX_XX_XX_create_company_tax_settings_table.php
Schema::create('company_tax_settings', function (Blueprint $table) {
    $table->id();
    $table->string('company_name');              // Razón social
    $table->string('nit', 20);                   // NIT empresa
    $table->string('dv', 1);                     // Dígito verificador
    $table->string('email');                     // Email empresa
    // municipality_id guarda el factus_id de dian_municipalities
    $table->unsignedBigInteger('municipality_id'); // FK a dian_municipalities.factus_id
    $table->string('economic_activity', 10)->nullable(); // Código CIIU
    $table->string('logo_url')->nullable();       // URL logo
    $table->string('factus_company_id')->nullable()->unique(); // ID en Factus
    $table->timestamps();
    
    // Índices
    $table->index('municipality_id');
    $table->index('factus_company_id');
    
    // Constraint: Solo una configuración activa
    // Se puede implementar con un campo 'is_active' o simplemente tener un solo registro
});
```

#### **Migración 7: Crear Tabla de Rangos de Numeración (Sincronizados desde Factus)**
```php
// database/migrations/XXXX_XX_XX_create_factus_numbering_ranges_table.php
Schema::create('factus_numbering_ranges', function (Blueprint $table) {
    $table->id();
    
    // ID real de Factus (se usa al facturar)
    $table->unsignedBigInteger('factus_id')->unique();
    
    // Datos del documento
    $table->string('document');                    // "Factura de Venta", "Nota Crédito", etc.
    $table->string('document_code')->nullable();  // "21", "22", "23" (si se cruza con catálogo)
    $table->string('prefix')->nullable();          // Prefijo (ej: "FV", "NC")
    
    // Rango de numeración
    $table->unsignedBigInteger('range_from');       // Número inicial
    $table->unsignedBigInteger('range_to');         // Número final
    $table->unsignedBigInteger('current');          // Número actual (gestionado por Factus)
    
    // Datos de resolución DIAN
    $table->string('resolution_number')->nullable(); // Número de resolución
    $table->string('technical_key')->nullable();     // Clave técnica
    
    // Fechas de validez
    $table->date('start_date')->nullable();          // Fecha inicio
    $table->date('end_date')->nullable();            // Fecha fin
    
    // Estados
    $table->boolean('is_expired')->default(false);    // Rango vencido
    $table->boolean('is_active')->default(false);    // Rango activo
    
    $table->timestamps();
    
    // Índices
    $table->index('factus_id');
    $table->index('is_active');
    $table->index('is_expired');
    $table->index('document');
    $table->index('document_code');
    $table->index(['is_active', 'is_expired', 'document']); // Para búsquedas frecuentes
    $table->index('start_date');
    $table->index('end_date');
});
```

### 5.2 Modelos de Catálogos DIAN

```php
// app/Models/DianIdentificationDocument.php
class DianIdentificationDocument extends Model
{
    protected $fillable = ['code', 'name', 'requires_dv'];
    protected $casts = ['requires_dv' => 'boolean'];
    
    public function taxProfiles()
    {
        return $this->hasMany(CustomerTaxProfile::class, 'identification_document_id');
    }
}

// app/Models/DianLegalOrganization.php
class DianLegalOrganization extends Model
{
    protected $fillable = ['code', 'name'];
    
    public function taxProfiles()
    {
        return $this->hasMany(CustomerTaxProfile::class, 'legal_organization_id');
    }
}

// app/Models/DianCustomerTribute.php
class DianCustomerTribute extends Model
{
    protected $fillable = ['code', 'name'];
    
    public function taxProfiles()
    {
        return $this->hasMany(CustomerTaxProfile::class, 'tribute_id');
    }
}

// app/Models/DianMunicipality.php (Ya definido en sección de Municipios)
// Ver sección "🏙️ MUNICIPIOS DIAN (SINCRONIZADOS DESDE FACTUS)" para implementación completa

// app/Models/DianDocumentType.php
class DianDocumentType extends Model
{
    protected $fillable = ['code', 'name'];
    
    public function electronicInvoices()
    {
        return $this->hasMany(ElectronicInvoice::class, 'document_type_id');
    }
}

// app/Models/DianOperationType.php
class DianOperationType extends Model
{
    protected $fillable = ['code', 'name'];
    
    public function electronicInvoices()
    {
        return $this->hasMany(ElectronicInvoice::class, 'operation_type_id');
    }
}

// app/Models/DianPaymentMethod.php
class DianPaymentMethod extends Model
{
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = ['code', 'name'];
}

// app/Models/DianPaymentForm.php
class DianPaymentForm extends Model
{
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = ['code', 'name'];
}

// app/Models/DianNumberingDocumentType.php
class DianNumberingDocumentType extends Model
{
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = ['code', 'description'];
    
    // Nota: La relación con factus_numbering_ranges es opcional
    // Se puede cruzar por document_code si se necesita
    public function factusNumberingRanges()
    {
        return $this->hasMany(FactusNumberingRange::class, 'document_code', 'code');
    }
}

// app/Models/DianCorrectionCode.php
class DianCorrectionCode extends Model
{
    protected $fillable = ['code', 'description'];
}

// app/Models/DianCreditNoteType.php
class DianCreditNoteType extends Model
{
    protected $fillable = ['code', 'description'];
}

// app/Models/DianProductStandard.php
class DianProductStandard extends Model
{
    protected $fillable = ['name'];
    
    public function invoiceItems()
    {
        return $this->hasMany(ElectronicInvoiceItem::class, 'standard_code_id');
    }
}

// app/Models/DianClaimConcept.php
class DianClaimConcept extends Model
{
    protected $fillable = ['code', 'name'];
}

// app/Models/DianEvent.php
class DianEvent extends Model
{
    protected $fillable = ['code', 'name'];
}

// app/Models/DianFiscalResponsibility.php
class DianFiscalResponsibility extends Model
{
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = ['code', 'description'];
}
```

### 5.3 Modelos de Configuración de Empresa (GRUPO D)

```php
// app/Models/CompanyTaxSetting.php
class CompanyTaxSetting extends Model
{
    protected $fillable = [
        'company_name', 'nit', 'dv', 'email',
        'municipality_id', 'economic_activity',
        'logo_url', 'factus_company_id',
    ];

    // Relaciones
    public function municipality()
    {
        return $this->belongsTo(DianMunicipality::class, 'municipality_id', 'factus_id');
    }

    // Helpers
    public static function getInstance(): ?self
    {
        return self::first(); // Solo debe haber uno
    }

    public function isConfigured(): bool
    {
        return !empty($this->nit) && 
               !empty($this->dv) && 
               !empty($this->municipality_id) &&
               !empty($this->email);
    }

    public function hasFactusId(): bool
    {
        return !empty($this->factus_company_id);
    }
}
```

### 5.4 Modelos de Facturación Electrónica (GRUPO B)

```php
// app/Models/ElectronicInvoice.php
class ElectronicInvoice extends Model
{
    protected $fillable = [
        'sale_id', 'customer_id', 'factus_numbering_range_id',
        'document_type_id', 'operation_type_id',
        'payment_method_code', 'payment_form_code',
        'reference_code', 'document', 'status',
        'cufe', 'qr',
        'total', 'tax_amount', 'gross_value', 
        'discount_amount', 'surcharge_amount',
        'validated_at',
        'payload_sent', 'response_dian',
        'pdf_url', 'xml_url',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'gross_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'surcharge_amount' => 'decimal:2',
        'validated_at' => 'datetime',
        'payload_sent' => 'array',
        'response_dian' => 'array',
    ];

    // Relaciones
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function numberingRange()
    {
        return $this->belongsTo(FactusNumberingRange::class, 'factus_numbering_range_id', 'factus_id');
    }

    public function documentType()
    {
        return $this->belongsTo(DianNumberingDocumentType::class, 'document_type_code', 'code');
    }

    public function documentType()
    {
        return $this->belongsTo(DianDocumentType::class, 'document_type_id');
    }

    public function operationType()
    {
        return $this->belongsTo(DianOperationType::class, 'operation_type_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(DianPaymentMethod::class, 'payment_method_code', 'code');
    }

    public function paymentForm()
    {
        return $this->belongsTo(DianPaymentForm::class, 'payment_form_code', 'code');
    }

    public function items()
    {
        return $this->hasMany(ElectronicInvoiceItem::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Helpers
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'sent', 'accepted']);
    }
}

// app/Models/ElectronicInvoiceItem.php
class ElectronicInvoiceItem extends Model
{
    protected $fillable = [
        'electronic_invoice_id', 'tribute_id', 'standard_code_id',
        'code_reference', 'name',
        'quantity', 'price',
        'tax_rate', 'tax_amount',
        'discount_rate', 'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relaciones
    public function electronicInvoice()
    {
        return $this->belongsTo(ElectronicInvoice::class);
    }

    public function tribute()
    {
        return $this->belongsTo(DianCustomerTribute::class, 'tribute_id');
    }

    public function productStandard()
    {
        return $this->belongsTo(DianProductStandard::class, 'standard_code_id');
    }
}

// app/Models/FactusNumberingRange.php
class FactusNumberingRange extends Model
{
    protected $table = 'factus_numbering_ranges';

    protected $fillable = [
        'factus_id',
        'document',
        'document_code',
        'prefix',
        'range_from',
        'range_to',
        'current',
        'resolution_number',
        'technical_key',
        'start_date',
        'end_date',
        'is_expired',
        'is_active',
    ];

    protected $casts = [
        'factus_id' => 'integer',
        'range_from' => 'integer',
        'range_to' => 'integer',
        'current' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_expired' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function electronicInvoices()
    {
        return $this->hasMany(ElectronicInvoice::class, 'factus_numbering_range_id', 'factus_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('is_expired', false);
    }

    public function scopeValid($query)
    {
        return $query->where('is_active', true)
                    ->where('is_expired', false)
                    ->where(function($q) {
                        $q->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                    })
                    ->where(function($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
    }

    public function scopeForDocument($query, string $document)
    {
        return $query->where('document', $document);
    }

    // Helpers
    public function isValid(): bool
    {
        if (!$this->is_active || $this->is_expired) {
            return false;
        }

        if ($this->start_date && now()->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && now()->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    public function isExhausted(): bool
    {
        return $this->current >= $this->range_to;
    }

    public function getRemainingNumbers(): int
    {
        return max(0, $this->range_to - $this->current);
    }

    /**
     * Obtener el factus_id para enviar a Factus al facturar
     * Este es el ID que se usa en el payload
     */
    public function getFactusId(): int
    {
        return $this->factus_id;
    }
}
```

### 5.4 Actualizar Modelo Sale (Relación con ElectronicInvoice)

```php
// app/Models/Sale.php (agregar relación)
public function electronicInvoice()
{
    return $this->hasOne(ElectronicInvoice::class);
}

public function hasElectronicInvoice(): bool
{
    return $this->electronicInvoice !== null;
}

public function requiresElectronicInvoice(): bool
{
    return $this->customer->requires_electronic_invoice ?? false;
}
```

### 5.5 Modelo CustomerTaxProfile

```php
// app/Models/CustomerTaxProfile.php
class CustomerTaxProfile extends Model
{
    protected $fillable = [
        'customer_id',
        'identification_document_id',
        'identification',
        'dv',
        'legal_organization_id',
        'company',
        'trade_name',
        'tribute_id',
        'municipality_id',
    ];

    // Relaciones
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function identificationDocument()
    {
        return $this->belongsTo(DianIdentificationDocument::class, 'identification_document_id');
    }

    public function legalOrganization()
    {
        return $this->belongsTo(DianLegalOrganization::class, 'legal_organization_id');
    }

    public function tribute()
    {
        return $this->belongsTo(DianCustomerTribute::class, 'tribute_id');
    }

    public function municipality()
    {
        return $this->belongsTo(DianMunicipality::class, 'municipality_id', 'factus_id');
    }

    // Helpers
    public function requiresDV(): bool
    {
        return $this->identificationDocument?->requires_dv ?? false;
    }

    public function isJuridicalPerson(): bool
    {
        return $this->identificationDocument?->code === 'NIT';
    }
}
```

### 5.3 Relación en Modelo Customer

```php
// app/Models/Customer.php (agregar)
public function taxProfile()
{
    return $this->hasOne(CustomerTaxProfile::class);
}

public function requiresElectronicInvoice(): bool
{
    return $this->requires_electronic_invoice && 
           $this->taxProfile !== null;
}

public function hasCompleteTaxProfileData(): bool
{
    if (!$this->requires_electronic_invoice) {
        return false;
    }
    
    $profile = $this->taxProfile;
    if (!$profile) {
        return false;
    }
    
    // Validar campos obligatorios base
    $required = ['identification_document_id', 'identification', 'municipality_id'];
    
    foreach ($required as $field) {
        if (empty($profile->$field)) {
            return false;
        }
    }
    
    // Validar DV si el documento lo requiere
    if ($profile->requiresDV() && empty($profile->dv)) {
        return false;
    }
    
    // Validar empresa si es persona jurídica
    if ($profile->isJuridicalPerson() && empty($profile->company)) {
        return false;
    }
    
    return true;
}
```
```

### 5.4 Ajustes al Controlador

```php
// app/Http/Controllers/CustomerController.php
public function create()
{
    // Cargar catálogos DIAN necesarios para el formulario
    $identificationDocuments = DianIdentificationDocument::orderBy('id')->get();
    $legalOrganizations = DianLegalOrganization::orderBy('id')->get();
    $tributes = DianCustomerTribute::orderBy('id')->get();
    // Nota: Para municipios, usar autocomplete con API
    // No cargar todos los municipios en el controlador
    // Ver sección de UX/UI para implementación con autocomplete
    
    return view('customers.create', compact(
        'identificationDocuments',
        'legalOrganizations',
        'tributes',
        'municipalities'
    ));
}

public function store(StoreCustomerRequest $request)
{
    // Crear cliente normal (sin campos DIAN obligatorios)
    $customer = Customer::create($request->validated());

    // Solo si requiere facturación electrónica, crear perfil fiscal
    if ($request->boolean('requires_electronic_invoice')) {
        CustomerTaxProfile::create([
            'customer_id' => $customer->id,
            'identification_document_id' => $request->input('identification_document_id'),
            'identification' => $request->input('identification'),
            'dv' => $request->input('dv'),
            'legal_organization_id' => $request->input('legal_organization_id'),
            'company' => $request->input('company'),
            'trade_name' => $request->input('trade_name'),
            'tribute_id' => $request->input('tribute_id'),
            'municipality_id' => $request->input('municipality_id'),
        ]);
    }

    return redirect()->route('customers.index')
        ->with('success', 'Cliente creado exitosamente.');
}

public function edit(Customer $customer)
{
    $customer->load('taxProfile');
    
    // Cargar catálogos DIAN
    $identificationDocuments = DianIdentificationDocument::orderBy('id')->get();
    $legalOrganizations = DianLegalOrganization::orderBy('id')->get();
    $tributes = DianCustomerTribute::orderBy('id')->get();
    // Nota: Para municipios, usar autocomplete con API
    // No cargar todos los municipios en el controlador
    // Ver sección de UX/UI para implementación con autocomplete
    
    return view('customers.edit', compact(
        'customer',
        'identificationDocuments',
        'legalOrganizations',
        'tributes',
        'municipalities'
    ));
}
```

---

## RESUMEN EJECUTIVO

### ✅ **Ventajas de la Solución Propuesta:**
1. **No rompe datos existentes** - Clientes actuales siguen funcionando normalmente
2. **Tabla customers limpia** - NO tiene campos DIAN obligatorios, solo el flag
3. **Escalable** - Fácil agregar nuevos proveedores o campos en tabla relacionada
4. **Separación clara** - Datos comerciales (customers) vs. datos fiscales (customer_electronic_billing)
5. **Validaciones condicionales** - Solo valida cuando `requires_electronic_invoice = true`
6. **Cumplimiento DIAN** - Estructura alineada con normativa colombiana
7. **Flexibilidad** - Clientes normales no requieren datos DIAN

### ⚠️ **Consideraciones:**
1. Migración de datos existentes (si hay clientes que requieren facturación)
2. Validación de dígito verificador NIT (algoritmo DIAN)
3. Integración futura con Factus (campos adicionales posibles)
4. Auditoría de cambios en datos fiscales

### 📋 **Próximos Pasos (Post-Implementación):**

#### **Fase 1: Configuración Inicial (GRUPO D)**
1. **Configuración de Empresa**
   - CRUD de `company_tax_settings`
   - Validación de datos completos
   - Registro en Factus para obtener `factus_company_id`
   - Configuración de logo para PDFs

2. **Gestión de Rangos de Numeración**
   - CRUD de `numbering_ranges`
   - Validación de rangos activos
   - Control de números disponibles
   - Alertas cuando rango está por agotarse
   - Asociación con tipo de documento (21, 22, 23, etc.)

#### **Fase 2: Integración con Factus**
3. **Integración con API Factus**
   - Servicio para registrar empresa en Factus
   - Servicio para enviar facturas
   - Manejo de respuestas y errores
   - Reintentos automáticos
   - Webhooks para eventos DIAN

4. **Generación de Facturas Electrónicas**
   - Servicio `ElectronicInvoiceService`
   - Construcción de payload según estructura DIAN
   - Validación de datos antes de enviar
   - Inclusión de datos de empresa y cliente

#### **Fase 3: Documentos y Reportes**
5. **Generación de Documentos**
   - PDF de factura electrónica (con logo de empresa)
   - XML según estructura DIAN
   - Almacenamiento de archivos (S3, local, etc.)
   - URLs públicas para descarga

6. **Reportes Fiscales**
   - Facturas aceptadas/rechazadas
   - Reportes por período
   - Exportación para contabilidad
   - Reportes de rangos de numeración

7. **Historial y Auditoría**
   - Consulta de facturas emitidas
   - Visualización de payload y respuesta
   - Descarga de PDF/XML
   - Trazabilidad completa

#### **Fase 4: Funcionalidades Avanzadas (Futuro)**
8. **Notas Crédito y Débito**
   - CRUD de notas crédito/débito
   - Relación con factura original
   - Códigos de corrección

9. **Eventos DIAN**
   - Seguimiento de eventos (030, 031, 032, etc.)
   - Reclamos
   - Aceptación tácita/expresa

10. **Documentos Soporte**
    - Generación de documentos soporte
    - Rangos de numeración para documentos soporte

### 🔐 **Consideraciones de Seguridad (GRUPO B y D):**

#### **Facturas Electrónicas:**
- `cufe` debe ser único (índice único) - CUFE es único por DIAN
- `reference_code` debe ser único (índice único) - Código interno único
- Validar que `numbering_range` esté activo antes de usar
- No permitir modificar facturas aceptadas por DIAN (inmutabilidad)
- Solo permitir cancelación según reglas DIAN (solo en estados válidos)
- Guardar historial de cambios en estados (auditoría)
- Proteger acceso a `payload_sent` y `response_dian` (datos sensibles)
- Validar integridad: `electronic_invoice_items` debe coincidir con `sale_items`

#### **Configuración de Empresa (GRUPO D):**
- Solo usuarios con permisos especiales pueden modificar `company_tax_settings`
- Validar NIT y DV antes de guardar
- Validar que `municipality_id` existe y está activo
- Proteger `factus_company_id` (no debe modificarse manualmente)
- Validar formato de email
- Validar que logo_url es una URL válida (si se proporciona)

#### **Rangos de Numeración (Sincronizados desde Factus):**
- Validar que `range_from` < `range_to` (en sincronización)
- Validar que `start_date` < `end_date` (si están definidas)
- `factus_id` debe ser único (viene de Factus)
- `current` se gestiona en Factus, no localmente
- Solo se actualiza mediante sincronización, no manualmente
- Alertar cuando rango está por agotarse (ej: 80% usado)
- No permitir eliminar rangos que tienen facturas asociadas (solo se desactivan en Factus)
- Validar que existe `technical_key` antes de facturar electrónicamente

### 📊 **Ventajas de la Estructura GRUPO B:**
1. **Auditoría Completa**: Guardas lo enviado y lo recibido
2. **Consultas Eficientes**: Items en tabla normalizada, no solo JSON
3. **Trazabilidad**: Puedes rastrear cada factura desde creación hasta aceptación
4. **Reportes Fáciles**: Consultas SQL directas sin parsear JSON
5. **Independencia**: No dependes de cambios en estructura JSON de Factus
6. **Recuperación**: Puedes reconstruir facturas desde la base de datos
7. **Validación**: Puedes validar integridad entre Sale y ElectronicInvoice

---

## 🔄 SERVICIO DE SINCRONIZACIÓN DE RANGOS (GRUPO D)

### **Comando Artisan: `factus:sync-numbering-ranges`**

```php
// app/Console/Commands/SyncFactusNumberingRanges.php
namespace App\Console\Commands;

use App\Services\FactusNumberingRangeService;
use Illuminate\Console\Command;

class SyncFactusNumberingRanges extends Command
{
    protected $signature = 'factus:sync-numbering-ranges';
    protected $description = 'Sincroniza rangos de numeración desde Factus';

    public function handle(FactusNumberingRangeService $service): int
    {
        $this->info('Sincronizando rangos de numeración desde Factus...');
        
        try {
            $synced = $service->sync();
            $this->info("✅ Sincronizados {$synced} rangos de numeración.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Error al sincronizar: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
```

### **Servicio: `FactusNumberingRangeService`**

```php
// app/Services/FactusNumberingRangeService.php
namespace App\Services;

use App\Models\FactusNumberingRange;
use App\Services\FactusApiService; // Servicio para llamadas a API Factus
use Illuminate\Support\Facades\Log;

class FactusNumberingRangeService
{
    public function __construct(
        private FactusApiService $apiService
    ) {}

    /**
     * Sincroniza todos los rangos de numeración desde Factus
     * 
     * @return int Número de rangos sincronizados
     * @throws \Exception Si falla la sincronización
     */
    public function sync(): int
    {
        // 1. Obtener token de autenticación
        $token = $this->apiService->getAuthToken();
        
        // 2. Llamar API de Factus
        $response = $this->apiService->get('/v1/numbering-ranges', [
            'filter' => ['is_active' => 1]
        ]);
        
        if (!isset($response['data']['data'])) {
            throw new \Exception('Respuesta inválida de Factus API');
        }
        
        $synced = 0;
        
        // 3. Iterar y sincronizar
        foreach ($response['data']['data'] as $range) {
            FactusNumberingRange::updateOrCreate(
                ['factus_id' => $range['id']],
                [
                    'document' => $range['document'] ?? null,
                    'document_code' => $range['document_code'] ?? null,
                    'prefix' => $range['prefix'] ?? null,
                    'range_from' => $range['from'] ?? 0,
                    'range_to' => $range['to'] ?? 0,
                    'current' => $range['current'] ?? 0,
                    'resolution_number' => $range['resolution_number'] ?? null,
                    'technical_key' => $range['technical_key'] ?? null,
                    'start_date' => isset($range['start_date']) ? $range['start_date'] : null,
                    'end_date' => isset($range['end_date']) ? $range['end_date'] : null,
                    'is_expired' => $range['is_expired'] ?? false,
                    'is_active' => $range['is_active'] ?? false,
                ]
            );
            $synced++;
        }
        
        Log::info("Sincronizados {$synced} rangos de numeración desde Factus");
        
        return $synced;
    }

    /**
     * Obtiene un rango válido para un tipo de documento
     * 
     * @param string $document Tipo de documento ("Factura de Venta", "Nota Crédito", etc.)
     * @return FactusNumberingRange
     * @throws \Exception Si no hay rango válido
     */
    public function getValidRangeForDocument(string $document): FactusNumberingRange
    {
        $range = FactusNumberingRange::valid()
            ->forDocument($document)
            ->first();
        
        if (!$range) {
            throw new \Exception("No hay rango de numeración válido para: {$document}");
        }
        
        if ($range->isExhausted()) {
            throw new \Exception("El rango de numeración está agotado para: {$document}");
        }
        
        if (empty($range->technical_key)) {
            throw new \Exception("El rango no tiene technical_key configurado para: {$document}");
        }
        
        return $range;
    }
}
```

### **Job Programado (Opcional, Recomendado)**

```php
// app/Jobs/SyncFactusNumberingRangesJob.php
namespace App\Jobs;

use App\Services\FactusNumberingRangeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncFactusNumberingRangesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(FactusNumberingRangeService $service): void
    {
        try {
            $synced = $service->sync();
            Log::info("Job: Sincronizados {$synced} rangos de numeración desde Factus");
        } catch (\Exception $e) {
            Log::error("Job: Error al sincronizar rangos de numeración: {$e->getMessage()}");
            throw $e; // Re-lanzar para que el job falle y se reintente
        }
    }
}
```

### **Programar Job Diario (Kernel.php)**

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    // Sincronizar rangos de numeración diariamente a las 2 AM
    $schedule->job(new SyncFactusNumberingRangesJob())
             ->dailyAt('02:00');
}
```

## 🔌 INTEGRACIÓN CON API FACTUS

### **Endpoint: Crear y Validar Factura**

El endpoint `/v1/bills/validate` permite crear y validar una factura electrónica. Esta sección describe cómo utilizar el endpoint junto con la información necesaria para garantizar su correcto uso.

#### **📋 Configuración del Endpoint:**

| Método | URL |
|--------|-----|
| **POST** | `/v1/bills/validate` |

**Pruebas (Sandbox):**
- URL Completa: `https://api-sandbox.factus.com.co/v1/bills/validate`

**Producción:**
- URL Completa: `https://api.factus.com.co/v1/bills/validate`

#### **📤 Encabezados (Headers) Requeridos:**

| Parámetro | Tipo | Descripción | Requerido |
|-----------|------|-------------|-----------|
| `Authorization` | string | Token de acceso en formato `Bearer {token}` | ✅ |
| `Content-Type` | string | Tipo de contenido: `application/json` | ✅ |
| `Accept` | string | Tipo de respuesta: `application/json` | ✅ |

**⚠️ Nota**: El token de acceso se envía en el header `Authorization` con el formato `Bearer {access_token}`, no como un parámetro `access_token` separado.

#### **⚠️ IMPORTANTE - Autenticación OAuth2:**

**La API de Factus utiliza OAuth2 para autenticación.**

- **Sistema**: OAuth2
- **Credenciales**: Se utilizan las credenciales de acceso al sistema suministradas por Factus (`client_id`, `client_secret`, `username`, `password`)
- **Token de Acceso**: Se genera mediante el endpoint de autenticación y debe usarse para realizar cualquier petición
- **Duración**: El token tiene una duración de **10 minutos (600 segundos)**
- **Token de Refresco**: Se debe usar el `refresh_token` para generar un nuevo `access_token` cuando expire
- **Renovación**: Implementar lógica de renovación automática usando el `refresh_token` antes de que expire el `access_token`
- **Documentación**: Para más información, consultar la documentación de OAuth2 de Factus

**⚠️ Nota**: Es necesario incluir un `access_token` válido en todas las peticiones a los endpoints de Factus.

#### **📦 Cuerpo (Body) Requerido:**

El cuerpo de la solicitud debe enviarse en formato JSON e incluir toda la información de la factura a crear y validar.

**Estructura del Body:**
- El body contiene directamente los datos de la factura (no se envuelve en un objeto `body`)
- Debe incluir: datos del emisor, adquiriente, items, valores, métodos de pago, etc.
- Ver sección de **Campos** para detalles específicos de cada campo requerido

**Nota**: Para comprender los datos requeridos, revise la estructura del payload que se debe enviar en la solicitud al endpoint. Si tiene dudas con algún campo, revise la descripción de los campos en la sección de **Campos**.

### **📚 Recursos Disponibles para Sincronizar**

Los siguientes endpoints están disponibles para obtener información de uso frecuente. **Recomendamos hacerla persistente en su sistema**, ya que rara vez se modifica, o usar los endpoints directamente (teniendo en cuenta los tiempos de respuesta):

1. **Rangos de numeración** (`/v1/numbering-ranges`)
   - Sincronizar mediante: `factus:sync-numbering-ranges`
   - Tabla: `factus_numbering_ranges`
   - Ver sección: **GRUPO D — Rangos de Numeración**

2. **Municipios** (`/v1/municipalities`)
   - Sincronizar mediante: `factus:sync-municipalities`
   - Tabla: `dian_municipalities`
   - Ver sección: **MUNICIPIOS DIAN**

3. **Tributos** (`/v1/tributes`)
   - Sincronizar mediante: `factus:sync-tributes` (a implementar)
   - Tabla: `dian_customer_tributes` (ya seedeada, pero puede sincronizarse)

4. **Unidades de medida** (`/v1/measurement-units`)
   - Sincronizar mediante: `factus:sync-measurement-units`
   - Tabla: `dian_measurement_units`
   - Ver sección: **UNIDADES DE MEDIDA DIAN**

#### **💡 Recomendación:**

> **Sincronizar estos recursos una vez y guardarlos localmente**, en lugar de consultarlos en cada factura. Esto mejora:
> - ⚡ **Rendimiento**: No depende de la API en cada factura
> - 🛡️ **Confiabilidad**: Funciona aunque la API esté temporalmente inaccesible
> - 📊 **Auditoría**: Permite rastrear cambios históricos
> - 🔍 **Búsquedas rápidas**: Consultas locales más eficientes

### **🔧 Servicio: `FactusApiService` (Base para Llamadas a API - OAuth2)**

```php
// app/Services/FactusApiService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FactusApiService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $username;
    private string $password;
    private string $tokenEndpoint;

    public function __construct()
    {
        $this->baseUrl = config('factus.api_url');
        $this->clientId = config('factus.client_id');
        $this->clientSecret = config('factus.client_secret');
        $this->username = config('factus.username');
        $this->password = config('factus.password');
        $this->tokenEndpoint = '/oauth/token';
    }

    /**
     * Obtiene o renueva el token de acceso usando OAuth2
     * 
     * Implementa el flujo OAuth2 con soporte para refresh tokens
     * 
     * @return string Token de acceso
     * @throws \Exception Si falla la autenticación
     */
    public function getAuthToken(): string
    {
        // Intentar obtener token del cache
        $tokenData = Cache::get('factus_token_data');
        
        if ($tokenData && isset($tokenData['access_token'])) {
            // Verificar si el token aún es válido
            $expiresAt = $tokenData['expires_at'] ?? null;
            
            if ($expiresAt && now()->lt($expiresAt)) {
                return $tokenData['access_token'];
            }
            
            // Token expirado, intentar renovar con refresh_token
            if (isset($tokenData['refresh_token'])) {
                try {
                    return $this->refreshAccessToken($tokenData['refresh_token']);
                } catch (\Exception $e) {
                    Log::warning('Error al renovar token con refresh_token, obteniendo nuevo token', [
                        'error' => $e->getMessage()
                    ]);
                    // Continuar para obtener nuevo token
                }
            }
        }

        // Generar nuevo token usando OAuth2
        return $this->requestNewAccessToken();
    }

    /**
     * Solicita un nuevo token de acceso usando OAuth2
     * 
     * Endpoint: /oauth/token
     * Método: POST
     * Grant Type: password
     * 
     * @return string Token de acceso
     * @throws \Exception Si falla la autenticación
     */
    private function requestNewAccessToken(): string
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->asForm()->post("{$this->baseUrl}{$this->tokenEndpoint}", [
            'grant_type' => 'password',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Error al autenticar con Factus OAuth2: ' . $response->body());
        }

        $data = $response->json();
        $accessToken = $data['access_token'] ?? null;
        $refreshToken = $data['refresh_token'] ?? null;
        $expiresIn = $data['expires_in'] ?? 600; // Default 10 minutos (600 segundos) según Factus
        $tokenType = $data['token_type'] ?? 'Bearer';

        if (!$accessToken) {
            throw new \Exception('No se recibió access_token de Factus');
        }

        // Calcular tiempo de expiración (renovar 1 minuto antes para seguridad)
        $expiresAt = now()->addSeconds($expiresIn - 60);

        // Guardar datos del token (incluyendo refresh_token si existe)
        $tokenData = [
            'access_token' => $accessToken,
            'token_type' => $tokenType,
            'expires_at' => $expiresAt,
            'expires_in' => $expiresIn,
        ];

        if ($refreshToken) {
            $tokenData['refresh_token'] = $refreshToken;
        }

        Cache::put('factus_token_data', $tokenData, now()->addSeconds($expiresIn));

        Log::info('Nuevo token de acceso obtenido de Factus', [
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in' => $expiresIn,
            'token_type' => $tokenType,
            'has_refresh_token' => !empty($refreshToken),
        ]);

        return $accessToken;
    }

    /**
     * Renueva el token de acceso usando el refresh token
     * 
     * Endpoint: /oauth/token
     * Método: POST
     * Grant Type: refresh_token
     * 
     * Permite actualizar el token de acceso mediante el uso de un refresh token
     * previamente generado, sin necesidad de username y password.
     * 
     * @param string $refreshToken Token de refresco previamente generado
     * @return string Nuevo token de acceso
     * @throws \Exception Si falla la renovación
     */
    private function refreshAccessToken(string $refreshToken): string
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->asForm()->post("{$this->baseUrl}{$this->tokenEndpoint}", [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Error al renovar token con Factus OAuth2: ' . $response->body());
        }

        $data = $response->json();
        $accessToken = $data['access_token'] ?? null;
        $newRefreshToken = $data['refresh_token'] ?? $refreshToken; // Usar el nuevo si viene, sino mantener el anterior
        $expiresIn = $data['expires_in'] ?? 600; // Usar el valor de la respuesta, default 10 minutos
        $tokenType = $data['token_type'] ?? 'Bearer';

        if (!$accessToken) {
            throw new \Exception('No se recibió access_token al renovar token');
        }

        // Calcular tiempo de expiración (renovar 1 minuto antes para seguridad)
        $expiresAt = now()->addSeconds($expiresIn - 60);

        // Actualizar datos del token (incluyendo nuevo refresh_token si viene)
        $tokenData = [
            'access_token' => $accessToken,
            'token_type' => $tokenType,
            'refresh_token' => $newRefreshToken,
            'expires_at' => $expiresAt,
            'expires_in' => $expiresIn,
        ];

        Cache::put('factus_token_data', $tokenData, now()->addSeconds($expiresIn));

        Log::info('Token de acceso renovado usando refresh_token', [
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in' => $expiresIn,
            'token_type' => $tokenType,
            'has_new_refresh_token' => ($newRefreshToken !== $refreshToken),
        ]);

        return $accessToken;
    }

    /**
     * Realiza una petición GET a la API de Factus
     * 
     * @param string $endpoint Endpoint relativo (ej: '/v1/numbering-ranges')
     * @param array $params Parámetros de consulta
     * @return array Respuesta de la API
     * @throws \Exception Si falla la petición
     */
    public function get(string $endpoint, array $params = []): array
    {
        $token = $this->getAuthToken();

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->get("{$this->baseUrl}{$endpoint}", $params);

        if (!$response->successful()) {
            // Si el error es 401, el token puede haber expirado, intentar renovar
            if ($response->status() === 401) {
                Log::warning('Token expirado en GET request, renovando token', ['endpoint' => $endpoint]);
                Cache::forget('factus_token_data'); // Limpiar token expirado
                $token = $this->getAuthToken(); // Obtener nuevo token
                
                // Reintentar la petición con el nuevo token
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                ])->get("{$this->baseUrl}{$endpoint}", $params);
                
                if (!$response->successful()) {
                    throw new \Exception("Error en GET {$endpoint} después de renovar token: " . $response->body());
                }
            } else {
                throw new \Exception("Error en GET {$endpoint}: " . $response->body());
            }
        }

        return $response->json();
    }

    /**
     * Realiza una petición POST a la API de Factus
     * 
     * @param string $endpoint Endpoint relativo (ej: '/v1/bills/validate')
     * @param array $data Datos a enviar
     * @return array Respuesta de la API
     * @throws \Exception Si falla la petición
     */
    public function post(string $endpoint, array $data): array
    {
        $token = $this->getAuthToken();

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post("{$this->baseUrl}{$endpoint}", $data);

        if (!$response->successful()) {
            // Si el error es 401, el token puede haber expirado, intentar renovar
            if ($response->status() === 401) {
                Log::warning('Token expirado en POST request, renovando token', ['endpoint' => $endpoint]);
                Cache::forget('factus_token_data'); // Limpiar token expirado
                $token = $this->getAuthToken(); // Obtener nuevo token
                
                // Reintentar la petición con el nuevo token
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post("{$this->baseUrl}{$endpoint}", $data);
                
                if (!$response->successful()) {
                    $errorBody = $response->body();
                    Log::error("Error en POST {$endpoint} después de renovar token", [
                        'status' => $response->status(),
                        'body' => $errorBody,
                        'data_sent' => $data,
                    ]);
                    throw new \Exception("Error en POST {$endpoint} después de renovar token: {$errorBody}");
                }
            } else {
                $errorBody = $response->body();
                Log::error("Error en POST {$endpoint}", [
                    'status' => $response->status(),
                    'body' => $errorBody,
                    'data_sent' => $data,
                ]);
                throw new \Exception("Error en POST {$endpoint}: {$errorBody}");
            }
        }

        return $response->json();
    }
}
```

### **📝 Configuración (config/factus.php)**

```php
// config/factus.php
return [
    // URLs base (sandbox o producción)
    'api_url' => env('FACTUS_API_URL', 'https://api.factus.com.co'),
    
    // Credenciales OAuth2
    'client_id' => env('FACTUS_CLIENT_ID'),
    'client_secret' => env('FACTUS_CLIENT_SECRET'),
    'username' => env('FACTUS_USERNAME'),
    'password' => env('FACTUS_PASSWORD'),
    
    // Endpoint de autenticación (fijo)
    'token_endpoint' => '/oauth/token',
];
```

### **🔐 Variables de Entorno (.env)**

```env
# Factus OAuth2 - URLs
# Pruebas (Sandbox)
FACTUS_API_URL=https://api-sandbox.factus.com.co

# Producción
# FACTUS_API_URL=https://api.factus.com.co

# Credenciales OAuth2 (suministradas por Factus)
FACTUS_CLIENT_ID=tu_client_id_aqui
FACTUS_CLIENT_SECRET=tu_client_secret_aqui
FACTUS_USERNAME=tu_username_aqui
FACTUS_PASSWORD=tu_password_aqui
```

**⚠️ Nota**: Las credenciales (`client_id`, `client_secret`, `username`, `password`) son suministradas por Factus al dar acceso al sistema. Contacta al administrador de la API para obtenerlas.

### **📚 Información Adicional sobre OAuth2 en Factus**

#### **Endpoint de Autenticación:**

**Pruebas (Sandbox):**
- URL: `https://api-sandbox.factus.com.co/oauth/token`
- Método: `POST`

**Producción:**
- URL: `https://api.factus.com.co/oauth/token`
- Método: `POST`

**⚠️ Nota**: El mismo endpoint (`/oauth/token`) se usa tanto para obtener el token inicial como para renovarlo. La diferencia está en el `grant_type` y los parámetros enviados.

#### **Detalles del Endpoint:**

##### **Obtener Token Inicial:**
- **Grant Type**: `password`
- **Duración del token**: **10 minutos (600 segundos)**
- **Headers requeridos**: `Accept: application/json`
- **Content-Type**: `application/x-www-form-urlencoded` (form-data)

##### **Renovar Token:**
- **Grant Type**: `refresh_token`
- **Duración del token**: Variable (generalmente 1 hora / 3600 segundos según respuesta)
- **Headers requeridos**: `Accept: application/json`
- **Content-Type**: `application/x-www-form-urlencoded` (form-data)
- **Ventaja**: No requiere `username` ni `password`, solo el `refresh_token` previamente obtenido

#### **Parámetros para Obtener Token Inicial (Grant Type: password):**

| Parámetro | Descripción | Requerido |
|-----------|-------------|-----------|
| `grant_type` | Tipo de autenticación | ✅ `password` |
| `client_id` | Identificador único del cliente | ✅ |
| `client_secret` | Secreto asociado al cliente | ✅ |
| `username` | Correo electrónico del usuario | ✅ |
| `password` | Contraseña del usuario | ✅ |

#### **Parámetros para Renovar Token (Grant Type: refresh_token):**

| Parámetro | Descripción | Requerido |
|-----------|-------------|-----------|
| `grant_type` | Tipo de concesión | ✅ `refresh_token` |
| `client_id` | ID del cliente proporcionado por el servicio | ✅ |
| `client_secret` | Secreto del cliente proporcionado por el servicio | ✅ |
| `refresh_token` | El refresh token previamente generado | ✅ |

**⚠️ Nota**: Para renovar el token, NO se requiere `username` ni `password`, solo el `refresh_token` previamente obtenido.

#### **Respuesta Exitosa - Token Inicial (200 OK):**

```json
{
  "token_type": "Bearer",
  "expires_in": 600,
  "access_token": "tu access token",
  "refresh_token": "tu refresh token"
}
```

#### **Respuesta Exitosa - Refresh Token (200 OK):**

```json
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "tu nuevo access token",
  "refresh_token": "tu nuevo refresh token"
}
```

**⚠️ Nota**: La respuesta del refresh token puede incluir un nuevo `refresh_token` o mantener el anterior. Siempre usar el `refresh_token` que viene en la respuesta más reciente.

#### **Credenciales:**

- **Credenciales**: Suministradas por Factus al dar acceso al sistema
- **Obtención**: Contactar al administrador de la API para obtener credenciales
- **Token de Acceso**: Necesario para todas las peticiones a los endpoints protegidos
- **Token de Refresco**: Usado para obtener nuevos tokens de acceso sin necesidad de `username` y `password`
- **Renovación**: Tras expirar (10 minutos), será necesario renovarlo usando el `refresh_token`

#### **Flujo de Autenticación:**

1. **Obtener Token Inicial**:
   - Endpoint: `/oauth/token`
   - Grant Type: `password`
   - Requiere: `client_id`, `client_secret`, `username`, `password`
   - Retorna: `access_token`, `refresh_token`, `expires_in` (600 segundos)

2. **Renovar Token**:
   - Endpoint: `/oauth/token` (mismo endpoint)
   - Grant Type: `refresh_token`
   - Requiere: `client_id`, `client_secret`, `refresh_token`
   - NO requiere: `username`, `password`
   - Retorna: `access_token`, `refresh_token` (nuevo o mismo), `expires_in`

#### **Importante:**

- El token es **obligatorio** para realizar cualquier solicitud a los endpoints protegidos de la API
- Después de 10 minutos, el token expira y debe renovarse usando el `refresh_token`
- Usar `refresh_token` para renovar sin necesidad de `username`/`password`
- El `refresh_token` puede ser reemplazado por uno nuevo en cada renovación - siempre usar el más reciente
- El `expires_in` del refresh token puede variar (generalmente 1 hora / 3600 segundos)

#### **Ejemplo de Uso del Refresh Token:**

```php
// El método refreshAccessToken() ya está implementado en FactusApiService
// Se llama automáticamente cuando el token expira

// Ejemplo manual de cómo se haría:
$response = Http::withHeaders([
    'Accept' => 'application/json',
])->asForm()->post('https://api-sandbox.factus.com.co/oauth/token', [
    'grant_type' => 'refresh_token',
    'client_id' => env('FACTUS_CLIENT_ID'),
    'client_secret' => env('FACTUS_CLIENT_SECRET'),
    'refresh_token' => $refreshTokenObtenidoPreviamente,
]);

if ($response->successful()) {
    $data = $response->json();
    $newAccessToken = $data['access_token'];
    $newRefreshToken = $data['refresh_token']; // Puede ser nuevo o el mismo
    $expiresIn = $data['expires_in'];
    
    // Guardar para uso futuro
    Cache::put('factus_token_data', [
        'access_token' => $newAccessToken,
        'refresh_token' => $newRefreshToken,
        'expires_at' => now()->addSeconds($expiresIn - 60),
        'expires_in' => $expiresIn,
    ], now()->addSeconds($expiresIn));
}
```

**⚠️ Importante**: Las credenciales (`client_id` y `client_secret`) son sensibles y deben protegerse. Nunca las expongas en código fuente público.

### **🔒 Seguridad y Buenas Prácticas**

#### **1. Manejo de Credenciales:**
- ✅ Usar variables de entorno (`.env`) para credenciales
- ✅ Agregar `.env` a `.gitignore`
- ✅ No hardcodear credenciales en código
- ✅ Rotar credenciales periódicamente si es posible
- ✅ Proteger `client_id`, `client_secret`, `username`, y `password`
- ❌ Nunca exponer credenciales en logs o respuestas de error

#### **2. Manejo de Tokens:**
- ✅ Cachear tokens para evitar solicitudes innecesarias
- ✅ Renovar tokens antes de que expiren (1 minuto antes recomendado)
- ✅ Usar refresh tokens cuando estén disponibles (no requiere username/password)
- ✅ Actualizar refresh_token si viene uno nuevo en la respuesta
- ✅ Manejar errores 401 (Unauthorized) renovando tokens automáticamente
- ✅ Logging de renovaciones de token (sin exponer valores)
- ✅ Verificar `expires_in` en cada respuesta para calcular correctamente la expiración

#### **3. Manejo de Errores:**
- ✅ Reintentar automáticamente cuando el token expira (401)
- ✅ Limpiar cache de token expirado antes de renovar
- ✅ Logging de errores de autenticación (sin credenciales)
- ✅ No exponer mensajes de error detallados al usuario final

#### **4. Performance:**
- ✅ Cachear tokens con tiempo de expiración adecuado
- ✅ Evitar solicitudes redundantes de tokens
- ✅ Usar refresh tokens en lugar de credenciales cuando sea posible

### **Uso en ElectronicInvoiceService (Actualizado)**

```php
// En app/Services/ElectronicInvoiceService.php
use App\Services\FactusApiService;

class ElectronicInvoiceService
{
    public function __construct(
        private FactusApiService $apiService
    ) {}

    public function createFromSale(Sale $sale): ElectronicInvoice
    {
        // ... validaciones anteriores ...
        
        // Obtener rango válido usando el servicio
        $numberingRangeService = app(FactusNumberingRangeService::class);
        $range = $numberingRangeService->getValidRangeForDocument('Factura de Venta');
        
        // Crear factura electrónica
        $invoice = ElectronicInvoice::create([
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'factus_numbering_range_id' => $range->factus_id, // ⭐ Usa factus_id
            // ... otros campos ...
        ]);
        
        // Construir payload
        $payload = $this->buildPayload($invoice);
        
        // ⭐ IMPORTANTE: Enviar factus_id del rango, NO el id local
        $payload['numbering_range_id'] = $range->factus_id;
        
        // ⭐ IMPORTANTE: Cada item debe incluir unit_measure_id (factus_id)
        // El buildPayload ya debe incluir esto desde electronic_invoice_items
        
        // Enviar a Factus usando el servicio
        $response = $this->apiService->post('/v1/bills/validate', $payload);
        
        // Guardar respuesta
        $invoice->update([
            'status' => $this->mapStatusFromResponse($response),
            'cufe' => $response['cufe'] ?? null,
            'qr' => $response['qr'] ?? null,
            'payload_sent' => $payload,
            'response_dian' => $response,
            'validated_at' => now(),
        ]);
        
        // ... procesar respuesta ...
        
        return $invoice;
    }

    private function sendToFactus(array $payload): array
    {
        return $this->apiService->post('/v1/bills/validate', $payload);
    }

    /**
     * Construye el payload para enviar a Factus
     * 
     * @param ElectronicInvoice $invoice
     * @return array
     */
    private function buildPayload(ElectronicInvoice $invoice): array
    {
        $company = CompanyTaxSetting::getInstance();
        $customer = $invoice->customer;
        $taxProfile = $customer->taxProfile;
        
        return [
            // Datos del emisor (empresa)
            'issuer' => [
                'nit' => $company->nit,
                'dv' => $company->dv,
                'company_name' => $company->company_name,
                'email' => $company->email,
                'municipality_id' => $company->municipality->factus_id, // ⭐ factus_id
                'economic_activity' => $company->economic_activity,
            ],
            
            // Datos del cliente (adquiriente)
            'customer' => [
                'identification_document_code' => $taxProfile->identificationDocument->code,
                'identification' => $taxProfile->identification,
                'dv' => $taxProfile->dv,
                'company_name' => $taxProfile->company ?? $customer->name,
                'municipality_id' => $taxProfile->municipality->factus_id, // ⭐ factus_id
            ],
            
            // Datos del documento
            'document_type' => $invoice->documentType->code,
            'operation_type' => $invoice->operationType->code,
            'document' => $invoice->document,
            'reference_code' => $invoice->reference_code,
            
            // Rango de numeración (usa factus_id, NO el id local)
            'numbering_range_id' => $invoice->numberingRange->factus_id,
            
            // Items (con unit_measure_id obligatorio) ⭐
            'items' => $invoice->items->map(function($item) {
                return [
                    'code_reference' => $item->code_reference,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'unit_measure_id' => $item->unitMeasure->factus_id, // ⭐ factus_id OBLIGATORIO
                    'tax_rate' => $item->tax_rate,
                    'tax_amount' => $item->tax_amount,
                    'total' => $item->total,
                ];
            })->toArray(),
            
            // Valores
            'gross_value' => $invoice->gross_value,
            'tax_amount' => $invoice->tax_amount,
            'discount_amount' => $invoice->discount_amount,
            'total' => $invoice->total,
            
            // Métodos de pago
            'payment_method_code' => $invoice->payment_method_code,
            'payment_form_code' => $invoice->payment_form_code,
        ];
    }
}
```

---

**Documento preparado para revisión y aprobación antes de implementación.**
