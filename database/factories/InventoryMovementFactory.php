<?php

namespace Database\Factories;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['input', 'output', 'sale', 'adjustment', 'room_consumption']);
        $quantity = fake()->randomElement([1, 2, 5, 10, -1, -2, -5]);
        $previousStock = fake()->numberBetween(0, 100);
        $currentStock = max(0, $previousStock + $quantity);

        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'room_id' => fake()->boolean(30) ? Room::factory() : null,
            'quantity' => $quantity,
            'type' => $type,
            'reason' => fake()->optional()->sentence(),
            'previous_stock' => $previousStock,
            'current_stock' => $currentStock,
        ];
    }

    /**
     * Indicate that this is an input movement.
     */
    public function input(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'input',
            'quantity' => fake()->numberBetween(1, 50),
            'current_stock' => $attributes['previous_stock'] + fake()->numberBetween(1, 50),
        ]);
    }

    /**
     * Indicate that this is an output movement.
     */
    public function output(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'output',
            'quantity' => -fake()->numberBetween(1, 20),
            'current_stock' => max(0, $attributes['previous_stock'] - fake()->numberBetween(1, 20)),
        ]);
    }

    /**
     * Indicate that this is a sale movement.
     */
    public function sale(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sale',
            'quantity' => -fake()->numberBetween(1, 10),
            'current_stock' => max(0, $attributes['previous_stock'] - fake()->numberBetween(1, 10)),
        ]);
    }

    /**
     * Indicate that this is an adjustment movement.
     */
    public function adjustment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'adjustment',
            'reason' => fake()->randomElement(['Stock correction', 'Inventory recount', 'System adjustment']),
        ]);
    }

    /**
     * Indicate that this is a room consumption movement.
     */
    public function roomConsumption(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'room_consumption',
            'quantity' => -fake()->numberBetween(1, 5),
            'room_id' => Room::factory(),
            'current_stock' => max(0, $attributes['previous_stock'] - fake()->numberBetween(1, 5)),
        ]);
    }
}
