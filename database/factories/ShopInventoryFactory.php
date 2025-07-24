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
            'created_by' => $this->faker->name,
            'product_name' => $this->faker->word,
            'brand' => $this->faker->company,
            'model' => $this->faker->bothify('Model-###'),
            'serial_number' => $this->faker->unique()->bothify('SN-#####'),
            'category' => $this->faker->randomElement(['Battery', 'UPS', 'Inverter', 'Stabilizer']),
            'description' => $this->faker->sentence,
            'quantity' => $this->faker->numberBetween(1, 100),
            'unit_price' => $this->faker->randomFloat(2, 100, 10000),
            'purchase_date' => $this->faker->date(),
            'warranty' => $this->faker->randomElement(['6 months', '1 year', '2 years']),
        ];
    }
}
