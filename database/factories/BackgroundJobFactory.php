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
            'class' => \App\Services\ExampleService::class,
            'method' => 'exampleMethod',
            'params' => json_encode(['param1' => $this->faker->word]),
            'status' => 'pending',
            'priority' => $this->faker->numberBetween(0, 10),
            'retry_count' => 0,
            'max_retries' => 3,
            'error_message' => null,
            'scheduled_at' => now()->addMinutes($this->faker->numberBetween(5, 60)),
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
