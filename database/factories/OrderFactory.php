<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomElement([100000, 250000, 500000]);

        return [
            'user_id' => User::factory(),
            'order_number' => Order::generateOrderNumber(),
            'status' => OrderStatus::Pending,
            'subtotal' => $subtotal,
            'discount' => 0,
            'total' => $subtotal,
            'currency' => 'IDR',
        ];
    }
}
