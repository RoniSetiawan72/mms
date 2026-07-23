<?php

namespace Database\Factories;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class MaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'MAT-' . strtoupper($this->faker->unique()->bothify('??####')),
            'name' => ucfirst($this->faker->words(2, true)),
            'unit' => $this->faker->randomElement(['pcs', 'kg', 'L', 'm', 'box', 'roll']),
            // Secara default stok diset 0. Penambahan stok yang benar harus melalui mekanisme Penerimaan (Receive) / Stored Procedure.
            'stock' => 0,
        ];
    }
}
