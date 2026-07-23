<?php

namespace Database\Factories;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'SUP-' . strtoupper($this->faker->unique()->bothify('??####')),
            'name' => $this->faker->company(),
            'contact' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
        ];
    }
}
