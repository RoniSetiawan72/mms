<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RolesAndAdminSeeder::class
        ]);

        Supplier::factory(10)->create();
        Material::factory(50)->create();
        Product::factory(20)->create();

    }
}
