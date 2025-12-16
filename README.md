# Hotel San Pedro - Sistema de Gestión Hotelera

Sistema web desarrollado en Laravel 12 para la gestión integral del Hotel San Pedro.

## 📋 Estado del Proyecto según Cronograma

### ✅ MÓDULOS IMPLEMENTADOS Y ÚTILES

#### 1. Infraestructura Base (Día 1) - ✅ COMPLETO
- ✅ Proyecto Laravel configurado
- ✅ Conexión a base de datos establecida
- ✅ Sistema de autenticación funcional
- ✅ Sistema de roles y permisos (Spatie Laravel Permission)
- ✅ Estructura base de carpetas
- ✅ Layout principal con Blade

**Nota:** Los roles actuales (Administrador, Vendedor, Técnico, Cliente) deben reemplazarse por:
- Administrador
- Recepcionista Día
- Recepcionista Noche

#### 2. Facturación Electrónica (Días 6-7) - ✅ COMPLETO
- ✅ Integración con API de Factus
- ✅ Modelos: `ElectronicInvoice`, `ElectronicInvoiceItem`
- ✅ Servicios: `FactusApiService`, `ElectronicInvoiceService`
- ✅ Controlador: `ElectronicInvoiceController`
- ✅ Generación y descarga de facturas en PDF
- ✅ Consulta de facturas por cliente y fecha
- ✅ Configuración fiscal de empresa

**Archivos relacionados:**
- `app/Services/FactusApiService.php`
- `app/Services/ElectronicInvoiceService.php`
- `app/Http/Controllers/ElectronicInvoiceController.php`
- `app/Models/ElectronicInvoice.php`
- `app/Models/ElectronicInvoiceItem.php`
- `config/factus.php`
- Migraciones relacionadas con DIAN y Factus

#### 3. Gestión de Clientes (Día 2) - ⚠️ PARCIAL
- ✅ Modelo `Customer` implementado
- ✅ Controlador `CustomerController` implementado
- ✅ Vistas de clientes (necesitan adaptación)
- ⚠️ El modelo está orientado a ventas, necesita adaptación para hotel
- ⚠️ Falta validación de unicidad de documento de identidad

**Archivos a adaptar:**
- `app/Models/Customer.php` - Eliminar relaciones con `sales` y `repairs`
- `app/Http/Controllers/CustomerController.php` - Adaptar para contexto hotelero
- Vistas de clientes en `resources/views/`

---

### ❌ MÓDULOS QUE DEBEN ELIMINARSE

#### 1. Módulo de Reparaciones (NO APLICA AL HOTEL)
**Archivos a eliminar:**
- `app/Models/Repair.php`
- `app/Http/Controllers/RepairController.php`
- `database/migrations/*_create_repairs_table.php`
- `database/factories/RepairFactory.php`
- Vistas en `resources/views/repairs/`
- Rutas relacionadas con repairs en `routes/web.php`
- Permisos relacionados: `view_repairs`, `create_repairs`, `edit_repairs`, `delete_repairs`, `update_repair_status`

#### 2. Módulo de Ventas (NO APLICA AL HOTEL)
**Archivos a eliminar:**
- `app/Models/Sale.php`
- `app/Models/SaleItem.php`
- `app/Http/Controllers/SaleController.php`
- `app/Services/SaleService.php`
- `app/Policies/SalePolicy.php`
- `database/migrations/*_create_sales_table.php`
- `database/migrations/*_create_sale_items_table.php`
- `database/factories/SaleFactory.php`
- `database/factories/SaleItemFactory.php`
- Vistas en `resources/views/sales/`
- Rutas relacionadas con sales en `routes/web.php`
- Permisos relacionados: `view_sales`, `create_sales`, `edit_sales`, `delete_sales`

#### 3. Módulo de Órdenes/Pedidos (NO APLICA AL HOTEL)
**Archivos a eliminar:**
- `app/Models/Order.php`
- `app/Models/OrderDetails.php`
- `app/Http/Controllers/Order/OrderController.php`
- `app/Http/Controllers/Order/DueOrderController.php`
- `app/Http/Controllers/Order/OrderPendingController.php`
- `app/Http/Controllers/Order/OrderVendidoController.php`
- `app/Livewire/OrderForm.php`
- `database/migrations/*_create_orders_table.php`
- `database/migrations/*_create_order_details_table.php`
- Vistas relacionadas con orders

#### 4. Carrito de Compras (NO APLICA AL HOTEL)
**Archivos a eliminar:**
- `app/Http/Controllers/CartController.php`
- `app/Livewire/ProductCart.php`
- `database/migrations/*_create_shoppingcart_table.php`
- `config/cart.php`

#### 5. Roles No Aplicables
**Eliminar del seeder:**
- Rol "Vendedor"
- Rol "Técnico"
- Rol "Cliente"

#### 6. Categorías y Proveedores (EVALUAR)
**Archivos a evaluar:**
- `app/Models/Category.php` - Solo si se necesita para inventario del hotel
- `app/Models/Supplier.php` - Solo si se necesita para inventario del hotel
- `app/Http/Controllers/CategoryController.php`
- Si el inventario del hotel no requiere categorías/proveedores, eliminar

#### 7. Productos (ADAPTAR PARA INVENTARIO DEL HOTEL)
**Archivos a adaptar:**
- `app/Models/Product.php` - Mantener pero adaptar para inventario hotelero
- `app/Http/Controllers/ProductController.php` - Adaptar para inventario hotelero
- Eliminar relaciones con `saleItems`
- Adaptar para productos de consumo del hotel (toallas, productos de limpieza, etc.)

#### 8. Otros Archivos No Aplicables
- `app/Http/Controllers/ReportController.php` - Adaptar reportes para contexto hotelero
- `app/Http/Controllers/InvoiceController.php` - Verificar si es necesario o usar solo ElectronicInvoiceController
- `app/Livewire/SearchProduct.php` - Adaptar o eliminar según necesidad
- `app/Livewire/PurchaseForm.php` - Eliminar si no aplica

---

### 🚧 MÓDULOS PENDIENTES DE IMPLEMENTAR

#### 1. Gestión de Reservas (Día 3) - ❌ NO IMPLEMENTADO

**Modelos a crear:**
- `app/Models/Reservation.php` (o `Booking.php`)
- `app/Models/Room.php` (habitaciones)
- `app/Models/RoomType.php` (tipos de habitación: individual, doble, suite, etc.)

**Migraciones a crear:**
- `create_rooms_table.php`
- `create_room_types_table.php`
- `create_reservations_table.php`

**Controlador a crear:**
- `app/Http/Controllers/ReservationController.php`

**Funcionalidades requeridas:**
- ✅ Crear reservas asociadas a clientes
- ✅ Validación de fechas de entrada y salida
- ✅ Validación de disponibilidad de habitaciones
- ✅ Prevenir reservas con fechas solapadas
- ✅ Edición de reservas
- ✅ Consulta y listado de reservas
- ✅ Relación entre reservas, clientes y habitaciones

**Vistas a crear:**
- `resources/views/reservations/index.blade.php`
- `resources/views/reservations/create.blade.php`
- `resources/views/reservations/edit.blade.php`
- `resources/views/reservations/show.blade.php`

#### 2. Gestión de Inventario (Día 4) - ⚠️ PARCIAL

**Estado actual:**
- ✅ Modelo `Product` existe pero orientado a ventas
- ✅ Controlador `ProductController` existe
- ⚠️ Necesita adaptación para inventario hotelero

**Adaptaciones necesarias:**
- Adaptar modelo `Product` para productos de consumo del hotel
- Implementar lógica de entradas y salidas de inventario
- Actualización automática de stock
- Alertas por bajo stock
- Validar restricciones para evitar salidas sin disponibilidad

**Vistas a adaptar:**
- Adaptar vistas de productos para contexto hotelero
- Crear vistas de reportes de inventario

#### 3. Gestión de Turnos (Día 8) - ❌ NO IMPLEMENTADO

**Modelos a crear:**
- `app/Models/Shift.php` (turnos de trabajo)

**Migraciones a crear:**
- `create_shifts_table.php`

**Controlador a crear:**
- `app/Http/Controllers/ShiftController.php`

**Funcionalidades requeridas:**
- ✅ Registro de horarios de entrada y salida
- ✅ Asignación manual de turnos
- ✅ Generación de reportes de horas trabajadas
- ✅ Relación con usuarios (empleados)

**Vistas a crear:**
- `resources/views/shifts/index.blade.php`
- `resources/views/shifts/create.blade.php`
- `resources/views/shifts/reports.blade.php`

#### 4. Roles Específicos del Hotel - ❌ NO IMPLEMENTADO

**Roles a crear:**
- Recepcionista Día
- Recepcionista Noche

**Permisos a definir:**
- Permisos específicos para cada rol según el cronograma
- Restricciones de acceso según rol

**Archivo a modificar:**
- `database/seeders/RoleSeeder.php`

#### 5. Validaciones y Reglas de Negocio (Día 9) - ⚠️ PARCIAL

**Implementar:**
- ✅ Límite de usuarios activos permitidos
- ✅ Restricciones de acceso por rol
- ✅ Validación de reglas de negocio del hotel
- ✅ Control de accesos y permisos

#### 6. Documentación Técnica (Día 10) - ❌ PENDIENTE

**Documentar:**
- Arquitectura general del sistema
- Integración con API de Factus
- Tablas normalizadas del sistema
- Variables, funciones y métodos principales
- Modelo lógico de datos
- Interfaz de usuario y flujos operativos

---

## 📊 Resumen de Estado por Día del Cronograma

| Día | Módulo | Estado | Acción Requerida |
|-----|--------|--------|------------------|
| 1 | Preparación entorno y estructura base | ✅ Completo | Adaptar roles |
| 2 | Gestión de clientes | ⚠️ Parcial | Adaptar modelo y validaciones |
| 3 | Gestión de reservas | ❌ No implementado | **CREAR COMPLETAMENTE** |
| 4 | Inventario | ⚠️ Parcial | Adaptar para contexto hotelero |
| 5 | Hito de presentación | ⚠️ Pendiente | Integrar módulos |
| 6-7 | Facturación electrónica | ✅ Completo | Mantener |
| 8 | Gestión de turnos | ❌ No implementado | **CREAR COMPLETAMENTE** |
| 9 | Seguridad y reglas de negocio | ⚠️ Parcial | Completar validaciones |
| 10 | Documentación técnica | ❌ Pendiente | **CREAR COMPLETAMENTE** |

---

## 🗂️ Estructura de Archivos a Eliminar

### Modelos
```
app/Models/Repair.php
app/Models/Sale.php
app/Models/SaleItem.php
app/Models/Order.php
app/Models/OrderDetails.php
```

### Controladores
```
app/Http/Controllers/RepairController.php
app/Http/Controllers/SaleController.php
app/Http/Controllers/CartController.php
app/Http/Controllers/Order/ (directorio completo)
app/Http/Controllers/InvoiceController.php (evaluar)
```

### Migraciones
```
database/migrations/*_create_repairs_table.php
database/migrations/*_create_sales_table.php
database/migrations/*_create_sale_items_table.php
database/migrations/*_create_orders_table.php
database/migrations/*_create_order_details_table.php
database/migrations/*_create_shoppingcart_table.php
```

### Vistas
```
resources/views/repairs/ (directorio completo)
resources/views/sales/ (directorio completo)
resources/views/orders/ (directorio completo)
resources/views/cart/ (si existe)
```

### Livewire
```
app/Livewire/OrderForm.php
app/Livewire/ProductCart.php
app/Livewire/PurchaseForm.php (evaluar)
```

### Seeders
```
database/seeders/SupplierSeeder.php (evaluar)
```

### Factories
```
database/factories/RepairFactory.php
database/factories/SaleFactory.php
database/factories/SaleItemFactory.php
```

---

## 🚀 Plan de Acción Recomendado

### Fase 1: Limpieza (Prioridad Alta)
1. Eliminar módulo de reparaciones
2. Eliminar módulo de ventas
3. Eliminar módulo de órdenes
4. Eliminar carrito de compras
5. Actualizar `RoleSeeder` con roles del hotel
6. Limpiar rutas en `web.php`

### Fase 2: Adaptación (Prioridad Alta)
1. Adaptar modelo `Customer` para contexto hotelero
2. Adaptar modelo `Product` para inventario hotelero
3. Adaptar controladores y vistas de clientes
4. Adaptar controladores y vistas de inventario

### Fase 3: Implementación Crítica (Prioridad Alta)
1. Crear modelo `Room` y `RoomType`
2. Crear modelo `Reservation`
3. Implementar `ReservationController`
4. Implementar validaciones de disponibilidad
5. Crear vistas de reservas

### Fase 4: Implementación de Turnos (Prioridad Media)
1. Crear modelo `Shift`
2. Implementar `ShiftController`
3. Crear vistas de turnos
4. Implementar reportes de horas trabajadas

### Fase 5: Validaciones y Seguridad (Prioridad Media)
1. Implementar límite de usuarios activos
2. Completar restricciones por rol
3. Validar reglas de negocio

### Fase 6: Documentación (Prioridad Baja)
1. Documentar arquitectura
2. Documentar integración con Factus
3. Documentar modelo de datos
4. Documentar funciones y métodos

---

## 🔧 Configuración Actual

### Tecnologías Utilizadas
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade + TailwindCSS + Alpine.js
- **Base de Datos**: MySQL/PostgreSQL/SQLite
- **Autenticación**: Laravel Sanctum + Spatie Laravel Permission
- **PDF**: DomPDF para generación de facturas
- **Facturación**: Integración con API de Factus

### Dependencias Principales
- `spatie/laravel-permission`: Sistema de roles y permisos
- `barryvdh/laravel-dompdf`: Generación de PDFs
- `laravel/sanctum`: Autenticación API

---

## 📝 Notas Importantes

1. **Facturación Electrónica**: El módulo de facturación con Factus está completamente implementado y funcional. No requiere cambios.

2. **Autenticación**: El sistema de autenticación está funcional. Solo requiere actualizar los roles.

3. **Base de Datos**: Revisar todas las migraciones antes de ejecutar `migrate:fresh` en producción.

4. **Rutas**: Limpiar `routes/web.php` eliminando todas las rutas relacionadas con módulos eliminados.

5. **Middleware**: Verificar que los middlewares de permisos estén correctamente configurados para los nuevos roles.

---

## 👥 Equipo de Desarrollo

- Jefferson Alexander Álvarez Rodríguez
- Camilo Andrés Hernández González
- Cristian Camilo Camacho Morales
- Mario Alexander Cañola Cano

---

**Última actualización**: Análisis inicial del proyecto según cronograma de desarrollo.
