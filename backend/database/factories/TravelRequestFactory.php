<?php

namespace Database\Factories;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TravelRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TravelRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departureDate = $this->faker->dateTimeBetween('+1 week', '+2 weeks');
        $returnDate = $this->faker->dateTimeBetween(
            $departureDate->format('Y-m-d'),
            (clone $departureDate)->modify('+2 weeks')->format('Y-m-d')
        );

        return [
            'user_id' => User::factory(),
            'destination' => $this->faker->city() . ', ' . $this->faker->country(),
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'status' => $this->faker->randomElement(['requested', 'approved', 'canceled']),
        ];
    }

    /**
     * Indicate that the travel request is requested.
     *
     * @return Factory
     */
    public function requested(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'requested',
            ];
        });
    }

    /**
     * Indicate that the travel request is approved.
     *
     * @return Factory
     */
    public function approved(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
            ];
        });
    }

    /**
     * Indicate that the travel request is canceled.
     *
     * @return Factory
     */
    public function canceled(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'canceled',
            ];
        });
    }
}
