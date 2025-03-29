<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $travelRequest = TravelRequest::factory()->create();

        return [
            'user_id' => $travelRequest->user_id,
            'travel_request_id' => $travelRequest->id,
            'message' => $this->faker->sentence(),
            'read' => $this->faker->boolean(20),
        ];
    }

    /**
     * Indicate that the notification is read.
     *
     * @return Factory
     */
    public function read(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'read' => true,
            ];
        });
    }

    /**
     * Indicate that the notification is unread.
     *
     * @return Factory
     */
    public function unread(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'read' => false,
            ];
        });
    }
}
