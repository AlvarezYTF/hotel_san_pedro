<?php

return [
    /**
     * Default initial base for cash drawer.
     */
    'default_initial_base' => 0.00,

    /**
     * Tolerance amount for differences in shift handovers.
     * If the difference is within this range, it might be considered acceptable.
     */
    'difference_tolerance' => 1000.00, // Pesos or current currency

    /**
     * How long (in minutes) a receptionist can delete their own cash operations
     * (gastos / retiros) while the shift is ACTIVE.
     */
    'cash_delete_window_minutes' => 60,

    /**
     * Shift configurations
     */
    'schedules' => [
        'day' => [
            'start' => '06:00',
            'end' => '14:00',
        ],
        'night' => [
            'start' => '22:00',
            'end' => '06:00',
        ],
    ],
];

