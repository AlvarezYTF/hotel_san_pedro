# Checklist de Limpieza y Adaptación del Proyecto Hotel San Pedro

## ✅ FASE 1: ELIMINACIÓN DE MÓDULOS NO APLICABLES

### Módulo de Reparaciones
- [ ] Eliminar `app/Models/Repair.php`
- [ ] Eliminar `app/Http/Controllers/RepairController.php`
- [ ] Eliminar migración `*_create_repairs_table.php`
- [ ] Eliminar `database/factories/RepairFactory.php`
- [ ] Eliminar directorio `resources/views/repairs/`
- [ ] Eliminar rutas de repairs en `routes/web.php`
- [ ] Eliminar permisos de repairs en `database/seeders/RoleSeeder.php`
- [ ] Eliminar relación `repairs()` del modelo `Customer`

### Módulo de Ventas
- [ ] Eliminar `app/Models/Sale.php`
- [ ] Eliminar `app/Models/SaleItem.php`
- [ ] Eliminar `app/Http/Controllers/SaleController.php`
- [ ] Eliminar `app/Services/SaleService.php`
- [ ] Eliminar `app/Policies/SalePolicy.php`
- [ ] Eliminar migraciones `*_create_sales_table.php` y `*_create_sale_items_table.php`
- [ ] Eliminar factories `SaleFactory.php` y `SaleItemFactory.php`
- [ ] Eliminar directorio `resources/views/sales/`
- [ ] Eliminar rutas de sales en `routes/web.php`
- [ ] Eliminar permisos de sales en `database/seeders/RoleSeeder.php`
- [ ] Eliminar relación `sales()` del modelo `Customer`
- [ ] Verificar y eliminar referencias en `ElectronicInvoiceController` si es necesario

### Módulo de Órdenes/Pedidos
- [ ] Eliminar `app/Models/Order.php`
- [ ] Eliminar `app/Models/OrderDetails.php`
- [ ] Eliminar directorio completo `app/Http/Controllers/Order/`
- [ ] Eliminar `app/Livewire/OrderForm.php`
- [ ] Eliminar migraciones `*_create_orders_table.php` y `*_create_order_details_table.php`
- [ ] Eliminar directorio `resources/views/orders/` si existe
- [ ] Eliminar rutas de orders en `routes/web.php`

### Carrito de Compras
- [ ] Eliminar `app/Http/Controllers/CartController.php`
- [ ] Eliminar `app/Livewire/ProductCart.php`
- [ ] Eliminar migración `*_create_shoppingcart_table.php`
- [ ] Eliminar `config/cart.php` si existe
- [ ] Eliminar rutas de cart en `routes/web.php`

### Roles No Aplicables
- [ ] Eliminar rol "Vendedor" de `database/seeders/RoleSeeder.php`
- [ ] Eliminar rol "Técnico" de `database/seeders/RoleSeeder.php`
- [ ] Eliminar rol "Cliente" de `database/seeders/RoleSeeder.php`
- [ ] Eliminar permisos relacionados con ventas y reparaciones

### Otros Archivos
- [ ] Evaluar y eliminar `app/Http/Controllers/InvoiceController.php` si no es necesario
- [ ] Evaluar `app/Livewire/PurchaseForm.php` - eliminar si no aplica
- [ ] Evaluar `app/Livewire/SearchProduct.php` - adaptar o eliminar

---

## 🔄 FASE 2: ADAPTACIÓN DE MÓDULOS EXISTENTES

### Modelo Customer
- [ ] Eliminar relación `sales()` del modelo
- [ ] Eliminar relación `repairs()` del modelo
- [ ] Eliminar método `getTotalSpentAttribute()` si no aplica
- [ ] Eliminar método `getTotalRepairsAttribute()`
- [ ] Agregar validación de unicidad de documento de identidad
- [ ] Adaptar campos si es necesario para contexto hotelero
- [ ] Verificar que `taxProfile()` se mantenga (necesario para facturación)

### Modelo Product
- [ ] Eliminar relación `saleItems()` del modelo
- [ ] Adaptar para productos de consumo del hotel
- [ ] Mantener lógica de stock y alertas de bajo stock
- [ ] Verificar campos necesarios para inventario hotelero

### Controlador CustomerController
- [ ] Eliminar referencias a ventas y reparaciones
- [ ] Adaptar validaciones para contexto hotelero
- [ ] Mantener funcionalidad de perfil fiscal (necesaria para facturación)
- [ ] Agregar validación de unicidad de documento

### Controlador ProductController
- [ ] Adaptar para inventario hotelero
- [ ] Mantener CRUD básico
- [ ] Adaptar vistas si es necesario

### Roles y Permisos
- [ ] Crear rol "Recepcionista Día" en `RoleSeeder.php`
- [ ] Crear rol "Recepcionista Noche" en `RoleSeeder.php`
- [ ] Definir permisos específicos para cada rol
- [ ] Mantener rol "Administrador" con todos los permisos
- [ ] Actualizar `UserSeeder.php` con usuarios de prueba para nuevos roles

### Rutas (routes/web.php)
- [ ] Eliminar todas las rutas de repairs
- [ ] Eliminar todas las rutas de sales
- [ ] Eliminar todas las rutas de orders
- [ ] Eliminar todas las rutas de cart
- [ ] Verificar y limpiar rutas de reportes (adaptar si es necesario)
- [ ] Mantener rutas de facturación electrónica
- [ ] Mantener rutas de autenticación

### Vistas
- [ ] Limpiar referencias a ventas y reparaciones en layout principal
- [ ] Actualizar menú de navegación
- [ ] Eliminar enlaces a módulos eliminados
- [ ] Adaptar dashboard si muestra información de ventas/reparaciones

---

## 🆕 FASE 3: IMPLEMENTACIÓN DE MÓDULOS NUEVOS

### Gestión de Reservas

#### Modelos
- [ ] Crear `app/Models/Room.php`
  - [ ] Campos: number, room_type_id, status, floor, description
  - [ ] Relación con RoomType
  - [ ] Relación con Reservations
- [ ] Crear `app/Models/RoomType.php`
  - [ ] Campos: name, description, capacity, price_per_night
  - [ ] Relación con Rooms
- [ ] Crear `app/Models/Reservation.php`
  - [ ] Campos: customer_id, room_id, check_in, check_out, status, total_price, notes
  - [ ] Relación con Customer
  - [ ] Relación con Room
  - [ ] Validaciones de fechas

#### Migraciones
- [ ] Crear `create_room_types_table.php`
- [ ] Crear `create_rooms_table.php`
- [ ] Crear `create_reservations_table.php`
- [ ] Agregar índices para optimización de consultas de disponibilidad

#### Controlador
- [ ] Crear `app/Http/Controllers/ReservationController.php`
  - [ ] Método `index()` - Listado de reservas
  - [ ] Método `create()` - Formulario de creación
  - [ ] Método `store()` - Guardar reserva con validaciones
  - [ ] Método `edit()` - Formulario de edición
  - [ ] Método `update()` - Actualizar reserva
  - [ ] Método `show()` - Ver detalle de reserva
  - [ ] Método `destroy()` - Eliminar reserva
  - [ ] Método para verificar disponibilidad

#### Validaciones
- [ ] Validar que check_in < check_out
- [ ] Validar que check_in >= fecha actual
- [ ] Validar disponibilidad de habitación en rango de fechas
- [ ] Prevenir solapamiento de reservas
- [ ] Validar que la habitación esté disponible (status)

#### Vistas
- [ ] Crear `resources/views/reservations/index.blade.php`
- [ ] Crear `resources/views/reservations/create.blade.php`
- [ ] Crear `resources/views/reservations/edit.blade.php`
- [ ] Crear `resources/views/reservations/show.blade.php`
- [ ] Implementar selector de fechas con validación
- [ ] Implementar selector de habitaciones disponibles

#### Rutas
- [ ] Agregar rutas resource para reservations
- [ ] Agregar ruta para verificar disponibilidad (API)

#### Seeders
- [ ] Crear `RoomTypeSeeder.php` con tipos básicos
- [ ] Crear `RoomSeeder.php` con habitaciones de ejemplo
- [ ] Actualizar `DatabaseSeeder.php`

### Gestión de Turnos

#### Modelo
- [ ] Crear `app/Models/Shift.php`
  - [ ] Campos: user_id, shift_date, start_time, end_time, notes
  - [ ] Relación con User
  - [ ] Método para calcular horas trabajadas

#### Migración
- [ ] Crear `create_shifts_table.php`

#### Controlador
- [ ] Crear `app/Http/Controllers/ShiftController.php`
  - [ ] Método `index()` - Listado de turnos
  - [ ] Método `create()` - Formulario de creación
  - [ ] Método `store()` - Guardar turno
  - [ ] Método `edit()` - Formulario de edición
  - [ ] Método `update()` - Actualizar turno
  - [ ] Método `reports()` - Reportes de horas trabajadas
  - [ ] Método para generar reporte por usuario y rango de fechas

#### Vistas
- [ ] Crear `resources/views/shifts/index.blade.php`
- [ ] Crear `resources/views/shifts/create.blade.php`
- [ ] Crear `resources/views/shifts/edit.blade.php`
- [ ] Crear `resources/views/shifts/reports.blade.php`

#### Rutas
- [ ] Agregar rutas resource para shifts
- [ ] Agregar ruta para reportes

#### Seeders
- [ ] Crear `ShiftSeeder.php` con datos de ejemplo

---

## 🔒 FASE 4: SEGURIDAD Y VALIDACIONES

### Límite de Usuarios Activos
- [ ] Crear middleware para verificar límite de usuarios
- [ ] Implementar lógica de conteo de usuarios activos
- [ ] Agregar validación en registro de usuarios
- [ ] Mostrar mensaje de error cuando se alcance el límite

### Restricciones por Rol
- [ ] Definir permisos específicos para Recepcionista Día
- [ ] Definir permisos específicos para Recepcionista Noche
- [ ] Implementar middleware de permisos en rutas
- [ ] Verificar restricciones en vistas (ocultar opciones según rol)

### Reglas de Negocio
- [ ] Validar que no se puedan crear reservas en el pasado
- [ ] Validar disponibilidad antes de confirmar reserva
- [ ] Implementar reglas de cancelación de reservas
- [ ] Validar horarios de turnos (no solapamiento)

---

## 📊 FASE 5: REPORTES Y DASHBOARD

### Dashboard
- [ ] Adaptar dashboard para contexto hotelero
- [ ] Mostrar estadísticas de reservas
- [ ] Mostrar habitaciones ocupadas/disponibles
- [ ] Mostrar alertas de inventario bajo
- [ ] Eliminar estadísticas de ventas y reparaciones

### Reportes
- [ ] Adaptar `ReportController` para contexto hotelero
- [ ] Crear reporte de reservas por período
- [ ] Crear reporte de ocupación
- [ ] Crear reporte de horas trabajadas (turnos)
- [ ] Crear reporte de inventario

---

## 📚 FASE 6: DOCUMENTACIÓN

### Documentación Técnica
- [ ] Documentar arquitectura general del sistema
- [ ] Documentar integración con API de Factus
- [ ] Documentar modelo de datos (diagrama ER)
- [ ] Documentar tablas normalizadas
- [ ] Documentar variables, funciones y métodos principales
- [ ] Documentar flujos operativos
- [ ] Crear manual de usuario

### Documentación de Código
- [ ] Agregar comentarios PHPDoc a modelos
- [ ] Agregar comentarios PHPDoc a controladores
- [ ] Agregar comentarios PHPDoc a servicios
- [ ] Documentar validaciones y reglas de negocio

---

## 🧪 FASE 7: PRUEBAS Y VALIDACIÓN

### Pruebas Funcionales
- [ ] Probar autenticación con nuevos roles
- [ ] Probar creación de reservas
- [ ] Probar validación de disponibilidad
- [ ] Probar edición de reservas
- [ ] Probar gestión de turnos
- [ ] Probar reportes de horas trabajadas
- [ ] Probar facturación electrónica (ya funcional)
- [ ] Probar gestión de clientes
- [ ] Probar gestión de inventario

### Pruebas de Integración
- [ ] Verificar integración entre módulos
- [ ] Verificar que las reservas se relacionan correctamente con clientes
- [ ] Verificar que la facturación funciona con reservas
- [ ] Verificar permisos y restricciones por rol

### Pruebas de Rendimiento
- [ ] Optimizar consultas de disponibilidad
- [ ] Agregar índices necesarios en base de datos
- [ ] Verificar tiempos de respuesta

---

## ✅ VERIFICACIÓN FINAL

- [ ] Todas las rutas eliminadas no existen en `routes/web.php`
- [ ] No hay referencias a modelos eliminados en el código
- [ ] Los nuevos roles están creados y funcionando
- [ ] Las reservas funcionan correctamente
- [ ] Los turnos funcionan correctamente
- [ ] La facturación electrónica sigue funcionando
- [ ] El dashboard muestra información correcta
- [ ] Los permisos están correctamente aplicados
- [ ] La documentación está completa
- [ ] El sistema está listo para presentación

---

**Nota**: Marcar cada ítem como completado cuando se finalice. Este checklist debe actualizarse conforme se avance en el desarrollo.

