<?php

namespace App\Models;

use App\Enums\ShiftHandoverStatus;
use App\Enums\ShiftType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftHandover extends Model
{
    use HasFactory;

    protected $fillable = [
        'entregado_por',
        'recibido_por',
        'shift_type',
        'shift_date',
        'started_at',
        'ended_at',
        'received_at',
        'base_inicial',
        'base_final',
        'base_recibida',
        'total_entradas_efectivo',
        'total_entradas_transferencia',
        'total_salidas',
        'base_esperada',
        'diferencia',
        'observaciones_entrega',
        'observaciones_recepcion',
        'status',
    ];

    protected $casts = [
        'shift_type' => ShiftType::class,
        'shift_date' => 'date',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'received_at' => 'datetime',
        'base_inicial' => 'decimal:2',
        'base_final' => 'decimal:2',
        'base_recibida' => 'decimal:2',
        'total_entradas_efectivo' => 'decimal:2',
        'total_entradas_transferencia' => 'decimal:2',
        'total_salidas' => 'decimal:2',
        'base_esperada' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'status' => ShiftHandoverStatus::class,
    ];

    public function entregadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entregado_por');
    }

    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recibido_por');
    }

    public function cashOuts(): HasMany
    {
        return $this->hasMany(ShiftCashOut::class);
    }

    public function cashOutflows(): HasMany
    {
        return $this->hasMany(CashOutflow::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    // Business Logic Methods
    public function calcularBaseEsperada(): float
    {
        return (float) ($this->base_inicial + $this->total_entradas_efectivo - $this->total_salidas);
    }

    public function calcularDiferencia(): float
    {
        return (float) ($this->base_recibida - $this->base_esperada);
    }

    public function getTotalEntradasEfectivo(): float
    {
        return (float) $this->total_entradas_efectivo;
    }

    public function getTotalEntradasTransferencia(): float
    {
        return (float) $this->total_entradas_transferencia;
    }

    public function getTotalSalidas(): float
    {
        return (float) $this->total_salidas;
    }

    public function getEfectivoDisponible(): float
    {
        $this->updateTotals();
        return (float) $this->base_esperada;
    }

    public function updateTotals(): void
    {
        $this->total_entradas_efectivo = $this->sales()->where('payment_method', 'efectivo')->sum('cash_amount') 
                                      + $this->sales()->where('payment_method', 'ambos')->sum('cash_amount');
        
        $this->total_entradas_transferencia = $this->sales()->where('payment_method', 'transferencia')->sum('transfer_amount') 
                                           + $this->sales()->where('payment_method', 'ambos')->sum('transfer_amount');
        
        // Total salidas de caja del turno:
        // - Gastos (CashOutflow)
        // - Retiros/traslados de efectivo (ShiftCashOut)
        $this->total_salidas = (float) $this->cashOutflows()->sum('amount')
            + (float) $this->cashOuts()->sum('amount');
        
        $this->base_esperada = $this->calcularBaseEsperada();
        $this->save();
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('status', ShiftHandoverStatus::ACTIVE);
    }

    public function scopeEntregado($query)
    {
        return $query->where('status', ShiftHandoverStatus::DELIVERED);
    }

    public function scopeRecibido($query)
    {
        return $query->where('status', ShiftHandoverStatus::RECEIVED);
    }

    public function scopePorRecepcionista($query, $userId)
    {
        return $query->where('entregado_por', $userId)->orWhere('recibido_por', $userId);
    }

    public function scopePorTurno($query, ShiftType $type)
    {
        return $query->where('shift_type', $type);
    }
}

