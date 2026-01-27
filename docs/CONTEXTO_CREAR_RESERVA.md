# Contexto para Crear una Reserva

## 📋 Resumen General

El sistema de creación de reservas utiliza un componente **Livewire** (`ReservationCreate`) que se puede mostrar en una página dedicada o dentro de un modal. El formulario permite crear reservas para una o múltiples habitaciones con asignación de huéspedes.

---

## 🏗️ Arquitectura

### Componentes Principales

1. **Componente Livewire**: `App\Livewire\Reservations\ReservationCreate`
2. **Controlador**: `App\Http\Controllers\ReservationController`
3. **Request de Validación**: `App\Http\Requests\StoreReservationRequest`
4. **Vista Livewire**: `resources/views/livewire/reservations/reservation-create.blade.php`
5. **Modal**: `resources/views/components/reservations/create-modal.blade.php`

---

## 📦 Datos Requeridos para Inicializar el Componente

El componente `ReservationCreate` necesita los siguientes datos en el método `mount()`:

### 1. **Habitaciones** (`$rooms`)
Array de habitaciones disponibles (excluyendo las en mantenimiento):
```php
[
    [
        'id' => 1,
        'room_number' => '101',
        'beds_count' => 2,
        'max_capacity' => 4,
    ],
    // ...
]
```

### 2. **Datos de Habitaciones** (`$roomsData`)
Array con información detallada de precios por ocupación:
```php
[
    [
        'id' => 1,
        'room_number' => '101',
        'occupancy_prices' => [
            1 => 50000,  // Precio para 1 persona
            2 => 80000,  // Precio para 2 personas
            3 => 100000, // Precio para 3 personas
            // ...
        ],
        'beds_count' => 2,
        'max_capacity' => 4,
    ],
    // ...
]
```

### 3. **Clientes** (`$customers`)
Array de clientes con información de perfil fiscal:
```php
[
    [
        'id' => 1,
        'name' => 'Juan Pérez',
        'phone' => '3001234567',
        'email' => 'juan@example.com',
        'taxProfile' => [
            'identification' => '1234567890',
            'dv' => '5',
        ],
    ],
    // ...
]
```

### 4. **Catálogos DIAN** (para creación de clientes)

#### **Documentos de Identificación** (`$identificationDocuments`)
```php
[
    ['id' => 1, 'code' => 'CC', 'name' => 'Cédula de Ciudadanía'],
    ['id' => 2, 'code' => 'CE', 'name' => 'Cédula de Extranjería'],
    // ...
]
```

#### **Organizaciones Legales** (`$legalOrganizations`)
```php
[
    ['id' => 1, 'code' => '1', 'name' => 'Sociedad Anónima'],
    // ...
]
```

#### **Tributos** (`$tributes`)
```php
[
    ['id' => 1, 'code' => '01', 'name' => 'IVA'],
    // ...
]
```

#### **Municipios** (`$municipalities`)
```php
[
    ['id' => 1, 'code' => '05001', 'name' => 'Medellín'],
    // ...
]
```

---

## 📝 Campos del Formulario

### Información del Cliente
- **Cliente Principal** (`customer_id`): Requerido. Selección mediante búsqueda.
- **Crear Nuevo Cliente**: Modal para crear cliente con información fiscal completa.

### Fechas y Estancia
- **Check-In** (`check_in_date`): Requerido. Debe ser >= hoy.
- **Check-Out** (`check_out_date`): Requerido. Debe ser > check-in.
- **Hora de Ingreso** (`check_in_time`): Opcional. Formato HH:MM, mínimo desde `config('hotel.check_in_time', '15:00')`.
- **Fecha de Reserva** (`reservation_date`): Requerido. Fecha en que se realiza la reserva.

### Habitaciones
- **Modo Simple**: Una habitación (`room_id`)
- **Modo Múltiple**: Múltiples habitaciones (`room_ids[]`)
- **Asignación de Huéspedes**: Por habitación (`room_guests[room_id][]`)

### Información Financiera
- **Total** (`total_amount`): Requerido. Calculado automáticamente según ocupación.
- **Abono** (`deposit`): Requerido. Monto inicial pagado.
- **Método de Pago** (`payment_method`): Opcional. Valores: `efectivo`, `transferencia`.

### Información Adicional
- **Número de Huéspedes** (`guests_count`): Requerido. Mínimo 1.
- **Notas** (`notes`): Opcional. Texto libre.

---

## ✅ Validaciones

### Validaciones del Componente Livewire

```php
protected $rules = [
    'customerId' => 'required|exists:customers,id',
    'checkIn' => 'required|date|after_or_equal:today',
    'checkOut' => 'required|date|after:checkIn',
    'checkInTime' => ['nullable', 'regex:/^([0-1]\d|2[0-3]):[0-5]\d$/', 'after_or_equal_to_hotel_checkin'],
    'total' => 'required|numeric|min:0',
    'deposit' => 'required|numeric|min:0',
    'guestsCount' => 'nullable|integer|min:0',
];
```

### Validaciones del Request (Backend)

```php
'customer_id'      => 'required|exists:customers,id',
'room_id'          => 'required_without:room_ids|nullable|exists:rooms,id',
'room_ids'         => 'required_without:room_id|nullable|array|min:1',
'room_ids.*'       => 'required|integer|exists:rooms,id',
'room_guests'      => 'nullable|array',
'room_guests.*'    => 'nullable|array',
'room_guests.*.*'  => 'nullable|integer|exists:customers,id',
'guests_count'     => 'required|integer|min:1',
'total_amount'     => 'required|numeric|min:0',
'deposit'          => 'required|numeric|min:0',
'reservation_date' => 'required|date',
'check_in_date'    => 'required|date|after_or_equal:today',
'check_out_date'   => 'required|date|after:check_in_date',
'check_in_time'    => ['nullable', 'regex:/^([0-1]\d|2[0-3]):[0-5]\d$/'],
'notes'            => 'nullable|string',
'payment_method'   => 'nullable|string|in:efectivo,transferencia',
```

### Validaciones Adicionales

1. **Disponibilidad de Habitaciones**: Verifica que las habitaciones estén disponibles en el rango de fechas.
2. **Asignación de Huéspedes**: Valida que los huéspedes asignados no excedan la capacidad de la habitación.
3. **Cálculo de Total**: Se calcula automáticamente según:
   - Número de noches
   - Precio por ocupación de la habitación
   - Número de huéspedes

---

## 🔄 Flujo de Creación

### 1. Inicialización
```php
// En ReservationController::index() o create()
$customers = Customer::withoutGlobalScopes()
    ->with('taxProfile')
    ->orderBy('name')
    ->get();

$rooms = Room::where('status', '!=', RoomStatus::MANTENIMIENTO)->get();
$roomsData = $this->prepareRoomsData($rooms);
$dianCatalogs = $this->getDianCatalogs();
```

### 2. Renderizado del Componente
```blade
@livewire('reservations.reservation-create', [
    'rooms' => $modalRooms,
    'roomsData' => $modalRoomsData,
    'customers' => $modalCustomers,
    'identificationDocuments' => $modalIdentificationDocuments,
    'legalOrganizations' => $modalLegalOrganizations,
    'tributes' => $modalTributes,
    'municipalities' => $modalMunicipalities,
])
```

### 3. Interacción del Usuario
1. Usuario completa fechas de Check-In y Check-Out
2. Sistema valida fechas y habilita selección de habitaciones
3. Usuario selecciona cliente (o crea uno nuevo)
4. Usuario selecciona habitación(es)
5. Sistema calcula total automáticamente
6. Usuario asigna huéspedes a habitaciones (opcional)
7. Usuario ingresa abono y método de pago
8. Usuario confirma y envía formulario

### 4. Procesamiento en Backend
```php
// ReservationController::store()
1. Validar request (StoreReservationRequest)
2. Validar disponibilidad de habitaciones
3. Validar asignación de huéspedes
4. Crear reserva (Reservation::create())
5. Crear relaciones en tabla pivot (ReservationRoom)
6. Asignar huéspedes a habitaciones
7. Actualizar estado de habitaciones si check-in es hoy
8. Registrar auditoría
9. Emitir evento Livewire 'reservation-created'
10. Redirigir a index con mensaje de éxito
```

---

## 🎯 Funcionalidades Especiales

### 1. **Modo Múltiples Habitaciones**
- Toggle entre modo simple y múltiple
- Selección múltiple de habitaciones
- Asignación independiente de huéspedes por habitación

### 2. **Cálculo Automático de Precios**
- Precios por ocupación (1, 2, 3+ personas)
- Cálculo basado en número de noches
- Actualización en tiempo real al cambiar fechas/habitación/huéspedes

### 3. **Verificación de Disponibilidad**
- Verificación en tiempo real
- Limpieza automática de selecciones no disponibles
- Mensajes informativos de disponibilidad

### 4. **Creación de Clientes**
- Modal para crear cliente principal
- Modal para crear huéspedes adicionales
- Validación de identificación (con/sin DV)
- Soporte para facturación electrónica (DIAN)

### 5. **Asignación de Huéspedes**
- Modal de búsqueda/creación de huéspedes
- Asignación por habitación
- Validación de capacidad máxima

---

## 📤 Estructura del Request al Enviar

```php
[
    'customer_id' => 1,
    'room_id' => 5,                    // Modo simple
    // O
    'room_ids' => [5, 6, 7],           // Modo múltiple
    'room_guests' => [
        5 => [2, 3],                   // Huéspedes para habitación 5
        6 => [4],                       // Huéspedes para habitación 6
    ],
    'guests_count' => 3,
    'total_amount' => 240000,
    'deposit' => 100000,
    'reservation_date' => '2024-01-15',
    'check_in_date' => '2024-01-20',
    'check_out_date' => '2024-01-23',
    'check_in_time' => '15:00',
    'payment_method' => 'efectivo',
    'notes' => 'Cliente VIP',
]
```

---

## 🔧 Configuración Necesaria

### Variables de Configuración (`config/hotel.php`)
```php
'check_in_time' => '15:00',      // Hora mínima de check-in
'check_out_time' => '12:00',      // Hora de check-out
```

### Relaciones de Base de Datos
- `reservations` → `customers` (belongsTo)
- `reservations` → `rooms` (belongsToMany via `reservation_rooms`)
- `reservation_rooms` → `customers` (belongsToMany via `reservation_room_guests`)

---

## 🚨 Manejo de Errores

### Errores Comunes
1. **Habitación no disponible**: Se limpia automáticamente la selección
2. **Fechas inválidas**: Mensajes de validación específicos
3. **Cliente no encontrado**: Opción para crear nuevo cliente
4. **Capacidad excedida**: Validación antes de enviar

### Mensajes de Error Personalizados
Todos los mensajes están definidos en:
- `ReservationCreate::messages()` (componente)
- `StoreReservationRequest::messages()` (request)

---

## 📍 Rutas Relacionadas

```php
// Mostrar formulario
GET  /reservations/create

// Crear reserva
POST /reservations

// Listar reservas
GET  /reservations

// Verificar disponibilidad (AJAX)
GET  /api/reservations/check-availability
```

---

## 💡 Notas Importantes

1. **El componente se inicializa con fechas por defecto**:
   - Check-In: Hoy
   - Check-Out: Mañana
   - Hora: Configuración del hotel

2. **El total se calcula automáticamente** pero puede ser editado manualmente.

3. **Las habitaciones en mantenimiento se excluyen** automáticamente.

4. **El sistema soporta reservas para el mismo día** si la hora de check-in es >= hora configurada.

5. **Al crear la reserva exitosamente**, se redirige a `/reservations` con el mes del check-in visible.

---

## 🔗 Archivos Relacionados

- `app/Livewire/Reservations/ReservationCreate.php`
- `app/Http/Controllers/ReservationController.php`
- `app/Http/Requests/StoreReservationRequest.php`
- `resources/views/livewire/reservations/reservation-create.blade.php`
- `resources/views/components/reservations/create-modal.blade.php`
- `resources/views/reservations/index.blade.php`
- `resources/views/components/reservations/header.blade.php`
