<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'name' => fake()->slug(2),
            'toPay' => fake()->randomFloat(2, 10, 15),
            'amountToPayByUser' => fake()->randomFloat(2, 1, 10),
            'date' => fake()->dateTimeBetween("2 day", "1 week"),
            'comment' => fake()->paragraph(1),
            'accessCode' => fake()->bothify('?????????'),
            'ownerId' => $this->faker->randomElement(DB::table('users')->pluck('id')),
            'status' => 0,
        ];
    }
}
