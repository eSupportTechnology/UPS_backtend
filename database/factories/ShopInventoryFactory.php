<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShopInventory>
 */
class ShopInventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_by'    => $this->faker->uuid(),
            'product_name'  => $this->faker->words(2, true),
            'brand'         => $this->faker->company(),
            'model'         => strtoupper($this->faker->bothify('??###')),
            'serial_number' => strtoupper($this->faker->bothify('SN-#####')),
            'category'      => $this->faker->randomElement(['Electronics', 'Furniture', 'Appliances', 'Tools']),
            'description'   => $this->faker->sentence(),
            'quantity'      => $this->faker->numberBetween(1, 100),
            'unit_price'    => $this->faker->randomFloat(2, 10, 1000),
            'purchase_date' => $this->faker->date(),
            'warranty'      => $this->faker->randomElement(['6 months', '1 year', '2 years', '3 years']),
        ];
    }
}
