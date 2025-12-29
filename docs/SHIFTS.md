## Sistema de Turnos (Shifts)

### Permisos (Spatie)
- **manage_shift_handovers**: iniciar turno, entregar turno, crear acta de entrega, confirmar recepción.
- **view_shift_handovers**: ver listado / detalle de actas y pantalla de recepción.
- **create_shift_cash_outs**: crear/eliminar retiros de caja de turno.
- **view_shift_cash_outs**: ver listado de retiros de caja.

Roles incluidos en `database/seeders/RoleSeeder.php`:
- **Recepcionista Día**
- **Recepcionista Noche**
- **Administrador** (tiene todos los permisos)

### Rutas
Dashboards:
- GET `/dashboard/recepcion/dia` (name: `dashboard.receptionist.day`)
- GET `/dashboard/recepcion/noche` (name: `dashboard.receptionist.night`)
- GET `/dashboard/receptionist-day` (name: `dashboard.receptionist.day.en`)
- GET `/dashboard/receptionist-night` (name: `dashboard.receptionist.night.en`)

Turnos:
- POST `/shifts/start` (name: `shift.start`) **manage_shift_handovers**
- POST `/shifts/end` (name: `shift.end`) **manage_shift_handovers**

Actas de entrega/recepción:
- GET `/shift-handovers` (name: `shift-handovers.index`) **view_shift_handovers|manage_shift_handovers**
- GET `/shift-handovers/create` (name: `shift-handovers.create`) **manage_shift_handovers**
- POST `/shift-handovers` (name: `shift-handovers.store`) **manage_shift_handovers**
- GET `/shift-handovers/receive` (name: `shift-handovers.receive`) **view_shift_handovers|manage_shift_handovers**
- POST `/shift-handovers/receive` (name: `shift-handovers.store-reception`) **manage_shift_handovers**
- GET `/shift-handovers/{id}` (name: `shift-handovers.show`) **view_shift_handovers|manage_shift_handovers**

Retiros de caja:
- GET `/shift-cash-outs` (name: `shift-cash-outs.index`) **view_shift_cash_outs|create_shift_cash_outs**
- GET `/shift-cash-outs/create` (name: `shift-cash-outs.create`) **create_shift_cash_outs**
- POST `/shift-cash-outs` (name: `shift-cash-outs.store`) **create_shift_cash_outs**
- DELETE `/shift-cash-outs/{id}` (name: `shift-cash-outs.destroy`) **create_shift_cash_outs**

### Configuración
Archivo: `config/shifts.php`
- **default_initial_base**: base inicial por defecto para iniciar turno si no se ingresa valor.
- **difference_tolerance**: tolerancia máxima permitida para la diferencia entre base esperada vs base recibida.  
  Si la diferencia supera la tolerancia, se exige observación en la recepción.

### Auditoría
Se registran logs en `audit_logs` (modelo `App\Models\AuditLog`) para:
- inicio de turno
- entrega de turno
- recepción de turno
- creación de retiro de caja
- eliminación de retiro de caja


