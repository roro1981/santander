<?php

use App\Models\CartStatus;
use App\Models\Cart;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = CartStatus::class;

    public function definition()
    {
        return [
            'car_id' => function () {
                return Cart::factory()->create()->car_id;
            },
            'cas_status' => $this->faker->randomElement(['CREATED']),
        ];
    }
}