<?php

namespace Database\Seeders;

use App\Models\BackgroundJob;
use Illuminate\Database\Seeder;

class BackgroundJobSeeder extends Seeder
{
    public function run(): void
    {
        BackgroundJob::factory()->count(10)->withAdmin()->create();
    }
}
