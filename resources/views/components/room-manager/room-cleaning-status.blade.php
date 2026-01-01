@props(['room'])

@php($cleaning = $room->cleaning_status)
<div class="relative inline-block">
    <button 
        type="button"
        @click.stop="open = !open"
        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $cleaning['color'] }} hover:opacity-80 transition-opacity cursor-pointer"
        title="Click para cambiar estado de limpieza">
        <i class="fas {{ $cleaning['icon'] }} mr-1.5"></i>
        {{ $cleaning['label'] }}
    </button>

    <!-- Dropdown para cambiar estado -->
    <div 
        x-show="open"
        @click.outside="open = false"
        @keydown.escape.window="open = false"
        x-transition
        x-cloak
        class="absolute left-1/2 transform -translate-x-1/2 mt-2 w-48 bg-white rounded-lg shadow-xl ring-1 ring-gray-200 z-50"
        style="display: none;">
        <div class="py-1">
            @if($cleaning['code'] === 'pendiente')
                <button 
                    type="button"
                    wire:click="updateCleaningStatus({{ $room->id }}, 'limpia')"
                    wire:target="updateCleaningStatus({{ $room->id }}, 'limpia')"
                    wire:loading.attr="disabled"
                    @click="open = false"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    <span>Marcar como Limpia</span>
                    <i class="fas fa-spinner fa-spin ml-auto text-xs" wire:loading wire:target="updateCleaningStatus({{ $room->id }}, 'limpia')"></i>
                </button>
            @else
                <button 
                    type="button"
                    wire:click="updateCleaningStatus({{ $room->id }}, 'pendiente')"
                    wire:target="updateCleaningStatus({{ $room->id }}, 'pendiente')"
                    wire:loading.attr="disabled"
                    @click="open = false"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                    <i class="fas fa-broom text-yellow-600 mr-2"></i>
                    <span>Marcar como Pendiente</span>
                    <i class="fas fa-spinner fa-spin ml-auto text-xs" wire:loading wire:target="updateCleaningStatus({{ $room->id }}, 'pendiente')"></i>
                </button>
            @endif
        </div>
    </div>
</div>

