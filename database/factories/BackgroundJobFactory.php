<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BackgroundJob;
use App\Models\User;

class BackgroundJobFactory extends Factory
{
    protected $model = BackgroundJob::class;

    public function definition(): array
    {
        return [
            //TODO: check if '\App\Services\ExampleService::class' is OK.
            'class' => \App\Services\ExampleService::class,
            'method' => 'exampleMethod',
            'params' => json_encode(['param1' => $this->faker->word]),
            'status' => $this->faker->randomElement(['pending', 'running', 'completed', 'failed', 'cancelled']),
            'priority' => $this->faker->numberBetween(1, 5),
            'retry_count' => 0,
            'max_retries' => $this->faker->numberBetween(1, 5),
            'last_attempted_at' => $this->faker->dateTime(),
            'error_message' => null,
            'scheduled_at' => now()->addMinutes($this->faker->numberBetween(10, 60)),
            'user_id' => User::factory(),
        ];
    }

    public function withAdmin(): static
    {
        return $this->state(function () {
            $admin = User::where('is_admin', true)->first();

            if (!$admin) {
                $admin = User::factory()->create(['is_admin' => true]);
            }

            return ['user_id' => $admin->id];
        });
    }
}
