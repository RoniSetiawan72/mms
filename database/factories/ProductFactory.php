<?php

namespace Database\Factories;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'PRD-' . strtoupper($this->faker->unique()->bothify('??####')),
            'name' => ucfirst($this->faker->words(3, true)),
            // Stok awal produk diset 0. Stok harusnya bertambah setelah proses Finish Production.
            'stock' => 0,
        ];
    }
}
