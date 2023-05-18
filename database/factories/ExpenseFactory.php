<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'amount' => $this->faker->numberBetween(1, 1000),
            'group_id' => function() {
                return Group::factory()->create()->id;
            },
            'user_id' => function() {
                return User::factory()->create()->id;
            },
        ];
    }
}
