<?php

namespace App\Services;

use App\Models\ShiftControl;

class ShiftControlService
{
    public function isOperationalEnabled(): bool
    {
        return $this->current()->operational_enabled;
    }

    public function current(): ShiftControl
    {
        return ShiftControl::query()->firstOrCreate(
            ["id" => 1],
            [
                "operational_enabled" => true,
                "note" => "Inicializado automáticamente",
            ],
        );
    }

    public function setOperationalEnabled(
        bool $enabled,
        ?int $updatedBy = null,
        ?string $note = null,
    ): ShiftControl {
        $control = $this->current();
        $control->operational_enabled = $enabled;
        $control->updated_by = $updatedBy;
        $control->note = $note;
        $control->save();

        return $control;
    }
}

