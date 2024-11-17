<?php

namespace Database\Factories;

use App\Models\BackgroundJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobLog>
 */
class JobLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'background_job_id' => BackgroundJob::factory(),
            'message' => $this->faker->sentence(),
        ];
    }
}
