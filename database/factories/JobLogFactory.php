<?php

namespace Database\Factories;

use App\Models\BackgroundJob;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'background_job_id' => BackgroundJob::factory(),
            'message' => $this->faker->sentence(),
        ];
    }
}
