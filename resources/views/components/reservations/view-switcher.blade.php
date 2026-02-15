@props([
    'view',
    'date'
])

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-3 rounded-xl border border-gray-100 shadow-sm">
    <div class="flex items-center p-1 bg-gray-50 rounded-xl border border-gray-100">
        <a href="{{ route('reservations.index', ['view' => 'calendar', 'month' => $date->format('Y-m')]) }}"
           class="flex items-center px-4 py-2 rounded-lg text-sm font-bold transition-all duration-200 {{ $view === 'calendar' ? 'bg-white text-emerald-600 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
            <i class="fas fa-calendar-alt mr-2"></i>Vista Calendario
        </a>
        <a href="{{ route('reservations.index', ['view' => 'list', 'month' => $date->format('Y-m')]) }}"
           class="flex items-center px-4 py-2 rounded-lg text-sm font-bold transition-all duration-200 {{ $view === 'list' ? 'bg-white text-emerald-600 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
            <i class="fas fa-list mr-2"></i>Vista Lista
        </a>
    </div>
</div>

