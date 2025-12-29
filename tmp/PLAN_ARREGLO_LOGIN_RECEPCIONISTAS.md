# Plan de Arreglo de Inicio de Sesión - Recepcionistas

## Estado Actual
- Los recepcionistas no pueden iniciar sesión correctamente.
- La lógica de restricción de horario en `SecurityControlMiddleware` no maneja correctamente los turnos que cruzan la medianoche (ej. Recepcionista Noche).
- El sistema de login solo permite el correo electrónico, aunque la vista indica "Email o Usuario".

## Estado Final
- Los recepcionistas podrán iniciar sesión dentro de sus horarios, incluso si el turno cruza la medianoche.
- Los usuarios podrán iniciar sesión usando tanto su email como su nombre de usuario.

## Archivos a Modificar
- `app/Http/Middleware/SecurityControlMiddleware.php`: Corregir la lógica de comparación de horas.
- `app/Http/Controllers/AuthController.php`: Modificar la lógica de autenticación para aceptar `username`.
- `resources/views/auth/login.blade.php`: Cambiar el tipo de input de `email` a `text`.

## Lista de Tareas
1. [x] Modificar `SecurityControlMiddleware.php` para soportar rangos de horas que cruzan la medianoche.
2. [x] Modificar `AuthController.php` para buscar al usuario por email o username.
3. [x] Modificar `resources/views/auth/login.blade.php` para cambiar el tipo de input y mejorar la UX.

