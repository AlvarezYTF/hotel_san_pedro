<?php

if (!function_exists('formatCurrency')) {
    /**
     * Formatea un valor numérico como moneda colombiana (COP)
     * 
     * @param float|int|string $value
     * @param bool $showDecimals
     * @return string
     */
    function formatCurrency($value, $showDecimals = false): string
    {
        $value = (float) $value;
        
        if ($showDecimals) {
            return '$' . number_format($value, 2, ',', '.');
        }
        
        return '$' . number_format($value, 0, ',', '.');
    }
}

if (!function_exists('parseCurrency')) {
    /**
     * Convierte un string de moneda a número
     * 
     * @param string $value
     * @return float
     */
    function parseCurrency(string $value): float
    {
        // Remover símbolo de dólar y espacios
        $value = str_replace(['$', ' '], '', $value);
        
        // Remover separadores de miles (puntos)
        $value = str_replace('.', '', $value);
        
        // Convertir coma decimal a punto
        $value = str_replace(',', '.', $value);
        
        return (float) $value;
    }
}

