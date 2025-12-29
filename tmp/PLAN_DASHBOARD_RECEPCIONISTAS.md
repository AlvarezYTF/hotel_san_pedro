# Plan de Mejora Dashboard Recepcionistas

## Estado Actual
- El dashboard de recepcionistas (día/noche) ya existe.
- Muestra el estado del turno, resumen operativo, estado de habitaciones y acciones rápidas.
- Sin embargo, faltan alertas específicas y una sección de accesos permitidos dinámica.

## Objetivo
- Crear un dashboard dinámico por rol que incluya:
  - Estado del turno (mejorado).
  - Caja actual (detallada).
  - Alertas importantes (habitaciones sucias, salidas de dinero, check-ins/outs pendientes).
  - Accesos permitidos según turno y permisos.

## Archivos a Modificar
- `app/Http/Controllers/ReceptionistDashboardController.php`
- `resources/views/dashboards/receptionist-day.blade.php`
- `resources/views/dashboards/receptionist-night.blade.php`

## Tareas
1. **Mejorar Controlador**:
   - [ ] Implementar lógica para alertas importantes en `renderDashboard`.
   - [ ] Agregar conteo de check-ins y check-outs pendientes para hoy.
2. **Actualizar Vistas**:
   - [ ] Agregar sección de "Alertas Críticas".
   - [ ] Mejorar visualización de la caja.
   - [ ] Agregar sección de "Accesos Permitidos" que dependa de permisos.
   - [ ] Asegurar que el diseño sea coherente con el tipo de turno (colores específicos).

