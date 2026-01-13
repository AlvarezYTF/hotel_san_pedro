@props(['room', 'selectedDate'])

@php
    // SINGLE SOURCE OF TRUTH: El estado de limpieza SOLO depende de last_cleaned_at y stays
    // NUNCA usar getOperationalStatus() ni estados operativos aquí
    $cleaningStatus = $room->cleaningStatus($selectedDate);
    
    // El método cleaningStatus() retorna un array con 'code' que puede ser:
    // - 'limpia' → Habitación limpia
    // - 'pendiente' → Pendiente por aseo
    // NUNCA retorna estados operativos como 'occupied', 'free_clean', etc.
    
    $statusConfig = match($cleaningStatus['code']) {
        'limpia' => [
            'label' => 'Limpia',
            'icon' => 'fa-check-circle',
            'color' => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
        ],
        'pendiente' => [
            'label' => 'Pendiente por aseo',
            'icon' => 'fa-broom',
            'color' => 'bg-yellow-100 text-yellow-700 border border-yellow-200',
        ],
        default => [
            // Fallback: si por alguna razón el código no es reconocido, mostrar como limpia
            'label' => 'Limpia',
            'icon' => 'fa-check-circle',
            'color' => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
        ],
    };
@endphp

<span 
    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusConfig['color'] }}"
    title="Estado de limpieza">
    <i class="fas {{ $statusConfig['icon'] }} mr-1.5"></i>
    {{ $statusConfig['label'] }}
</span>

