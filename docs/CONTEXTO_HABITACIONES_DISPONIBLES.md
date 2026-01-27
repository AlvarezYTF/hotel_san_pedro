# Contexto para Mostrar Habitaciones Disponibles

## 📋 Resumen General

El sistema de disponibilidad de habitaciones en el formulario de creación de reservas funciona de manera reactiva: cuando el usuario selecciona fechas de check-in y check-out, el sistema filtra automáticamente las habitaciones disponibles para ese rango de fechas.

---

## 🏗️ Arquitectura

### Componentes Principales

1. **Componente Livewire**: `App\Livewire\Reservations\ReservationCreate`
2. **Propiedad Computada**: `getAvailableRoomsProperty()` - Filtra habitaciones disponibles
3. **Método de Verificación**: `isRoomAvailableForDates()` - Verifica disponibilidad de una habitación
4. **Vista**: `resources/views/livewire/reservations/reservation-create.blade.php`

---

## 🔄 Flujo de Funcionamiento

### 1. Inicialización de Datos

Al montar el componente, se reciben todas las habitaciones disponibles (excluyendo las en mantenimiento):

```php
// En ReservationController::index() o create()
$rooms = Room::where('status', '!=', RoomStatus::MANTENIMIENTO)->get();

// Se preparan como array para Livewire
$roomsArray = $rooms->map(function ($room) {
    return [
        'id' => (int) $room->id,
        'room_number' => (string) ($room->room_number ?? ''),
        'beds_count' => (int) ($room->beds_count ?? 0),
        'max_capacity' => (int) ($room->max_capacity ?? 0),
    ];
})->toArray();
```

### 2. Selección de Fechas

Cuando el usuario cambia las fechas:

```php
// En ReservationCreate.php
public function updatedCheckIn($value)
{
    $this->clearDateErrors();
    $this->resetAvailabilityState();
    
    // Validar fechas
    $this->validateCheckInDate($value);
    
    if (!empty($this->checkOut)) {
        $this->validateCheckOutAgainstCheckIn();
    }
    
    $this->validateDates();
    
    // Limpiar selecciones de habitaciones no disponibles
    $this->clearUnavailableRooms();
    
    // Recalcular total
    $this->calculateTotal();
    
    // Verificar disponibilidad si está listo
    $this->checkAvailabilityIfReady();
}
```

### 3. Cálculo de Habitaciones Disponibles

La propiedad computada `availableRooms` se calcula automáticamente cuando cambian las fechas:

```php
public function getAvailableRoomsProperty(): array
{
    // Guard clauses: validar que las fechas estén completas
    if (empty($this->checkIn) || empty($this->checkOut)) {
        return [];
    }
    
    // Si hay errores de validación, retornar vacío
    if ($this->hasDateValidationErrors()) {
        return [];
    }
    
    // Solo filtrar si las fechas están completas y validadas
    if (!$this->datesCompleted) {
        return [];
    }
    
    try {
        $checkIn = Carbon::parse($this->checkIn)->startOfDay();
        $checkOut = Carbon::parse($this->checkOut)->startOfDay();
        
        // Validar rango de fechas
        if ($checkOut->lte($checkIn)) {
            return [];
        }
        
        $availableRooms = [];
        $allRooms = $this->rooms ?? [];
        
        // Filtrar cada habitación
        foreach ($allRooms as $room) {
            if (!is_array($room) || empty($room['id'])) {
                continue;
            }
            
            $roomId = (int) $room['id'];
            
            // Verificar disponibilidad para el rango de fechas
            if ($this->isRoomAvailableForDates($roomId, $checkIn, $checkOut)) {
                $availableRooms[] = $room;
            }
        }
        
        return $availableRooms;
    } catch (\Exception $e) {
        Log::error('Error filtering available rooms: ' . $e->getMessage());
        return [];
    }
}
```

### 4. Verificación de Disponibilidad

El método `isRoomAvailableForDates()` verifica si una habitación está disponible. **CRÍTICO**: Ahora considera tanto **stays activas** (ocupación real) como **reservations** (planificación futura):

```php
private function isRoomAvailableForDates(int $roomId, Carbon $checkIn, Carbon $checkOut): bool
{
    // 🔥 AJUSTE CRÍTICO 1: Verificar stays activas (ocupación real)
    // Una habitación NO está disponible si tiene una stay activa que intersecta el rango solicitado
    $hasActiveStay = \App\Models\Stay::where('room_id', $roomId)
        ->where('status', 'active')
        ->where(function ($q) use ($checkIn, $checkOut) {
            $q->where('check_in_at', '<', $checkOut->endOfDay())
              ->where(function ($q2) use ($checkIn) {
                  $q2->whereNull('check_out_at')
                     ->orWhere('check_out_at', '>', $checkIn->startOfDay());
              });
        })
        ->exists();
    
    if ($hasActiveStay) {
        return false; // ❌ Habitación ocupada por stay activa
    }
    
    // Verificar en tabla reservations (reservas de una sola habitación)
    $existsInReservations = Reservation::where('room_id', $roomId)
        ->where(function ($query) use ($checkIn, $checkOut) {
            $query->where('check_in_date', '<', $checkOut)
                  ->where('check_out_date', '>', $checkIn);
        })
        ->exists();
    
    if ($existsInReservations) {
        return false; // Habitación ocupada
    }
    
    // Verificar en tabla reservation_rooms (reservas de múltiples habitaciones)
    $existsInPivot = DB::table('reservation_rooms')
        ->join('reservations', 'reservation_rooms.reservation_id', '=', 'reservations.id')
        ->where('reservation_rooms.room_id', $roomId)
        ->whereNull('reservations.deleted_at') // Excluir reservas eliminadas
        ->where(function ($query) use ($checkIn, $checkOut) {
            $query->where('reservations.check_in_date', '<', $checkOut)
                  ->where('reservations.check_out_date', '>', $checkIn);
        })
        ->exists();
    
    return !$existsInPivot; // Disponible si no existe conflicto
}
```

**🔐 Regla de Oro:**
- **Stays activas** = Ocupación real (check-in ya ocurrió)
- **Reservations** = Planificación futura (check-in aún no ocurre)
- **Disponibilidad** = No hay stays activas Y no hay reservations que solapen

**Lógica de Solapamiento:**
- Una habitación está **ocupada** si existe una reserva donde:
  - `check_in_date < check_out` (la reserva empieza antes del checkout solicitado)
  - `check_out_date > check_in` (la reserva termina después del check-in solicitado)
- Si hay solapamiento → habitación **NO disponible**
- Si no hay solapamiento → habitación **disponible**

### 5. Filtrado para Búsqueda

Si el usuario busca habitaciones por número, se aplica un filtro adicional:

```php
public function getFilteredRoomsProperty(): array
{
    $availableRooms = $this->availableRooms; // Ya filtradas por disponibilidad
    
    if (!is_array($availableRooms) || empty($availableRooms)) {
        return [];
    }
    
    // Si hay término de búsqueda, filtrar por número de habitación
    if (!empty($this->roomSearchTerm)) {
        $searchTerm = strtolower(trim($this->roomSearchTerm));
        return array_filter($availableRooms, function ($room) use ($searchTerm) {
            $roomNumber = strtolower($room['room_number'] ?? '');
            return strpos($roomNumber, $searchTerm) !== false;
        });
    }
    
    return $availableRooms;
}
```

---

## 🎨 Visualización en la Vista

### Estructura de la Vista

```blade
<!-- Sección de Habitaciones -->
<div class="space-y-3">
    @if(!$this->datesCompleted)
        <!-- Mensaje: completar fechas primero -->
        <div class="bg-amber-50 text-amber-700 border-amber-100 p-3 rounded-xl border">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span>Completa las fechas para ver las habitaciones disponibles</span>
        </div>
    @endif

    @if($this->datesCompleted)
        <div class="border border-gray-300 rounded-xl bg-white max-h-72 overflow-y-auto">
            @php
                $filteredRooms = $this->filteredRooms; // Habitaciones disponibles y filtradas
            @endphp

            @if(is_array($filteredRooms) && count($filteredRooms) > 0)
                <!-- Lista de habitaciones disponibles -->
                @foreach($filteredRooms as $room)
                    <button type="button" wire:click="selectRoom({{ $room['id'] }})">
                        <div class="font-bold">Habitación {{ $room['room_number'] }}</div>
                        <div class="text-xs text-gray-500">
                            <span>{{ $room['beds_count'] }} Camas</span>
                            <span>Capacidad {{ $room['max_capacity'] }}</span>
                        </div>
                    </button>
                @endforeach
            @else
                <!-- Mensaje: no hay habitaciones disponibles -->
                <div class="px-4 py-6 text-center text-sm text-gray-500">
                    <i class="fas fa-door-closed text-2xl mb-2 opacity-50"></i>
                    <p>No hay habitaciones disponibles para estas fechas</p>
                </div>
            @endif
        </div>
    @endif
</div>
```

### Estados Visuales

1. **Fechas no completadas**: Muestra mensaje para completar fechas
2. **Fechas completadas sin habitaciones**: Muestra mensaje "No hay habitaciones disponibles"
3. **Fechas completadas con habitaciones**: Muestra lista de habitaciones disponibles

---

## ⚠️ AJUSTE CRÍTICO: Consideración de Stays Activas

### ¿Por qué es necesario?

Con la introducción del sistema de **stays** y **stay_nights**, la lógica de disponibilidad ya no puede depender **solo** de reservations. Una habitación puede estar ocupada por una stay activa sin tener una nueva reserva.

### Escenarios que se resuelven:

1. **Estadía Extendida**: 
   - Una stay activa puede extenderse más allá de la fecha original de checkout
   - Sin verificar stays, el sistema marcaría la habitación como disponible ❌

2. **Continuidad de Estadía**:
   - Una stay puede continuarse sin crear nueva reserva
   - La habitación sigue ocupada pero no aparece en reservations

3. **Check-in Inmediato**:
   - Cuando se crea una reserva con check-in HOY, se crea una stay
   - La stay marca la ocupación real, no solo la reserva

### Regla de Oro:

```
Disponibilidad = Planificación (reservations) + Ocupación Real (stays activas)
```

**Orden de Verificación:**
1. ✅ **Primero**: Verificar stays activas (ocupación real)
2. ✅ **Segundo**: Verificar reservations (planificación futura)

Esto previene **overbooking** y alinea la disponibilidad con el estado real del sistema.

---

## 🔍 Lógica de Disponibilidad Detallada

### Criterios de Disponibilidad

Una habitación está disponible si:

1. ✅ **No está en mantenimiento**: Excluida desde el inicio
2. ✅ **No tiene stays activas que solapen**: Verificado PRIMERO en `isRoomAvailableForDates()`
3. ✅ **No tiene reservas solapadas**: Verificado en `isRoomAvailableForDates()`
4. ✅ **No está eliminada**: `deleted_at IS NULL` en reservas

**Orden de Verificación (CRÍTICO):**
1. **Primero**: Verificar stays activas (ocupación real)
2. **Segundo**: Verificar reservations (planificación futura)
3. **Tercero**: Verificar reservation_rooms (reservas múltiples)

### Verificación de Solapamiento

**Fórmula de solapamiento:**
```
check_in_date < check_out_solicitado AND check_out_date > check_in_solicitado
```

**Ejemplos:**

| Reserva Existente | Fechas Solicitadas | Resultado |
|-------------------|-------------------|-----------|
| 01/01 - 05/01 | 03/01 - 07/01 | ❌ Ocupada (solapamiento) |
| 01/01 - 05/01 | 06/01 - 10/01 | ✅ Disponible (no solapa) |
| 01/01 - 05/01 | 05/01 - 10/01 | ✅ Disponible (checkout = checkin, no solapa) |
| 01/01 - 05/01 | 31/12 - 02/01 | ❌ Ocupada (solapamiento) |

### Consideraciones Especiales

1. **Stays Activas**: Se verifican PRIMERO (ocupación real tiene prioridad)
   - Solo se consideran stays con `status = 'active'`
   - Se verifica solapamiento con `check_in_at` y `check_out_at`
   - Si `check_out_at` es NULL, la stay está activa indefinidamente
2. **Reservas Eliminadas**: Se excluyen con `whereNull('reservations.deleted_at')`
3. **Reservas Múltiples Habitaciones**: Se verifican en tabla `reservation_rooms`
4. **Reservas Únicas**: Se verifican en tabla `reservations` (campo `room_id`)

**⚠️ IMPORTANTE**: Una habitación puede estar ocupada por una stay activa sin tener una reserva nueva. Por eso es crítico verificar stays primero.

---

## 🔄 Actualización Reactiva

### Cuándo se Recalcula

La propiedad `availableRooms` se recalcula automáticamente cuando:

1. ✅ Cambia `checkIn` (usuario modifica fecha de entrada)
2. ✅ Cambia `checkOut` (usuario modifica fecha de salida)
3. ✅ Se valida el rango de fechas
4. ✅ Se completa el formulario de fechas

### Limpieza de Selecciones

Cuando cambian las fechas, se limpian automáticamente las selecciones de habitaciones que ya no están disponibles:

```php
private function clearUnavailableRooms(): void
{
    // Si las fechas no son válidas, limpiar todo
    if (!$this->datesCompleted || $this->hasDateValidationErrors()) {
        $this->roomId = '';
        $this->selectedRoomIds = [];
        $this->roomGuests = [];
        return;
    }
    
    // Verificar cada habitación seleccionada
    if (!empty($this->roomId)) {
        $roomId = (int) $this->roomId;
        if (!$this->isRoomAvailableForDates($roomId, $checkIn, $checkOut)) {
            $this->roomId = ''; // Limpiar si no está disponible
            unset($this->roomGuests[$roomId]);
        }
    }
    
    // Similar para múltiples habitaciones
    // ...
}
```

---

## 📊 Estructura de Datos

### Habitación Disponible

```php
[
    'id' => 1,
    'room_number' => '101',
    'beds_count' => 2,
    'max_capacity' => 4,
]
```

### Propiedades del Componente

```php
// Todas las habitaciones (sin filtrar por disponibilidad)
public $rooms = [];

// Habitaciones disponibles (calculadas automáticamente)
public function getAvailableRoomsProperty(): array

// Habitaciones filtradas (por búsqueda)
public function getFilteredRoomsProperty(): array

// Fechas
public $checkIn = '';
public $checkOut = '';

// Estado de validación
public $datesCompleted = false;
```

---

## ⚠️ Manejo de Errores

### Casos de Error

1. **Fechas Inválidas**: Retorna array vacío
2. **Error de Parsing**: Capturado en try-catch, retorna array vacío
3. **Error de Base de Datos**: Loggeado, retorna array vacío

### Logging

```php
Log::error('Error filtering available rooms: ' . $e->getMessage(), [
    'checkIn' => $this->checkIn,
    'checkOut' => $this->checkOut,
    'trace' => $e->getTraceAsString()
]);
```

---

## 🎯 Casos de Uso

### Caso 1: Usuario Selecciona Fechas

1. Usuario ingresa check-in: `31/01/2026`
2. Usuario ingresa check-out: `01/02/2026`
3. Sistema valida fechas
4. Sistema filtra habitaciones disponibles
5. Vista muestra lista de habitaciones o mensaje "No hay habitaciones disponibles"

### Caso 2: Usuario Cambia Fechas

1. Usuario cambia check-out a `05/02/2026`
2. Sistema recalcula disponibilidad
3. Si habitación seleccionada ya no está disponible → se limpia automáticamente
4. Vista se actualiza con nuevas habitaciones disponibles

### Caso 3: Usuario Busca Habitación

1. Usuario escribe "10" en búsqueda
2. Sistema filtra habitaciones disponibles que contengan "10" en el número
3. Vista muestra solo habitaciones que coinciden

---

## 🔗 Archivos Relacionados

- `app/Livewire/Reservations/ReservationCreate.php` - Lógica de disponibilidad
- `resources/views/livewire/reservations/reservation-create.blade.php` - Vista de habitaciones
- `app/Http/Controllers/ReservationController.php` - Preparación de datos iniciales
- `app/Models/Reservation.php` - Modelo de reservas
- `app/Models/Room.php` - Modelo de habitaciones

---

## 💡 Notas Importantes

1. **Las habitaciones en mantenimiento se excluyen desde el inicio** en el controlador
2. **La verificación es en tiempo real** usando propiedades computadas de Livewire
3. **Las selecciones se limpian automáticamente** si las fechas cambian y la habitación ya no está disponible
4. **El sistema verifica tanto reservas únicas como múltiples** para garantizar precisión
5. **Las reservas eliminadas (soft delete) se excluyen** de la verificación
6. **🔥 CRÍTICO: Se verifican stays activas PRIMERO** antes de verificar reservations
   - Esto evita overbooking cuando una habitación está ocupada por una stay extendida
   - Alinea la disponibilidad con el sistema de estadías real
   - Previene conflictos con continuidad de estadía y extensiones

---

## 🚀 Optimizaciones Futuras

1. **Caché de Disponibilidad**: Cachear resultados para rangos de fechas comunes
2. **Índices de Base de Datos**: Asegurar índices en `check_in_date` y `check_out_date`
3. **Búsqueda Avanzada**: Filtrar por capacidad, tipo de cama, etc.
4. **Disponibilidad en Tiempo Real**: WebSockets para actualizaciones instantáneas
