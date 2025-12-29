# Plan: Gestión de Salidas de Dinero (Gastos/Egresos)

## Estado Actual
- No existe un sistema para registrar la salida de dinero de la caja.
- Los recepcionistas no pueden reportar gastos menores o pagos realizados durante su turno.

## Estado Final
Un módulo que permita:
- Registrar salidas de dinero (gastos, pagos a proveedores, etc.).
- Vincular la salida al usuario (recepcionista) que la realiza.
- Reportar el motivo y el monto exacto.
- Visualizar un historial de salidas filtrado por fecha.

## Archivos a Crear/Modificar

### Base de Datos
1. `database/migrations/YYYY_MM_DD_HHMMSS_create_cash_outflows_table.php`
   - Campos: `id`, `user_id`, `amount`, `reason`, `date`, `timestamps`.

### Modelos
1. `app/Models/CashOutflow.php`
   - Relaciones: `user`.
   - Casts: `amount` (decimal), `date` (datetime).

### Livewire
1. `app/Livewire/CashOutflowManager.php`
   - Lógica para listar y crear registros.
2. `resources/views/livewire/cash-outflow-manager.blade.php`
   - Interfaz con tabla y modal de creación.

### Rutas
1. `routes/web.php`
   - Agregar ruta `/cash-outflows`.

### Interfaz
1. `resources/views/layouts/app.blade.php`
   - Agregar enlace en el sidebar.

## Lista de Tareas
1. [x] Crear migración `cash_outflows`.
2. [x] Crear modelo `CashOutflow`.
3. [x] Crear componente Livewire `CashOutflowManager`.
4. [x] Implementar vista del componente.
5. [x] Configurar rutas y navegación.
6. [x] Agregar permisos básicos (Administrador y Recepcionistas).

