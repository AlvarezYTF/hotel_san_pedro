<?php

namespace Database\Factories;

use App\Models\Room;
use App\Enums\RoomStatus;
use App\Enums\VentilationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_number' => fake()->unique()->numerify('###'),
            'beds_count' => fake()->numberBetween(1, 4),
            'max_capacity' => fake()->numberBetween(1, 6),
            'ventilation_type' => fake()->randomElement(['ventilador', 'aire_acondicionado']),
            'price_per_night' => fake()->randomFloat(2, 50, 300),
            'price_1_person' => fake()->randomFloat(2, 50, 150),
            'price_2_persons' => fake()->randomFloat(2, 80, 200),
            'price_additional_person' => fake()->randomFloat(2, 20, 50),
            'occupancy_prices' => [
                '1' => fake()->randomFloat(2, 50, 150),
                '2' => fake()->randomFloat(2, 80, 200),
                '3' => fake()->randomFloat(2, 100, 250),
            ],
            'status' => 'available',
            'last_cleaned_at' => now(),
        ];
    }

    /**
     * Indicate that the room is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'available',
        ]);
    }

    /**
     * Indicate that the room is occupied.
     */
    public function occupied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'occupied',
        ]);
    }

    /**
     * Indicate that the room needs cleaning.
     */
    public function needsCleaning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'needs_cleaning',
            'last_cleaned_at' => now()->subDays(3),
        ]);
    }
}
